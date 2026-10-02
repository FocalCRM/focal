<x-filament-panels::page>
    <style>
        .mc-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            color: #0f172a;
            font-family: inherit;
        }

        :where(.dark, .dark *) .mc-container {
            color: #f8fafc;
        }

        .mc-top-bar {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        @media (min-width: 640px) {
            .mc-top-bar {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .mc-actions {
            display: inline-flex;
            gap: 0.5rem;
        }

        .mc-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            font-size: 0.8125rem;
            font-weight: 600;
            border-radius: 0.375rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .mc-btn-primary {
            background: #0284c7;
            color: #ffffff;
        }

        .mc-btn-primary:hover {
            background: #0369a1;
        }

        .mc-btn-secondary {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
        }

        :where(.dark, .dark *) .mc-btn-secondary {
            background: #1f2937;
            color: #e2e8f0;
            border-color: #374151;
        }

        /* 5-Card KPI Grid */
        .mc-kpi-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }

        @media (min-width: 640px) {
            .mc-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {
            .mc-kpi-grid {
                grid-template-columns: repeat(6, minmax(0, 1fr));
            }
        }

        .mc-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1.125rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 0.5rem;
        }

        :where(.dark, .dark *) .mc-card {
            background: #111827;
            border-color: #1f2937;
        }

        .mc-card-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        :where(.dark, .dark *) .mc-card-title {
            color: #9ca3af;
        }

        .mc-card-value {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.1;
            color: #0f172a;
        }

        :where(.dark, .dark *) .mc-card-value {
            color: #f8fafc;
        }

        .mc-card-subtitle {
            font-size: 0.8125rem;
            color: #64748b;
        }

        :where(.dark, .dark *) .mc-card-subtitle {
            color: #9ca3af;
        }

        /* 2-Column Split Grid */
        .mc-split-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.25rem;
        }

        @media (min-width: 1024px) {
            .mc-split-grid {
                grid-template-columns: 1.5fr 1fr;
            }
        }

        .mc-panel {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03);
        }

        :where(.dark, .dark *) .mc-panel {
            background: #111827;
            border-color: #1f2937;
        }

        .mc-panel-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        :where(.dark, .dark *) .mc-panel-header {
            border-bottom-color: #1f2937;
        }

        .mc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .mc-table th {
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        :where(.dark, .dark *) .mc-table th {
            background: #182234;
            color: #9ca3af;
            border-bottom-color: #1f2937;
        }

        .mc-table td {
            padding: 0.875rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        :where(.dark, .dark *) .mc-table td {
            border-bottom-color: #1f2937;
        }

        .mc-badge {
            display: inline-flex;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            letter-spacing: 0.03em;
        }

        .mc-badge-sent { background: #dcfce7; color: #15803d; }
        .mc-badge-draft { background: #f1f5f9; color: #475569; }
        .mc-badge-sending { background: #fef3c7; color: #b45309; }

        :where(.dark, .dark *) .mc-badge-sent { background: #14532d; color: #bbf7d0; }
        :where(.dark, .dark *) .mc-badge-draft { background: #374151; color: #e5e7eb; }
        :where(.dark, .dark *) .mc-badge-sending { background: #78350f; color: #fde68a; }
    </style>

    <div class="mc-container">
        {{-- Header & Quick Actions --}}
        <div class="mc-top-bar">
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 700;">Marketing Command Center</h2>
                <p style="font-size: 0.8125rem; color: #64748b;" class="dark:text-slate-400">
                    Manage email broadcasts, track engagement rates, and monitor inbound lead capture.
                </p>
            </div>

            <div class="mc-actions">
                <a href="{{ \Odden\Filament\Pages\MarketingAttribution::getUrl() }}" class="mc-btn mc-btn-secondary">
                    <x-filament::icon icon="heroicon-m-chart-bar-square" class="w-4 h-4 text-sky-500" />
                    <span>Attribution & ROI</span>
                </a>
                <a href="{{ \Odden\Filament\Pages\UtmLinkBuilder::getUrl() }}" class="mc-btn mc-btn-secondary">
                    <x-filament::icon icon="heroicon-m-link" class="w-4 h-4 text-indigo-500" />
                    <span>UTM Builder</span>
                </a>
                <a href="{{ \Odden\Filament\Resources\CampaignResource::getUrl('create') }}" class="mc-btn mc-btn-primary">
                    <x-filament::icon icon="heroicon-m-plus" class="w-4 h-4" />
                    <span>New Campaign</span>
                </a>
                <a href="{{ \Odden\Filament\Resources\MarketingFormResource::getUrl('create') }}" class="mc-btn mc-btn-secondary">
                    <span>New Lead Form</span>
                </a>
            </div>
        </div>

        {{-- 5 KPI Metric Cards --}}
        <div class="mc-kpi-grid">
            <div class="mc-card">
                <div class="mc-card-title">
                    <span>Broadcasts Sent</span>
                    <x-filament::icon icon="heroicon-m-paper-airplane" class="w-4 h-4 text-sky-500" />
                </div>
                <div class="mc-card-value text-sky-600 dark:text-sky-400">
                    {{ $this->totalCampaignsCount }}
                </div>
                <div class="mc-card-subtitle">
                    Completed email campaigns
                </div>
            </div>

            <div class="mc-card">
                <div class="mc-card-title">
                    <span>Emails Delivered</span>
                    <x-filament::icon icon="heroicon-m-envelope" class="w-4 h-4 text-indigo-500" />
                </div>
                <div class="mc-card-value text-indigo-600 dark:text-indigo-400">
                    {{ number_format($this->totalDelivered) }}
                </div>
                <div class="mc-card-subtitle">
                    Successfully received
                </div>
            </div>

            <div class="mc-card">
                <div class="mc-card-title">
                    <span>Avg Open Rate</span>
                    <x-filament::icon icon="heroicon-m-eye" class="w-4 h-4 text-emerald-500" />
                </div>
                <div class="mc-card-value text-emerald-600 dark:text-emerald-400">
                    {{ $this->averageOpenRate }}%
                </div>
                <div class="mc-card-subtitle">
                    Unique email opens
                </div>
            </div>

            <div class="mc-card">
                <div class="mc-card-title">
                    <span>Click Rate (CTR)</span>
                    <x-filament::icon icon="heroicon-m-cursor-arrow-rays" class="w-4 h-4 text-purple-500" />
                </div>
                <div class="mc-card-value text-purple-600 dark:text-purple-400">
                    {{ $this->averageClickRate }}%
                </div>
                <div class="mc-card-subtitle">
                    Unique link clicks
                </div>
            </div>

            <div class="mc-card">
                <div class="mc-card-title">
                    <span>Leads Captured</span>
                    <x-filament::icon icon="heroicon-m-user-plus" class="w-4 h-4 text-amber-500" />
                </div>
                <div class="mc-card-value text-amber-500">
                    {{ number_format($this->totalLeadsCaptured) }}
                </div>
                <div class="mc-card-subtitle">
                    Form submissions
                </div>
            </div>

            <div class="mc-card">
                <div class="mc-card-title">
                    <span>MQL / SQL Leads</span>
                    <x-filament::icon icon="heroicon-m-sparkles" class="w-4 h-4 text-emerald-500" />
                </div>
                <div class="mc-card-value text-emerald-600 dark:text-emerald-400">
                    {{ number_format($this->qualifiedLeadsCount) }}
                </div>
                <div class="mc-card-subtitle">
                    Scored & Qualified
                </div>
            </div>
        </div>

        {{-- Closed-Loop Revenue & Velocity Intelligence --}}
        @php
            $closedLoop = $this->closedLoopMetrics;
        @endphp
        <div class="mc-panel" style="background: linear-gradient(135deg, rgba(2, 132, 199, 0.05) 0%, rgba(99, 102, 241, 0.05) 100%); border: 1px solid #bae6fd;">
            <div class="mc-panel-header" style="border-bottom: 1px solid #e0f2fe;">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-m-banknotes" class="w-5 h-5 text-sky-600" />
                    <div>
                        <h3 style="font-size: 0.9375rem; font-weight: 700;">Closed-Loop Revenue & Pipeline Velocity</h3>
                        <p style="font-size: 0.75rem; color: #64748b;">Direct marketing-influenced pipeline, revenue, and sales cycle duration</p>
                    </div>
                </div>
                <span class="mc-badge mc-badge-sent">HubSpot Enterprise Parity</span>
            </div>

            <div style="padding: 1rem 1.25rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                <div>
                    <div style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Influenced Pipeline</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #0284c7;">${{ number_format($closedLoop['total_influenced_pipeline'], 2) }}</div>
                    <div style="font-size: 0.75rem; color: #64748b;">Across {{ $closedLoop['open_deals_count'] + $closedLoop['won_deals_count'] }} marketing-touched deals</div>
                </div>
                <div>
                    <div style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Closed-Won Revenue</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #16a34a;">${{ number_format($closedLoop['total_closed_won_revenue'], 2) }}</div>
                    <div style="font-size: 0.75rem; color: #64748b;">From {{ $closedLoop['won_deals_count'] }} closed-won opportunities</div>
                </div>
                <div>
                    <div style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Marketing Spend & CAC</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #475569;">${{ number_format($closedLoop['total_marketing_spend'], 2) }}</div>
                    <div style="font-size: 0.75rem; color: #64748b;">CAC: ${{ number_format($closedLoop['blended_cac'], 2) }} &middot; CPL: ${{ number_format($closedLoop['cost_per_lead'], 2) }}</div>
                </div>
                <div>
                    <div style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Marketing ROI</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #059669;">{{ $closedLoop['marketing_roi_percentage'] }}%</div>
                    <div style="font-size: 0.75rem; color: #64748b;">Return on marketing spend</div>
                </div>
                <div>
                    <div style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Marketing Win Rate</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #8b5cf6;">{{ $closedLoop['marketing_win_rate'] }}%</div>
                    <div style="font-size: 0.75rem; color: #64748b;">Won vs. Lost closed opportunities</div>
                </div>
                <div>
                    <div style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Sales Cycle Velocity</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #ea580c;">{{ $closedLoop['average_sales_cycle_days'] }} <span style="font-size: 0.875rem; font-weight: 600;">days</span></div>
                    <div style="font-size: 0.75rem; color: #64748b;">Avg time from first touch to close</div>
                </div>
            </div>
        </div>

        {{-- Conversion Funnel Analytics Panel --}}
        @php
            $funnel = $this->conversionFunnel;
        @endphp
        <div class="mc-panel" style="margin-bottom: 1.5rem;">
            <div class="mc-panel-header">
                <div>
                    <h3 style="font-size: 0.9375rem; font-weight: 700;">Full-Funnel Conversion Analytics</h3>
                    <p style="font-size: 0.75rem; color: #64748b;">Visitor to closed-won milestone pipeline efficiency over the last {{ $funnel['time_window_days'] }} days</p>
                </div>
                <div style="font-size: 0.8125rem; font-weight: 700; color: #0284c7;">
                    Overall Funnel Conversion: {{ $funnel['overall_funnel_conversion_rate'] }}%
                </div>
            </div>

            <div style="padding: 1.25rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                @foreach ($funnel['steps'] as $step)
                    <div style="padding: 1rem; border-radius: 0.5rem; background: #f8fafc; border: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 0.5rem;" class="dark:bg-slate-900 dark:border-slate-800">
                        <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: flex; justify-content: space-between;">
                            <span>{{ $step['name'] }}</span>
                            <span style="color: #0284c7;">#{{ $step['index'] + 1 }}</span>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 800; color: #0f172a;" class="dark:text-white">
                            {{ number_format($step['count']) }}
                        </div>
                        <div style="font-size: 0.75rem; color: #64748b; display: flex; flex-direction: column; gap: 0.25rem;">
                            @if ($step['index'] > 0)
                                <div style="display: flex; justify-content: space-between;">
                                    <span>Step Conversion:</span>
                                    <strong style="color: {{ $step['conversion_rate'] >= 50 ? '#16a34a' : '#ea580c' }};">{{ $step['conversion_rate'] }}%</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span>Drop-off:</span>
                                    <span style="color: #dc2626;">-{{ number_format($step['dropoff_count']) }} ({{ $step['dropoff_rate'] }}%)</span>
                                </div>
                            @else
                                <div style="color: #16a34a; font-weight: 600;">Top of Funnel Discovery</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 2-Column Main Workspace --}}
        <div class="mc-split-grid">
            {{-- Column 1: Recent Broadcast Campaigns --}}
            <div class="mc-panel">
                <div class="mc-panel-header">
                    <div>
                        <h3 style="font-size: 0.9375rem; font-weight: 700;">Recent Broadcast Campaigns</h3>
                        <p style="font-size: 0.75rem; color: #64748b;">Delivered and scheduled email campaigns</p>
                    </div>
                    <a href="{{ \Odden\Filament\Resources\CampaignResource::getUrl('index') }}" class="text-xs font-semibold text-sky-600 hover:underline">
                        View All
                    </a>
                </div>

                @if ($this->recentCampaigns->isEmpty())
                    <div style="padding: 2.5rem; text-align: center; color: #64748b;">
                        <x-filament::icon icon="heroicon-o-paper-airplane" class="w-10 h-10 mx-auto text-slate-400 mb-2" />
                        <p class="text-sm font-medium">No campaigns created yet.</p>
                        <a href="{{ \Odden\Filament\Resources\CampaignResource::getUrl('create') }}" class="text-xs text-sky-600 hover:underline mt-1 inline-block">
                            Create your first email campaign
                        </a>
                    </div>
                @else
                    <table class="mc-table">
                        <thead>
                            <tr>
                                <th>Campaign</th>
                                <th>Status</th>
                                <th style="text-align: center;">Open Rate</th>
                                <th style="text-align: center;">Click Rate</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->recentCampaigns as $c)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: #0284c7;">
                                            <a href="{{ \Odden\Filament\Resources\CampaignResource::getUrl('edit', ['record' => $c->id]) }}" class="hover:underline">
                                                {{ $c->name }}
                                            </a>
                                        </div>
                                        <div style="font-size: 0.75rem; color: #64748b;">
                                            {{ Str::limit($c->subject, 40) }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="mc-badge mc-badge-{{ $c->status->value }}">
                                            {{ $c->status->getLabel() }}
                                        </span>
                                    </td>
                                    <td style="text-align: center; font-weight: 600; color: #0284c7;">
                                        {{ $c->open_rate }}%
                                    </td>
                                    <td style="text-align: center; font-weight: 600; color: #059669;">
                                        {{ $c->click_rate }}%
                                    </td>
                                    <td style="text-align: right;">
                                        @if (in_array($c->status, [\Odden\Marketing\Enums\CampaignStatus::Draft, \Odden\Marketing\Enums\CampaignStatus::Scheduled], true))
                                            <button
                                                type="button"
                                                wire:click="sendCampaignNow({{ $c->id }})"
                                                wire:confirm="Send campaign '{{ $c->name }}' now?"
                                                class="mc-btn mc-btn-primary"
                                                style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                            >
                                                Send
                                            </button>
                                        @else
                                            <span style="font-size: 0.75rem; color: #64748b;">
                                                {{ $c->sent_at?->diffForHumans() }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            {{-- Column 2: Lead Acquisition Engine --}}
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                {{-- Top Converting Forms --}}
                <div class="mc-panel">
                    <div class="mc-panel-header">
                        <div>
                            <h3 style="font-size: 0.9375rem; font-weight: 700;">Lead Capture Forms</h3>
                            <p style="font-size: 0.75rem; color: #64748b;">High conversion capture funnels</p>
                        </div>
                        <a href="{{ \Odden\Filament\Resources\MarketingFormResource::getUrl('index') }}" class="text-xs font-semibold text-sky-600 hover:underline">
                            View All
                        </a>
                    </div>

                    @if ($this->activeForms->isEmpty())
                        <div style="padding: 1.5rem; text-align: center; color: #64748b;">
                            <p class="text-xs">No active forms.</p>
                        </div>
                    @else
                        <div style="padding: 0.75rem 1rem;">
                            @foreach ($this->activeForms as $f)
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;" class="dark:border-gray-800">
                                    <div>
                                        <div style="font-weight: 600; font-size: 0.8125rem;">
                                            <a href="{{ $f->getPublicUrl() }}" target="_blank" class="hover:underline text-slate-900 dark:text-white">
                                                {{ $f->title }}
                                            </a>
                                        </div>
                                        <div style="font-size: 0.6875rem; color: #64748b;">
                                            /forms/{{ $f->slug }}
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <span style="font-size: 0.875rem; font-weight: 700; color: #0284c7;">
                                            {{ $f->submissions_count }}
                                        </span>
                                        <div style="font-size: 0.6875rem; color: #64748b;">leads</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Recent Submissions Feed --}}
                <div class="mc-panel">
                    <div class="mc-panel-header">
                        <h3 style="font-size: 0.9375rem; font-weight: 700;">Recent Submissions</h3>
                        <span style="font-size: 0.75rem; color: #64748b;">Live feed</span>
                    </div>

                    @if ($this->recentSubmissions->isEmpty())
                        <div style="padding: 1.5rem; text-align: center; color: #64748b;">
                            <p class="text-xs">No form submissions recorded yet.</p>
                        </div>
                    @else
                        <div style="padding: 0.5rem 1rem;">
                            @foreach ($this->recentSubmissions as $sub)
                                <div style="padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;" class="dark:border-gray-800">
                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                        <span style="font-weight: 600; font-size: 0.8125rem;">
                                            {{ $sub->contact?->full_name ?? ($sub->form_data['email'] ?? 'Anonymous') }}
                                        </span>
                                        <span style="font-size: 0.6875rem; color: #64748b;">
                                            {{ $sub->created_at?->diffForHumans() }}
                                        </span>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b;">
                                        via {{ $sub->form->title }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
