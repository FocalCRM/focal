<x-filament-panels::page>
    <style>
        .abm-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            color: #0f172a;
            font-family: inherit;
        }

        :where(.dark, .dark *) .abm-container {
            color: #f8fafc;
        }

        .abm-top-bar {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        @media (min-width: 640px) {
            .abm-top-bar {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .abm-actions {
            display: inline-flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .abm-btn {
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

        .abm-btn-primary {
            background: #0284c7;
            color: #ffffff;
        }

        .abm-btn-primary:hover {
            background: #0369a1;
        }

        .abm-btn-secondary {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
        }

        :where(.dark, .dark *) .abm-btn-secondary {
            background: #1f2937;
            color: #e2e8f0;
            border-color: #374151;
        }

        .abm-kpi-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }

        @media (min-width: 640px) {
            .abm-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {
            .abm-kpi-grid {
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }
        }

        .abm-card {
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

        :where(.dark, .dark *) .abm-card {
            background: #111827;
            border-color: #1f2937;
        }

        .abm-card-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        :where(.dark, .dark *) .abm-card-title {
            color: #9ca3af;
        }

        .abm-card-value {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .abm-card-subtitle {
            font-size: 0.8125rem;
            color: #64748b;
        }

        :where(.dark, .dark *) .abm-card-subtitle {
            color: #9ca3af;
        }

        .abm-panel {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03);
        }

        :where(.dark, .dark *) .abm-panel {
            background: #111827;
            border-color: #1f2937;
        }

        .abm-panel-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        @media (min-width: 640px) {
            .abm-panel-header {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        :where(.dark, .dark *) .abm-panel-header {
            border-bottom-color: #1f2937;
        }

        .abm-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .abm-table th {
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

        :where(.dark, .dark *) .abm-table th {
            background: #182234;
            color: #9ca3af;
            border-bottom-color: #1f2937;
        }

        .abm-table td {
            padding: 0.875rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        :where(.dark, .dark *) .abm-table td {
            border-bottom-color: #1f2937;
        }

        .abm-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            letter-spacing: 0.03em;
        }

        .abm-badge-tier1 { background: #fee2e2; color: #b91c1c; }
        .abm-badge-tier2 { background: #fef3c7; color: #b45309; }
        .abm-badge-tier3 { background: #f1f5f9; color: #475569; }
        .abm-badge-surge { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }

        :where(.dark, .dark *) .abm-badge-tier1 { background: #7f1d1d; color: #fecaca; }
        :where(.dark, .dark *) .abm-badge-tier2 { background: #78350f; color: #fde68a; }
        :where(.dark, .dark *) .abm-badge-tier3 { background: #374151; color: #e5e7eb; }
        :where(.dark, .dark *) .abm-badge-surge { background: #7c2d12; color: #fdba74; border-color: #9a3412; }

        .abm-tab-btn {
            padding: 0.375rem 0.75rem;
            font-size: 0.8125rem;
            font-weight: 600;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .abm-tab-active {
            background: #0284c7;
            color: #ffffff;
        }

        .abm-tab-inactive {
            background: transparent;
            color: #64748b;
        }

        .abm-tab-inactive:hover {
            color: #0f172a;
            background: #f1f5f9;
        }

        :where(.dark, .dark *) .abm-tab-inactive {
            color: #9ca3af;
        }

        :where(.dark, .dark *) .abm-tab-inactive:hover {
            color: #f8fafc;
            background: #1f2937;
        }
    </style>

    <div class="abm-container">
        {{-- Header & Quick Actions --}}
        <div class="abm-top-bar">
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 700;">Account-Based Marketing (ABM) Cockpit</h2>
                <p style="font-size: 0.8125rem; color: #64748b;" class="dark:text-slate-400">
                    Target account prioritization, multi-persona buying committee mapping, and real-time intent surge signals.
                </p>
            </div>

            <div class="abm-actions">
                <button
                    type="button"
                    wire:click="recalculateAll"
                    wire:loading.attr="disabled"
                    class="abm-btn abm-btn-primary"
                >
                    <x-filament::icon icon="heroicon-m-arrow-path" class="w-4 h-4" />
                    <span>Recalculate All Intent</span>
                </button>
                <a href="{{ \Odden\Filament\Resources\CompanyResource::getUrl('index') }}" class="abm-btn abm-btn-secondary">
                    <x-filament::icon icon="heroicon-m-building-office" class="w-4 h-4 text-sky-500" />
                    <span>All Accounts</span>
                </a>
                <a href="{{ \Odden\Filament\Pages\MarketingCockpit::getUrl() }}" class="abm-btn abm-btn-secondary">
                    <x-filament::icon icon="heroicon-m-megaphone" class="w-4 h-4 text-indigo-500" />
                    <span>Marketing Cockpit</span>
                </a>
            </div>
        </div>

        {{-- 5 KPI Metric Cards --}}
        <div class="abm-kpi-grid">
            <div class="abm-card">
                <div class="abm-card-title">
                    <span>Target Accounts</span>
                    <x-filament::icon icon="heroicon-m-building-office-2" class="w-4 h-4 text-sky-500" />
                </div>
                <div class="abm-card-value text-sky-600 dark:text-sky-400">
                    {{ $this->totalTargetAccounts }}
                </div>
                <div class="abm-card-subtitle">
                    Tier 1 & Tier 2 accounts
                </div>
            </div>

            <div class="abm-card">
                <div class="abm-card-title">
                    <span>Surging Intent</span>
                    <span class="text-xs">🔥</span>
                </div>
                <div class="abm-card-value text-orange-600 dark:text-orange-400">
                    {{ $this->surgingAccountsCount }}
                </div>
                <div class="abm-card-subtitle">
                    High buying momentum
                </div>
            </div>

            <div class="abm-card">
                <div class="abm-card-title">
                    <span>Tier 1 Enterprise</span>
                    <x-filament::icon icon="heroicon-m-shield-check" class="w-4 h-4 text-red-500" />
                </div>
                <div class="abm-card-value text-red-600 dark:text-red-400">
                    {{ $this->tier1AccountsCount }}
                </div>
                <div class="abm-card-subtitle">
                    Highest strategic value
                </div>
            </div>

            <div class="abm-card">
                <div class="abm-card-title">
                    <span>Avg Intent Score</span>
                    <x-filament::icon icon="heroicon-m-bolt" class="w-4 h-4 text-indigo-500" />
                </div>
                <div class="abm-card-value text-indigo-600 dark:text-indigo-400">
                    {{ $this->averageIntentScore }}
                </div>
                <div class="abm-card-subtitle">
                    Target account average
                </div>
            </div>

            <div class="abm-card">
                <div class="abm-card-title">
                    <span>Buying Committee</span>
                    <x-filament::icon icon="heroicon-m-user-group" class="w-4 h-4 text-emerald-500" />
                </div>
                <div class="abm-card-value text-emerald-600 dark:text-emerald-400">
                    {{ number_format($this->totalBuyingCommittee) }}
                </div>
                <div class="abm-card-subtitle">
                    Engaged stakeholder personas
                </div>
            </div>
        </div>

        {{-- Main ABM Account Matrix Table --}}
        <div class="abm-panel">
            <div class="abm-panel-header">
                <div>
                    <h3 style="font-size: 0.9375rem; font-weight: 700;">Prioritized Target Accounts & Intent Radar</h3>
                    <p style="font-size: 0.75rem; color: #64748b;">Ranked by real-time engagement surge and aggregate buyer intent score</p>
                </div>

                {{-- Tier Filter Tabs --}}
                <div style="display: inline-flex; gap: 0.25rem; background: #f8fafc; padding: 0.25rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;" class="dark:bg-slate-900 dark:border-slate-800">
                    <button
                        type="button"
                        wire:click="setTier('all')"
                        class="abm-tab-btn {{ $this->activeTier === 'all' ? 'abm-tab-active' : 'abm-tab-inactive' }}"
                    >
                        All Target ({{ $this->totalTargetAccounts }})
                    </button>
                    <button
                        type="button"
                        wire:click="setTier('surging')"
                        class="abm-tab-btn {{ $this->activeTier === 'surging' ? 'abm-tab-active' : 'abm-tab-inactive' }}"
                    >
                        Surging 🔥 ({{ $this->surgingAccountsCount }})
                    </button>
                    <button
                        type="button"
                        wire:click="setTier('tier_1')"
                        class="abm-tab-btn {{ $this->activeTier === 'tier_1' ? 'abm-tab-active' : 'abm-tab-inactive' }}"
                    >
                        Tier 1 ({{ $this->tier1AccountsCount }})
                    </button>
                    <button
                        type="button"
                        wire:click="setTier('tier_2')"
                        class="abm-tab-btn {{ $this->activeTier === 'tier_2' ? 'abm-tab-active' : 'abm-tab-inactive' }}"
                    >
                        Tier 2 ({{ $this->tier2AccountsCount }})
                    </button>
                </div>
            </div>

            @if ($this->accounts->isEmpty())
                <div style="padding: 3rem; text-align: center; color: #64748b;">
                    <x-filament::icon icon="heroicon-o-building-office-2" class="w-12 h-12 mx-auto text-slate-400 mb-2" />
                    <p class="text-sm font-semibold">No target accounts found for this view.</p>
                    <p class="text-xs text-slate-500 mt-1">Assign accounts to Tier 1 or Tier 2 in the Companies directory to start tracking intent.</p>
                    <a href="{{ \Odden\Filament\Resources\CompanyResource::getUrl('index') }}" class="abm-btn abm-btn-secondary mt-3">
                        Browse Companies
                    </a>
                </div>
            @else
                <table class="abm-table">
                    <thead>
                        <tr>
                            <th>Target Account</th>
                            <th>Tier</th>
                            <th>Intent Score & Signal</th>
                            <th style="text-align: center;">Buying Committee</th>
                            <th>Assigned Owner</th>
                            <th>Last Activity</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->accounts as $account)
                            <tr>
                                <td>
                                    <div style="font-weight: 700; color: #0284c7;">
                                        <a href="{{ \Odden\Filament\Resources\CompanyResource::getUrl('edit', ['record' => $account->id]) }}" class="hover:underline">
                                            {{ $account->name }}
                                        </a>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b;">
                                        {{ $account->domain ?: ($account->industry ?: 'Domain unassigned') }}
                                    </div>
                                </td>
                                <td>
                                    @if ($account->account_tier === 'tier_1')
                                        <span class="abm-badge abm-badge-tier1">Tier 1 Enterprise</span>
                                    @elseif ($account->account_tier === 'tier_2')
                                        <span class="abm-badge abm-badge-tier2">Tier 2 Mid-Market</span>
                                    @elseif ($account->account_tier === 'tier_3')
                                        <span class="abm-badge abm-badge-tier3">Tier 3 Growth</span>
                                    @else
                                        <span class="text-xs text-slate-400">Untiered</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span style="font-weight: 800; font-size: 1rem; color: {{ $account->intent_score >= 75 ? '#ea580c' : ($account->intent_score >= 30 ? '#0284c7' : '#64748b') }};">
                                            {{ $account->intent_score }}
                                        </span>
                                        @if ($account->intent_surge)
                                            <span class="abm-badge abm-badge-surge">
                                                🔥 Surging
                                            </span>
                                        @endif
                                    </div>
                                    <div style="width: 100px; height: 4px; background: #e2e8f0; border-radius: 9999px; overflow: hidden; margin-top: 0.25rem;" class="dark:bg-slate-700">
                                        <div style="width: {{ min(100, $account->intent_score) }}%; height: 100%; background: {{ $account->intent_surge ? '#ea580c' : '#0284c7' }};"></div>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: inline-flex; align-items: center; gap: 0.25rem; font-weight: 700; color: #0f172a;" class="dark:text-slate-200">
                                        <x-filament::icon icon="heroicon-m-user-group" class="w-4 h-4 text-emerald-500" />
                                        <span>{{ $account->buying_committee_size }}</span>
                                    </div>
                                    <div style="font-size: 0.6875rem; color: #64748b;">
                                        {{ $account->contacts_count ?? $account->contacts->count() }} contacts
                                    </div>
                                </td>
                                <td>
                                    @if ($account->owner)
                                        <span style="font-size: 0.8125rem; font-weight: 600; color: #334155;" class="dark:text-slate-300">
                                            {{ $account->owner->name ?? 'Sales Rep' }}
                                        </span>
                                    @else
                                        <span style="font-size: 0.75rem; color: #94a3b8; font-style: italic;">
                                            Unassigned
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-size: 0.75rem; color: #64748b;">
                                        {{ $account->last_intent_activity_at?->diffForHumans() ?? 'No recent activity' }}
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <button
                                        type="button"
                                        wire:click="recalculateCompany({{ $account->id }})"
                                        class="abm-btn abm-btn-secondary"
                                        style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                        title="Recalculate Intent"
                                    >
                                        <x-filament::icon icon="heroicon-m-arrow-path" class="w-3.5 h-3.5 text-sky-500" />
                                        <span>Sync</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-filament-panels::page>
