<?php

declare(strict_types=1);

namespace Odden\Core\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Odden\Core\Enums\CustomerHealthStatus;
use Odden\Core\Models\Company;
use Odden\Core\Support\OptionalRelation;

class CalculateCustomerHealthScoreAction
{
    /**
     * Calculate and synchronize the customer health score and status for a company.
     * Evaluates cross-hub signals: activities, contacts, deals, and support tickets.
     */
    public function execute(Company $company): Company
    {
        return DB::transaction(function () use ($company): Company {
            $score = 70; // Baseline healthy score

            // 1. Touchpoints & Activity Recency
            $latestActivity = $company->activities()->latest('created_at')->first();
            if ($latestActivity !== null && $latestActivity->created_at !== null) {
                $daysSinceLastActivity = Carbon::parse($latestActivity->created_at)->diffInDays(now());
                if ($daysSinceLastActivity <= 14) {
                    $score += 15;
                } elseif ($daysSinceLastActivity <= 30) {
                    $score += 5;
                } elseif ($daysSinceLastActivity >= 60) {
                    $score -= 20;
                }
            } else {
                // No logged activities at all
                $score -= 10;
            }

            // 2. Stakeholder Breadth (Contacts)
            $contactsCount = $company->contacts()->count();
            if ($contactsCount >= 3) {
                $score += 10;
            } elseif ($contactsCount === 0) {
                $score -= 10;
            }

            // 3. Commercial / Sales Hub Signals (Deals)
            // Sales registers `deals` with resolveRelationUsing(), so look it up dynamically.
            $dealsRelation = OptionalRelation::on($company, 'deals');
            if ($dealsRelation !== null) {
                /** @var Collection<int, Model> $deals */
                $deals = $dealsRelation->get();

                if ($deals->isNotEmpty()) {
                    $hasClosedWon = false;
                    $hasOpenDeals = false;
                    $hasRecentLost = false;

                    foreach ($deals as $deal) {
                        $rawStatus = $deal->getAttribute('status');
                        $dealStatus = $rawStatus instanceof \BackedEnum ? (string) $rawStatus->value : (string) $rawStatus;

                        if ($dealStatus === 'won') {
                            $hasClosedWon = true;
                        } elseif ($dealStatus === 'open') {
                            $hasOpenDeals = true;
                        } elseif ($dealStatus === 'lost') {
                            $updatedAt = $deal->getAttribute('updated_at');
                            if ($updatedAt !== null && Carbon::parse($updatedAt)->greaterThan(now()->subDays(30))) {
                                $hasRecentLost = true;
                            }
                        }
                    }

                    if ($hasClosedWon) {
                        $score += 15;
                    }
                    if ($hasOpenDeals) {
                        $score += 10;
                    }
                    if ($hasRecentLost) {
                        $score -= 10;
                    }
                }
            }

            // 4. Service Hub Signals (Tickets & CSAT)
            // Service registers `tickets` with resolveRelationUsing(), so look it up dynamically.
            $ticketsRelation = OptionalRelation::on($company, 'tickets');
            if ($ticketsRelation !== null) {
                /** @var Collection<int, Model> $tickets */
                $tickets = $ticketsRelation->get();

                if ($tickets->isNotEmpty()) {
                    $openUrgentHighCount = 0;
                    $slaBreachCount = 0;
                    $csatScores = [];
                    $allResolved = true;

                    foreach ($tickets as $ticket) {
                        $rawTicketStatus = $ticket->getAttribute('status');
                        $ticketStatus = $rawTicketStatus instanceof \BackedEnum ? (string) $rawTicketStatus->value : (string) $rawTicketStatus;
                        $rawTicketPriority = $ticket->getAttribute('priority');
                        $ticketPriority = $rawTicketPriority instanceof \BackedEnum ? (string) $rawTicketPriority->value : (string) $rawTicketPriority;

                        if (! in_array($ticketStatus, ['resolved', 'closed'], true)) {
                            $allResolved = false;
                            if (in_array($ticketPriority, ['high', 'urgent'], true)) {
                                $openUrgentHighCount++;
                            }
                        }

                        if ($ticket->getAttribute('is_sla_response_breached') === true || $ticket->getAttribute('is_sla_resolution_breached') === true) {
                            $slaBreachCount++;
                        }

                        $csat = $ticket->getAttribute('csat_rating');
                        if (is_numeric($csat) && $csat > 0) {
                            $csatScores[] = (int) $csat;
                        }
                    }

                    // Penalize open critical tickets (up to -30)
                    $score -= min(30, $openUrgentHighCount * 15);

                    // Penalize SLA breaches (up to -40)
                    $score -= min(40, $slaBreachCount * 20);

                    // Evaluate CSAT
                    if (! empty($csatScores)) {
                        $avgCsat = array_sum($csatScores) / count($csatScores);
                        if ($avgCsat >= 4.0) {
                            $score += 15;
                        } elseif ($avgCsat <= 2.5) {
                            $score -= 25;
                        }
                    } elseif ($allResolved && $slaBreachCount === 0) {
                        // Clean support track record
                        $score += 10;
                    }
                }
            }

            // Clamp score between 0 and 100
            $finalScore = max(0, min(100, $score));

            $status = match (true) {
                $finalScore >= 70 => CustomerHealthStatus::Healthy,
                $finalScore >= 40 => CustomerHealthStatus::Neutral,
                default => CustomerHealthStatus::AtRisk,
            };

            $wasAtRisk = $company->health_status === CustomerHealthStatus::AtRisk;

            $company->update([
                'health_score' => $finalScore,
                'health_status' => $status,
                'last_health_calculated_at' => now(),
            ]);

            // Auto-trigger retention task when company drops into AtRisk status
            if ($status === CustomerHealthStatus::AtRisk && ! $wasAtRisk) {
                $company->logTask(
                    title: "Customer Churn Risk Alert: {$company->name}",
                    dueAt: now()->addHours(24),
                    body: "Account {$company->name} health dropped to At Risk (Score: {$finalScore}/100). Executive check-in and account review recommended."
                );
            }

            return $company->fresh() ?? $company;
        });
    }
}
