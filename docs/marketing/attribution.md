---
title: Attribution and closed-loop reporting
description: Attribute pipeline and revenue to campaigns, calculate closed-loop marketing ROI, and analyze multi-step conversion funnels.
---

Three actions report on how marketing turns into revenue. `GetCampaignAttributionAction` reports on a single campaign, `CalculateClosedLoopMetricsAction` reports on all marketing activity, and `AnalyzeConversionFunnelAction` counts how many people reach each step of a funnel you define. All of them run their queries when called; nothing is stored or scheduled.

Revenue figures come from deals in `focalcrm/sales`. Without that package installed, the revenue and deal values are zero. A deal counts as influenced by marketing when it's [associated](../core/associations.md) with a contact that marketing reached.

## Attribution models

`Focal\Marketing\Enums\AttributionModel` selects how much of the influenced revenue is credited to marketing:

| Case | Value |
| --- | --- |
| `FirstTouch` | `first_touch` |
| `LastTouch` | `last_touch` |
| `Linear` | `linear` |
| `UShaped` | `u_shaped` |
| `WShaped` | `w_shaped` |
| `TimeDecay` | `time_decay` |

`label()` returns a display name such as `U-Shaped Attribution (40/40/20 Weighting)`.

The models don't distribute credit across individual touchpoints. Each model is a fixed multiplier applied to the total influenced pipeline and won revenue, as shown in the tables below. Treat them as weighting presets for reporting, not as per-touch attribution. The labels' percentages describe the classic models; the code doesn't apply them.

## Campaign attribution

```php
use Focal\Marketing\Actions\GetCampaignAttributionAction;
use Focal\Marketing\Enums\AttributionModel;

$report = app(GetCampaignAttributionAction::class)->execute($campaign, AttributionModel::UShaped);

$report['won_revenue'];            // won deal amounts, unweighted
$report['attributed_won_revenue']; // won revenue × model weight
$report['roi_percentage'];
```

`execute(Campaign $campaign, AttributionModel $model = AttributionModel::Linear): array` collects two groups of contacts:

- **Leads**: contacts with a [form submission](forms-and-landing-pages.md#what-happens-on-submission) whose `utm_campaign` equals the campaign's `name`, or the name lowercased with spaces replaced by hyphens (`Q3 Launch` matches `Q3 Launch` and `q3-launch`).
- **Engaged contacts**: the campaign's recipients who opened or clicked.

It then sums the amounts of every deal associated with any of these contacts: `won` deals into won revenue and `open` deals into pipeline. Each deal is counted once.

The campaign's own `utm_campaign` field isn't used. Links tagged by [UTM auto-tagging](campaigns.md) use `Str::slug()` of the `utm_campaign` field or the name, so they match only when `utm_campaign` is empty and the name slugs the same way (letters, digits, and single spaces).

| Model | Weight |
| --- | --- |
| `FirstTouch` | 1.0 if the campaign has leads, else 0.5 |
| `LastTouch` | 1.0 if the campaign has engaged contacts, else 0.5 |
| `UShaped` | 0.8 if it has both leads and engaged contacts, else 0.6 |
| `WShaped` | 0.7 |
| `TimeDecay` | 0.65 |
| `Linear` | 0.5 |

The returned array:

| Key | Meaning |
| --- | --- |
| `campaign_name`, `attribution_model` | Echoed inputs. |
| `leads_count` | Distinct lead contacts. |
| `engaged_contacts_count` | Distinct engaged recipients. |
| `deals_count` | Distinct associated deals, any status. |
| `budget` | The campaign's `budget`, or `null`. |
| `actual_cost` | `actual_spend`, falling back to `actual_cost`, else 0. |
| `pipeline_value`, `won_revenue` | Unweighted open and won deal amounts. |
| `attributed_pipeline_value`, `attributed_won_revenue` | The same, multiplied by the weight. |
| `net_profit` | `attributed_won_revenue − actual_cost`. |
| `roi_percentage` | `net_profit / actual_cost × 100`, or 0 without a cost. |
| `cost_per_lead` | `actual_cost / leads_count`, or 0 without leads. |

For example, a campaign with one lead whose contact has a $5,000 won deal and $1,000 `actual_spend`, using `UShaped` without engaged recipients (weight 0.6), reports `attributed_won_revenue` 3000, `roi_percentage` 200, and `cost_per_lead` 1000.

Budget fields such as `budget`, `actual_spend`, and `target_revenue` are set on the campaign; see [campaigns](campaigns.md).

## Closed-loop metrics

```php
use Focal\Marketing\Actions\CalculateClosedLoopMetricsAction;
use Focal\Marketing\Enums\AttributionModel;

$metrics = app(CalculateClosedLoopMetricsAction::class)->execute(AttributionModel::Linear);
```

`execute(?AttributionModel $model = null): array` looks at every contact that marketing reached: any campaign recipient (whether or not they engaged) and any contact with a form submission. Influenced deals are the deals associated with those contacts, in either direction.

Total marketing spend is the sum of all campaigns' `actual_spend`, or, if that sum is 0, the sum of `actual_cost`.

| Key | Meaning |
| --- | --- |
| `total_influenced_pipeline` | Sum of all influenced deal amounts, any status. |
| `total_closed_won_revenue` | Sum of won influenced deals. |
| `total_marketing_spend` | See above. |
| `blended_cac` | Spend / won deals. |
| `cost_per_lead` | Spend / marketing-reached contacts. |
| `marketing_roi_percentage` | `(won revenue − spend) / spend × 100`, one decimal. |
| `won_deals_count`, `open_deals_count` | Influenced deals by status. |
| `marketing_win_rate` | Won / (won + lost) × 100, one decimal. |
| `average_sales_cycle_days` | Average days from the first associated contact's `created_at` to the deal's `closed_at` (or `updated_at`), minimum 1 per deal. |
| `top_campaigns` | Up to five campaigns with delivered emails and influenced pipeline, sorted by won revenue. Each has `name`, `won_revenue`, `pipeline_influenced`, `spend`, `roi_percentage`. |
| `attribution_model` | The model value, or `null`. |
| `attributed_closed_won_revenue`, `attributed_pipeline` | Won revenue and pipeline × model weight. |
| `attributed_roi_percentage` | ROI using the attributed won revenue. |

The weights here are 1.0 for `FirstTouch`, `LastTouch`, and no model, then 0.8 (`UShaped`), 0.7 (`WShaped`), 0.65 (`TimeDecay`), and 0.5 (`Linear`). When there are no marketing contacts or influenced deals, every value except the spend (and `cost_per_lead`, when there are contacts) is 0.

`top_campaigns` only finds deals where the contact is the parent of the association (`$contact->associateWith($deal)`), while the totals count both directions.

## Conversion funnels

`Focal\Marketing\Actions\AnalyzeConversionFunnelAction` counts each step of a funnel within a date range:

```php
use Focal\Marketing\Actions\AnalyzeConversionFunnelAction;

$funnel = app(AnalyzeConversionFunnelAction::class)->execute(
    steps: [
        ['name' => 'Viewed pricing', 'type' => 'page_view', 'path' => '/pricing'],
        ['name' => 'Requested a demo', 'type' => 'form_submission', 'form_slug' => 'request-a-demo'],
        ['name' => 'Closed won', 'type' => 'deal_won'],
    ],
    startDate: now()->subDays(90),
);

foreach ($funnel['steps'] as $step) {
    echo "{$step['name']}: {$step['count']} ({$step['conversion_rate']}% of previous step)";
}
```

`execute(array $steps, ?CarbonInterface $startDate = null, ?CarbonInterface $endDate = null): array` defaults to the last 30 days (from the start of the day 30 days ago to the end of today). Step types and what they count:

| `type` | Counts | Filters |
| --- | --- | --- |
| `page_view` or `page_visit` | Distinct [visitor sessions](web-tracking.md#sessions-and-page-views) with a matching pageview | `path`, matched as a substring |
| `form_submission` | Form submissions | `form_id` or `form_slug` |
| `behavioral_event` | Distinct contacts with a [custom event](inbound-webhooks.md#custom-behavioral-events) | `event_name` |
| `campaign_click` | Distinct recipient emails that clicked | `campaign_id` |
| `contact_created` | Contacts created | none |
| `deal_created` | Deals created (0 without `focalcrm/sales`) | none |
| `deal_won` | Deals won, by `closed_at` (0 without `focalcrm/sales`) | none |

Each step is counted independently over the whole date range; the action doesn't follow individual people from one step to the next. An unknown type counts 0.

The result has `steps` (each with `index`, `name`, `type`, `count`, `conversion_rate` from the previous step, `dropoff_count`, `dropoff_rate`, and `overall_conversion_rate` from the first step), `total_top_of_funnel`, `total_bottom_of_funnel`, `overall_funnel_conversion_rate`, and `time_window_days`. A step that follows a step with a count of 0 shows a 100% conversion rate.
