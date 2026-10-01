<x-filament-panels::page>
    <style>
        .sa-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            color: #0f172a;
            font-family: inherit;
        }

        :where(.dark, .dark *) .sa-container {
            color: #f8fafc;
        }

        .sa-top-bar {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        @media (min-width: 640px) {
            .sa-top-bar {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .sa-filter-group {
            display: inline-flex;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            overflow: hidden;
            background: #ffffff;
        }

        :where(.dark, .dark *) .sa-filter-group {
            border-color: #1f2937;
            background: #111827;
        }

        .sa-filter-btn {
            padding: 0.375rem 0.875rem;
            font-size: 0.8125rem;
            font-weight: 600;
            background: transparent;
            border: none;
            cursor: pointer;
            color: #64748b;
            transition: all 0.15s ease;
        }

        :where(.dark, .dark *) .sa-filter-btn {
            color: #9ca3af;
        }

        .sa-filter-btn.active {
            background: #0284c7;
            color: #ffffff;
        }

        :where(.dark, .dark *) .sa-filter-btn.active {
            background: #0284c7;
            color: #ffffff;
        }

        /* 5-Card KPI Grid */
        .sa-kpi-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }

        @media (min-width: 640px) {
            .sa-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {
            .sa-kpi-grid {
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }
        }

        .sa-card {
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

        :where(.dark, .dark *) .sa-card {
            background: #111827;
            border-color: #1f2937;
        }

        .sa-card-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        :where(.dark, .dark *) .sa-card-title {
            color: #9ca3af;
        }

        .sa-card-value {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.1;
            color: #0f172a;
        }

        :where(.dark, .dark *) .sa-card-value {
            color: #f8fafc;
        }

        .sa-card-subtitle {
            font-size: 0.8125rem;
            color: #64748b;
        }

        :where(.dark, .dark *) .sa-card-subtitle {
            color: #9ca3af;
        }

        /* 2-Column Middle Grid */
        .sa-split-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.25rem;
        }

        @media (min-width: 1024px) {
            .sa-split-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .sa-section-panel {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03);
        }

        :where(.dark, .dark *) .sa-section-panel {
            background: #111827;
            border-color: #1f2937;
        }

        .sa-section-header {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sa-bar-row {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
            margin-bottom: 0.875rem;
        }

        .sa-bar-header {
            display: flex;
            justify-content: space-between;
            font-size: 0.8125rem;
            font-weight: 600;
        }

        .sa-progress-track {
            height: 0.5rem;
            background: #f1f5f9;
            border-radius: 9999px;
            overflow: hidden;
        }

        :where(.dark, .dark *) .sa-progress-track {
            background: #1f2937;
        }

        .sa-progress-fill {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.3s ease;
        }

        /* Agent Table */
        .sa-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .sa-table th {
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

        :where(.dark, .dark *) .sa-table th {
            background: #182234;
            color: #9ca3af;
            border-bottom-color: #1f2937;
        }

        .sa-table td {
            padding: 0.875rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        :where(.dark, .dark *) .sa-table td {
            border-bottom-color: #1f2937;
        }
    </style>

    <div class="sa-container">
        {{-- Top Bar & Date Range Selector --}}
        <div class="sa-top-bar">
            <div>
                <h2 style="font-size: 1.125rem; font-weight: 700;">Service Performance Overview</h2>
                <p style="font-size: 0.8125rem; color: #64748b;" class="dark:text-slate-400">
                    Track SLA compliance, response speeds, and customer satisfaction metrics.
                </p>
            </div>

            <div class="sa-filter-group">
                <button
                    type="button"
                    wire:click="setDateRange('7_days')"
                    class="sa-filter-btn {{ $dateRange === '7_days' ? 'active' : '' }}"
                >
                    7 Days
                </button>
                <button
                    type="button"
                    wire:click="setDateRange('30_days')"
                    class="sa-filter-btn {{ $dateRange === '30_days' ? 'active' : '' }}"
                >
                    30 Days
                </button>
                <button
                    type="button"
                    wire:click="setDateRange('this_month')"
                    class="sa-filter-btn {{ $dateRange === 'this_month' ? 'active' : '' }}"
                >
                    This Month
                </button>
                <button
                    type="button"
                    wire:click="setDateRange('all_time')"
                    class="sa-filter-btn {{ $dateRange === 'all_time' ? 'active' : '' }}"
                >
                    All Time
                </button>
            </div>
        </div>

        @php $stats = $this->summaryStats; @endphp

        {{-- 5 KPI Cards --}}
        <div class="sa-kpi-grid">
            <div class="sa-card">
                <div class="sa-card-title">
                    <span>Total Inbound</span>
                    <x-filament::icon icon="heroicon-m-inbox" class="w-4 h-4 text-sky-500" />
                </div>
                <div class="sa-card-value text-sky-600 dark:text-sky-400">
                    {{ $stats['total_tickets'] }}
                </div>
                <div class="sa-card-subtitle">
                    {{ $stats['resolved_tickets'] }} resolved ({{ $stats['resolution_rate'] }}%)
                </div>
            </div>

            <div class="sa-card">
                <div class="sa-card-title">
                    <span>Avg First Response</span>
                    <x-filament::icon icon="heroicon-m-bolt" class="w-4 h-4 text-emerald-500" />
                </div>
                <div class="sa-card-value text-emerald-600 dark:text-emerald-400">
                    {{ $stats['avg_frt_formatted'] }}
                </div>
                <div class="sa-card-subtitle">
                    Initial agent reply time
                </div>
            </div>

            <div class="sa-card">
                <div class="sa-card-title">
                    <span>Avg Resolution Time</span>
                    <x-filament::icon icon="heroicon-m-check-badge" class="w-4 h-4 text-indigo-500" />
                </div>
                <div class="sa-card-value text-indigo-600 dark:text-indigo-400">
                    {{ $stats['avg_mttr_formatted'] }}
                </div>
                <div class="sa-card-subtitle">
                    Mean time to resolve (MTTR)
                </div>
            </div>

            <div class="sa-card">
                <div class="sa-card-title">
                    <span>SLA Compliance</span>
                    <x-filament::icon icon="heroicon-m-shield-check" class="w-4 h-4 text-teal-500" />
                </div>
                <div class="sa-card-value {{ $stats['sla_compliance_rate'] >= 95 ? 'text-teal-600 dark:text-teal-400' : 'text-amber-600 dark:text-amber-400' }}">
                    {{ $stats['sla_compliance_rate'] }}%
                </div>
                <div class="sa-card-subtitle">
                    {{ $stats['sla_breaches_total'] }} total breaches
                </div>
            </div>

            <div class="sa-card">
                <div class="sa-card-title">
                    <span>Customer CSAT</span>
                    <x-filament::icon icon="heroicon-m-star" class="w-4 h-4 text-amber-500" />
                </div>
                <div class="sa-card-value text-amber-500">
                    {{ $stats['csat_average'] !== null ? $stats['csat_average'] . ' / 5.0' : 'N/A' }}
                </div>
                <div class="sa-card-subtitle">
                    {{ $stats['csat_total_ratings'] }} customer survey ratings
                </div>
            </div>
        </div>

        {{-- Breakdown Grid: Channels & Priorities --}}
        <div class="sa-split-grid">
            {{-- Inbound Channels --}}
            <div class="sa-section-panel">
                <div class="sa-section-header">
                    <span>Tickets by Channel</span>
                    <span style="font-size: 0.8125rem; font-weight: 500; color: #64748b;">Volume Distribution</span>
                </div>
                @foreach ($this->channelBreakdown as $channel)
                    <div class="sa-bar-row">
                        <div class="sa-bar-header">
                            <span>{{ $channel['label'] }}</span>
                            <span>{{ $channel['count'] }} ({{ $channel['percentage'] }}%)</span>
                        </div>
                        <div class="sa-progress-track">
                            <div class="sa-progress-fill" style="width: {{ $channel['percentage'] }}%; background: #0284c7;"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Priority Breakdown --}}
            <div class="sa-section-panel">
                <div class="sa-section-header">
                    <span>Tickets by Priority</span>
                    <span style="font-size: 0.8125rem; font-weight: 500; color: #64748b;">Severity Levels</span>
                </div>
                @php
                    $colors = [
                        'urgent' => '#ef4444',
                        'high' => '#f97316',
                        'medium' => '#0284c7',
                        'low' => '#64748b',
                    ];
                @endphp
                @foreach ($this->priorityBreakdown as $key => $prio)
                    <div class="sa-bar-row">
                        <div class="sa-bar-header">
                            <span>{{ $prio['label'] }}</span>
                            <span>{{ $prio['count'] }} ({{ $prio['percentage'] }}%)</span>
                        </div>
                        <div class="sa-progress-track">
                            <div class="sa-progress-fill" style="width: {{ $prio['percentage'] }}%; background: {{ $colors[$key] ?? '#0284c7' }};"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Support Agent Leaderboard --}}
        <div class="sa-section-panel" style="padding: 0; overflow: hidden;">
            <div style="padding: 1.25rem; border-bottom: 1px solid #e2e8f0;" class="dark:border-gray-800">
                <h3 style="font-size: 1rem; font-weight: 700;">Support Agent Performance Leaderboard</h3>
                <p style="font-size: 0.8125rem; color: #64748b;" class="dark:text-slate-400">
                    Individual representative workloads, resolution volume, and satisfaction ratings.
                </p>
            </div>

            @php $agents = $this->agentPerformance; @endphp
            @if (empty($agents))
                <div style="padding: 2.5rem; text-align: center; color: #64748b;">
                    <x-filament::icon icon="heroicon-o-users" class="w-10 h-10 mx-auto text-slate-400 mb-2" />
                    <p class="text-sm">No ticket assignments recorded in this timeframe.</p>
                </div>
            @else
                <table class="sa-table">
                    <thead>
                        <tr>
                            <th>Support Agent</th>
                            <th style="text-align: center;">Assigned Tickets</th>
                            <th style="text-align: center;">Resolved</th>
                            <th style="text-align: center;">SLA Breaches</th>
                            <th style="text-align: right;">Average CSAT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($agents as $agent)
                            <tr>
                                <td>
                                    <div style="font-weight: 700;">{{ $agent['name'] }}</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">{{ $agent['email'] }}</div>
                                </td>
                                <td style="text-align: center; font-weight: 600;">
                                    {{ $agent['assigned_count'] }}
                                </td>
                                <td style="text-align: center; font-weight: 600; color: #059669;">
                                    {{ $agent['resolved_count'] }}
                                </td>
                                <td style="text-align: center; font-weight: 600; {{ $agent['breach_count'] > 0 ? 'color: #dc2626;' : 'color: #64748b;' }}">
                                    {{ $agent['breach_count'] }}
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #d97706;">
                                    {{ $agent['avg_csat'] !== null ? $agent['avg_csat'] . ' ★' : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-filament-panels::page>
