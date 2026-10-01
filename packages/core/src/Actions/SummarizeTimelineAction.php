<?php

declare(strict_types=1);

namespace Focal\Core\Actions;

use Focal\Core\Models\Activity;
use Focal\Core\Models\Company;
use Focal\Core\Models\Contact;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class SummarizeTimelineAction
{
    /**
     * Generate an AI/heuristic executive briefing of a Contact or Company timeline.
     * Analyzes recent touchpoints, commercial deals, support tickets, and sentiment.
     *
     * @return array{
     *     title: string,
     *     sentiment: 'positive'|'neutral'|'at_risk',
     *     executive_summary: string,
     *     key_milestones: list<string>,
     *     recommended_next_action: string,
     *     touchpoints_analyzed: int
     * }
     */
    public function execute(Contact|Company $subject): array
    {
        /** @var Collection<int, Activity> $recentActivities */
        $recentActivities = $subject->activities()
            ->latest('created_at')
            ->limit(15)
            ->get();

        $activityCount = $recentActivities->count();
        $keyMilestones = [];

        foreach ($recentActivities->take(5) as $activity) {
            $createdStr = $activity->created_at?->diffForHumans() ?? 'recently';
            $keyMilestones[] = "[{$activity->type->value}] {$activity->title} ({$createdStr})";
        }

        $isCompany = $subject instanceof Company;
        $name = $isCompany ? $subject->name : "{$subject->first_name} {$subject->last_name}";

        // Evaluate Commercial & Health Signals
        $sentiment = 'neutral';
        $healthScore = $isCompany ? (int) $subject->health_score : 70;
        $leadScore = $subject instanceof Contact ? (int) $subject->lead_score : (int) ($subject->intent_score ?? 0);

        if ($healthScore >= 75 || $leadScore >= 70) {
            $sentiment = 'positive';
        } elseif ($healthScore < 40) {
            $sentiment = 'at_risk';
        }

        // Check Deals if method exists
        $openDealsCount = 0;
        $wonDealsCount = 0;
        if (method_exists($subject, 'deals')) {
            /** @var Collection<int, Model> $deals */
            $deals = $subject->deals()->get();
            foreach ($deals as $deal) {
                $rawStatus = $deal->getAttribute('status');
                $status = $rawStatus instanceof \BackedEnum ? (string) $rawStatus->value : (string) $rawStatus;
                if ($status === 'won') {
                    $wonDealsCount++;
                    $sentiment = 'positive';
                } elseif ($status === 'open') {
                    $openDealsCount++;
                }
            }
        }

        // Check Tickets if method exists
        $unresolvedTicketsCount = 0;
        $slaBreachedCount = 0;
        if (method_exists($subject, 'tickets')) {
            /** @var Collection<int, Model> $tickets */
            $tickets = $subject->tickets()->get();
            foreach ($tickets as $ticket) {
                $rawTicketStatus = $ticket->getAttribute('status');
                $status = $rawTicketStatus instanceof \BackedEnum ? (string) $rawTicketStatus->value : (string) $rawTicketStatus;
                if (! in_array($status, ['resolved', 'closed'], true)) {
                    $unresolvedTicketsCount++;
                }
                if ($ticket->getAttribute('is_sla_response_breached') || $ticket->getAttribute('is_sla_resolution_breached')) {
                    $slaBreachedCount++;
                    $sentiment = 'at_risk';
                }
            }
        }

        // Generate Briefing Narrative
        if ($sentiment === 'at_risk') {
            $summary = "{$name} exhibits elevated churn or stalling risk (Health: {$healthScore}/100). "
                .($slaBreachedCount > 0 ? "Account has {$slaBreachedCount} active SLA breach(es). " : '')
                .($activityCount === 0 ? 'Engagement has lapsed with no recent touchpoints.' : "Analyzed {$activityCount} recent interactions.");

            $recommendation = 'Schedule an immediate executive sponsor alignment call and escalate any open support tickets.';
        } elseif ($sentiment === 'positive') {
            $summary = "{$name} demonstrates strong account momentum (Score: {$healthScore}/100, Lead Intent: {$leadScore} pts). "
                .($wonDealsCount > 0 ? "Active commercial customer with {$wonDealsCount} closed-won deal(s). " : '')
                .($openDealsCount > 0 ? "Currently advancing {$openDealsCount} active sales opportunities." : '');

            $recommendation = $openDealsCount > 0
                ? 'Send customized closing proposal and executive security review documentation.'
                : 'Explore expansion tier upgrade and request customer case study quote.';
        } else {
            $summary = "{$name} is in stable standing with {$activityCount} logged touchpoint(s). Pipeline and engagement metrics remain steady.";
            $recommendation = 'Conduct standard 30-day discovery check-in call and share relevant product release notes.';
        }

        return [
            'title' => "Focal Breeze Briefing: {$name}",
            'sentiment' => $sentiment,
            'executive_summary' => trim($summary),
            'key_milestones' => $keyMilestones,
            'recommended_next_action' => $recommendation,
            'touchpoints_analyzed' => $activityCount,
        ];
    }
}
