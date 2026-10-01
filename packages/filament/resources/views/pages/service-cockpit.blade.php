<x-filament-panels::page>
    <style>
        .service-cockpit {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            color: #0f172a;
            font-family: inherit;
        }

        :where(.dark, .dark *) .service-cockpit {
            color: #f8fafc;
        }

        /* Top Header */
        .sc-header {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        @media (min-width: 768px) {
            .sc-header {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .sc-title-area {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sc-rep-select {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            background: transparent;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            padding: 0.25rem 0.625rem;
            cursor: pointer;
            outline: none;
        }

        :where(.dark, .dark *) .sc-rep-select {
            color: #f8fafc;
            border-color: #374151;
            background: #1f2937;
        }

        .sc-filters {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.625rem;
        }

        .sc-input {
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            background: #ffffff;
            color: inherit;
            outline: none;
        }

        :where(.dark, .dark *) .sc-input {
            background: #1f2937;
            border-color: #374151;
        }

        /* Metric KPI Cards */
        .sc-kpis {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }

        @media (min-width: 640px) {
            .sc-kpis {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {
            .sc-kpis {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        .sc-kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1.125rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            position: relative;
            overflow: hidden;
        }

        .sc-kpi-card:hover {
            border-color: #94a3b8;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07);
        }

        :where(.dark, .dark *) .sc-kpi-card {
            background: #111827;
            border-color: #1f2937;
        }

        :where(.dark, .dark *) .sc-kpi-card:hover {
            border-color: #4b5563;
        }

        .sc-kpi-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.8125rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }

        :where(.dark, .dark *) .sc-kpi-header {
            color: #9ca3af;
        }

        .sc-kpi-value {
            font-size: 1.875rem;
            font-weight: 800;
            line-height: 1.1;
            color: #0f172a;
        }

        :where(.dark, .dark *) .sc-kpi-value {
            color: #f8fafc;
        }

        .sc-kpi-footer {
            font-size: 0.8125rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        :where(.dark, .dark *) .sc-kpi-footer {
            color: #9ca3af;
        }

        .sc-badge-pulse {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background-color: #ef4444;
            animation: sc-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes sc-pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.2); }
        }

        /* Workspace Tabs */
        .sc-tabs-bar {
            display: flex;
            gap: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 0.625rem;
            overflow-x: auto;
        }

        :where(.dark, .dark *) .sc-tabs-bar {
            border-bottom-color: #1f2937;
        }

        .sc-tab-btn {
            font-size: 0.9375rem;
            font-weight: 600;
            color: #64748b;
            background: transparent;
            border: none;
            padding-bottom: 0.625rem;
            margin-bottom: -0.6875rem;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .sc-tab-btn:hover {
            color: #0f172a;
        }

        :where(.dark, .dark *) .sc-tab-btn:hover {
            color: #f8fafc;
        }

        .sc-tab-btn.active {
            color: #0284c7;
            border-bottom-color: #0284c7;
        }

        :where(.dark, .dark *) .sc-tab-btn.active {
            color: #38bdf8;
            border-bottom-color: #38bdf8;
        }

        .sc-tab-badge {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            background: #f1f5f9;
            color: #475569;
        }

        :where(.dark, .dark *) .sc-tab-badge {
            background: #1f2937;
            color: #9ca3af;
        }

        .sc-tab-badge.highlight {
            background: #fee2e2;
            color: #b91c1c;
        }

        :where(.dark, .dark *) .sc-tab-badge.highlight {
            background: #7f1d1d;
            color: #fca5a5;
        }

        /* Main Table Cards */
        .sc-panel {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
        }

        :where(.dark, .dark *) .sc-panel {
            background: #111827;
            border-color: #1f2937;
        }

        .sc-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.875rem;
        }

        .sc-table th {
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        :where(.dark, .dark *) .sc-table th {
            background: #182234;
            color: #9ca3af;
            border-bottom-color: #1f2937;
        }

        .sc-table td {
            padding: 0.875rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        :where(.dark, .dark *) .sc-table td {
            border-bottom-color: #1f2937;
        }

        .sc-table tr:hover {
            background: #f8fafc;
        }

        :where(.dark, .dark *) .sc-table tr:hover {
            background: #192231;
        }

        /* Badges */
        .sc-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 0.1875rem 0.5rem;
            border-radius: 0.375rem;
            letter-spacing: 0.03em;
        }

        .sc-tag-urgent { background: #fee2e2; color: #991b1b; }
        .sc-tag-high { background: #ffedd5; color: #9a3412; }
        .sc-tag-medium { background: #e0f2fe; color: #0369a1; }
        .sc-tag-low { background: #f1f5f9; color: #475569; }

        :where(.dark, .dark *) .sc-tag-urgent { background: #7f1d1d; color: #fecaca; }
        :where(.dark, .dark *) .sc-tag-high { background: #7c2d12; color: #fed7aa; }
        :where(.dark, .dark *) .sc-tag-medium { background: #075985; color: #bae6fd; }
        :where(.dark, .dark *) .sc-tag-low { background: #374151; color: #e5e7eb; }

        .sc-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.3125rem 0.625rem;
            font-size: 0.8125rem;
            font-weight: 600;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
            border: 1px solid transparent;
        }

        .sc-btn-primary {
            background: #0284c7;
            color: #ffffff;
        }

        .sc-btn-primary:hover {
            background: #0369a1;
        }

        .sc-btn-secondary {
            background: #f1f5f9;
            color: #334155;
            border-color: #cbd5e1;
        }

        :where(.dark, .dark *) .sc-btn-secondary {
            background: #1f2937;
            color: #e2e8f0;
            border-color: #374151;
        }

        .sc-btn-secondary:hover {
            background: #e2e8f0;
        }

        :where(.dark, .dark *) .sc-btn-secondary:hover {
            background: #374151;
        }

        .sc-btn-success {
            background: #059669;
            color: #ffffff;
        }

        .sc-btn-success:hover {
            background: #047857;
        }

        /* Empty state */
        .sc-empty-state {
            padding: 3rem 1.5rem;
            text-align: center;
            color: #64748b;
        }

        :where(.dark, .dark *) .sc-empty-state {
            color: #9ca3af;
        }

        /* Modal Backdrop & Dialog */
        .sc-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(2px);
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .sc-modal-content {
            background: #ffffff;
            border-radius: 0.75rem;
            width: 100%;
            max-width: 34rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border: 1px solid #e2e8f0;
        }

        :where(.dark, .dark *) .sc-modal-content {
            background: #111827;
            border-color: #1f2937;
            color: #f8fafc;
        }

        .sc-modal-header {
            padding: 1.125rem 1.25rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        :where(.dark, .dark *) .sc-modal-header {
            border-bottom-color: #1f2937;
        }

        .sc-modal-body {
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .sc-modal-footer {
            padding: 1rem 1.25rem;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        :where(.dark, .dark *) .sc-modal-footer {
            background: #182234;
            border-top-color: #1f2937;
        }
    </style>

    <div class="service-cockpit">
        {{-- Top Navigation & Controls --}}
        <div class="sc-header">
            <div class="sc-title-area">
                <span style="font-size: 1.25rem; font-weight: 700;">Support Rep:</span>
                <select
                    wire:change="setSelectedUser($event.target.value)"
                    class="sc-rep-select"
                >
                    <option value="" @selected($selectedUserId === null)>All Agents / Unassigned</option>
                    @foreach ($this->users as $user)
                        <option value="{{ $user->id }}" @selected($selectedUserId === $user->id)>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sc-filters">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search ticket #, subject, contact..."
                    class="sc-input"
                    style="min-width: 220px;"
                />

                <select wire:change="setPriorityFilter($event.target.value)" class="sc-input">
                    <option value="">All Priorities</option>
                    @foreach (\Focal\Service\Enums\TicketPriority::cases() as $priority)
                        <option value="{{ $priority->value }}" @selected($priorityFilter === $priority->value)>
                            {{ $priority->getLabel() }}
                        </option>
                    @endforeach
                </select>

                <select wire:change="setSourceFilter($event.target.value)" class="sc-input">
                    <option value="">All Channels</option>
                    @foreach (\Focal\Service\Enums\TicketSource::cases() as $src)
                        <option value="{{ $src->value }}" @selected($sourceFilter === $src->value)>
                            {{ $src->getLabel() }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- 4 Metric KPI Cards --}}
        <div class="sc-kpis">
            <div class="sc-kpi-card" wire:click="setActiveTab('triage')">
                <div class="sc-kpi-header">
                    <span>Unassigned Triage</span>
                    @if ($this->unassignedTicketsCount > 0)
                        <span class="sc-badge-pulse" title="Needs assignment"></span>
                    @endif
                </div>
                <div class="sc-kpi-value" style="{{ $this->unassignedTicketsCount > 0 ? 'color: #ea580c;' : '' }}">
                    {{ $this->unassignedTicketsCount }}
                </div>
                <div class="sc-kpi-footer">
                    <span>Awaiting agent ownership</span>
                </div>
            </div>

            <div class="sc-kpi-card" wire:click="setActiveTab('my_tickets')">
                <div class="sc-kpi-header">
                    <span>My Active Queue</span>
                    <x-filament::icon icon="heroicon-m-user" class="w-4 h-4 text-sky-500" />
                </div>
                <div class="sc-kpi-value text-sky-600 dark:text-sky-400">
                    {{ $this->myActiveCount }}
                </div>
                <div class="sc-kpi-footer">
                    <span>Open tickets assigned to you</span>
                </div>
            </div>

            <div class="sc-kpi-card" wire:click="setActiveTab('sla_watch')">
                <div class="sc-kpi-header">
                    <span>SLA Risk / Breached</span>
                    <x-filament::icon icon="heroicon-m-clock" class="w-4 h-4 text-red-500" />
                </div>
                <div class="sc-kpi-value" style="{{ $this->slaAtRiskCount > 0 ? 'color: #dc2626;' : '' }}">
                    {{ $this->slaAtRiskCount }}
                </div>
                <div class="sc-kpi-footer">
                    <span>Breached or due &lt; 1 hour</span>
                </div>
            </div>

            <div class="sc-kpi-card">
                <div class="sc-kpi-header">
                    <span>Avg CSAT Rating</span>
                    <x-filament::icon icon="heroicon-m-star" class="w-4 h-4 text-amber-500" />
                </div>
                <div class="sc-kpi-value text-amber-500">
                    {{ $this->averageCsatRating !== null ? $this->averageCsatRating.' / 5.0' : 'N/A' }}
                </div>
                <div class="sc-kpi-footer">
                    <span>Customer satisfaction score</span>
                </div>
            </div>
        </div>

        {{-- Workspace Subnav Tabs --}}
        <div class="sc-tabs-bar">
            <button
                type="button"
                wire:click="setActiveTab('triage')"
                class="sc-tab-btn {{ $activeTab === 'triage' ? 'active' : '' }}"
            >
                <x-filament::icon icon="heroicon-m-inbox-stack" class="w-4 h-4" />
                <span>Triage Queue</span>
                <span class="sc-tab-badge {{ $this->unassignedTicketsCount > 0 ? 'highlight' : '' }}">
                    {{ $this->unassignedTicketsCount }}
                </span>
            </button>

            <button
                type="button"
                wire:click="setActiveTab('my_tickets')"
                class="sc-tab-btn {{ $activeTab === 'my_tickets' ? 'active' : '' }}"
            >
                <x-filament::icon icon="heroicon-m-user" class="w-4 h-4" />
                <span>My Tickets</span>
                <span class="sc-tab-badge">
                    {{ $this->myActiveCount }}
                </span>
            </button>

            <button
                type="button"
                wire:click="setActiveTab('sla_watch')"
                class="sc-tab-btn {{ $activeTab === 'sla_watch' ? 'active' : '' }}"
            >
                <x-filament::icon icon="heroicon-m-exclamation-triangle" class="w-4 h-4 text-red-500" />
                <span>SLA Watchlist</span>
                @if ($this->slaAtRiskCount > 0)
                    <span class="sc-tab-badge highlight">
                        {{ $this->slaAtRiskCount }}
                    </span>
                @endif
            </button>

            <button
                type="button"
                wire:click="setActiveTab('all')"
                class="sc-tab-btn {{ $activeTab === 'all' ? 'active' : '' }}"
            >
                <x-filament::icon icon="heroicon-m-queue-list" class="w-4 h-4" />
                <span>All Active Tickets</span>
                <span class="sc-tab-badge">
                    {{ $this->openTicketsCount }}
                </span>
            </button>
        </div>

        {{-- Tab Content 1: Triage Queue --}}
        @if ($activeTab === 'triage')
            <div class="sc-panel">
                @if ($this->triageTickets->isEmpty())
                    <div class="sc-empty-state">
                        <x-filament::icon icon="heroicon-o-check-circle" class="w-12 h-12 mx-auto text-emerald-500 mb-2" />
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">Triage Queue is Clear!</h3>
                        <p class="text-sm text-slate-500">All incoming tickets have been assigned to support agents.</p>
                    </div>
                @else
                    <table class="sc-table">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Requester</th>
                                <th>Priority</th>
                                <th>Channel</th>
                                <th>First Response Due</th>
                                <th>Created</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->triageTickets as $ticket)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: #0284c7;">
                                            <a href="{{ \Focal\Filament\Resources\TicketResource::getUrl('edit', ['record' => $ticket->id]) }}" class="hover:underline">
                                                {{ $ticket->ticket_number }}
                                            </a>
                                        </div>
                                        <div style="font-size: 0.8125rem; font-weight: 600; color: #334155;" class="dark:text-slate-200">
                                            {{ Str::limit($ticket->subject, 50) }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($ticket->contact)
                                            <div style="font-weight: 600;">{{ $ticket->contact->full_name }}</div>
                                            <div style="font-size: 0.75rem; color: #64748b;">{{ $ticket->contact->email }}</div>
                                        @else
                                            <span style="color: #94a3b8;">Guest Customer</span>
                                        @endif
                                        @if ($ticket->company)
                                            <div style="font-size: 0.75rem; color: #0284c7;">{{ $ticket->company->name }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="sc-tag sc-tag-{{ $ticket->priority->value }}">
                                            {{ $ticket->priority->getLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.75rem; color: #64748b; text-transform: capitalize;">
                                            {{ str_replace('_', ' ', $ticket->source->value) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($ticket->first_response_due_at)
                                            <div style="font-weight: 600; font-size: 0.8125rem; {{ $ticket->isFirstResponseBreached() ? 'color: #dc2626;' : 'color: #475569;' }}">
                                                {{ $ticket->first_response_due_at->diffForHumans() }}
                                                @if ($ticket->isFirstResponseBreached())
                                                    <span style="font-size: 0.6875rem; font-weight: 700; color: #dc2626; text-transform: uppercase;">(Breached)</span>
                                                @endif
                                            </div>
                                        @else
                                            <span style="color: #94a3b8;">—</span>
                                        @endif
                                    </td>
                                    <td style="color: #64748b; font-size: 0.8125rem;">
                                        {{ $ticket->created_at?->diffForHumans() }}
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: inline-flex; gap: 0.375rem;">
                                            <button
                                                type="button"
                                                wire:click="claimTicket({{ $ticket->id }})"
                                                class="sc-btn sc-btn-primary"
                                                title="Assign ticket to yourself"
                                            >
                                                Claim
                                            </button>
                                            <a
                                                href="{{ \Focal\Filament\Resources\TicketResource::getUrl('edit', ['record' => $ticket->id]) }}"
                                                class="sc-btn sc-btn-secondary"
                                            >
                                                Open
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif

        {{-- Tab Content 2: My Active Tickets --}}
        @if ($activeTab === 'my_tickets')
            <div class="sc-panel">
                @if ($this->myTickets->isEmpty())
                    <div class="sc-empty-state">
                        <x-filament::icon icon="heroicon-o-face-smile" class="w-12 h-12 mx-auto text-sky-500 mb-2" />
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">Your Queue is Empty!</h3>
                        <p class="text-sm text-slate-500">Pick up open tickets from the Triage Queue or take a break.</p>
                    </div>
                @else
                    <table class="sc-table">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Customer</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Next SLA Target</th>
                                <th style="text-align: right;">Quick Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->myTickets as $ticket)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: #0284c7;">
                                            <a href="{{ \Focal\Filament\Resources\TicketResource::getUrl('edit', ['record' => $ticket->id]) }}" class="hover:underline">
                                                {{ $ticket->ticket_number }}
                                            </a>
                                        </div>
                                        <div style="font-size: 0.8125rem; font-weight: 600; color: #334155;" class="dark:text-slate-200">
                                            {{ Str::limit($ticket->subject, 50) }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($ticket->contact)
                                            <div style="font-weight: 600;">{{ $ticket->contact->full_name }}</div>
                                            <div style="font-size: 0.75rem; color: #64748b;">{{ $ticket->contact->email }}</div>
                                        @else
                                            <span style="color: #94a3b8;">Guest Customer</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="sc-tag sc-tag-{{ $ticket->priority->value }}">
                                            {{ $ticket->priority->getLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">
                                            {{ $ticket->status->getLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if (! $ticket->first_responded_at && $ticket->first_response_due_at)
                                            <div style="font-size: 0.8125rem; font-weight: 600; {{ $ticket->isFirstResponseBreached() ? 'color: #dc2626;' : 'color: #475569;' }}">
                                                Resp: {{ $ticket->first_response_due_at->diffForHumans() }}
                                            </div>
                                        @elseif ($ticket->resolution_due_at)
                                            <div style="font-size: 0.8125rem; font-weight: 600; {{ $ticket->isResolutionBreached() ? 'color: #dc2626;' : 'color: #475569;' }}">
                                                Res: {{ $ticket->resolution_due_at->diffForHumans() }}
                                            </div>
                                        @else
                                            <span style="color: #94a3b8;">—</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: inline-flex; gap: 0.375rem;">
                                            <button
                                                type="button"
                                                wire:click="openReplyModal({{ $ticket->id }})"
                                                class="sc-btn sc-btn-primary"
                                            >
                                                Reply
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="openResolveModal({{ $ticket->id }})"
                                                class="sc-btn sc-btn-success"
                                            >
                                                Resolve
                                            </button>
                                            <a
                                                href="{{ \Focal\Filament\Resources\TicketResource::getUrl('edit', ['record' => $ticket->id]) }}"
                                                class="sc-btn sc-btn-secondary"
                                            >
                                                View
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif

        {{-- Tab Content 3: SLA Watchlist --}}
        @if ($activeTab === 'sla_watch')
            <div class="sc-panel">
                @if ($this->slaWatchTickets->isEmpty())
                    <div class="sc-empty-state">
                        <x-filament::icon icon="heroicon-o-shield-check" class="w-12 h-12 mx-auto text-emerald-500 mb-2" />
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">All SLA Targets Healthy!</h3>
                        <p class="text-sm text-slate-500">No tickets are currently breached or approaching their breach window.</p>
                    </div>
                @else
                    <table class="sc-table">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Assigned Agent</th>
                                <th>Priority</th>
                                <th>SLA Status</th>
                                <th>First Response Due</th>
                                <th>Resolution Due</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->slaWatchTickets as $ticket)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: #0284c7;">
                                            <a href="{{ \Focal\Filament\Resources\TicketResource::getUrl('edit', ['record' => $ticket->id]) }}" class="hover:underline">
                                                {{ $ticket->ticket_number }}
                                            </a>
                                        </div>
                                        <div style="font-size: 0.8125rem; font-weight: 600; color: #334155;" class="dark:text-slate-200">
                                            {{ Str::limit($ticket->subject, 50) }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($ticket->owner)
                                            <div style="font-weight: 600;">{{ $ticket->owner->name }}</div>
                                        @else
                                            <span style="color: #ea580c; font-weight: 600;">Unassigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="sc-tag sc-tag-{{ $ticket->priority->value }}">
                                            {{ $ticket->priority->getLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($ticket->isFirstResponseBreached() || $ticket->isResolutionBreached())
                                            <span style="background: #fee2e2; color: #b91c1c; font-size: 0.6875rem; font-weight: 800; padding: 0.25rem 0.5rem; border-radius: 0.375rem; text-transform: uppercase;">
                                                Breached
                                            </span>
                                        @else
                                            <span style="background: #fef3c7; color: #b45309; font-size: 0.6875rem; font-weight: 800; padding: 0.25rem 0.5rem; border-radius: 0.375rem; text-transform: uppercase;">
                                                At Risk (&lt;2h)
                                            </span>
                                        @endif
                                    </td>
                                    <td style="font-size: 0.8125rem;">
                                        @if ($ticket->first_response_due_at)
                                            <span style="{{ $ticket->isFirstResponseBreached() ? 'color: #dc2626; font-weight: 700;' : '' }}">
                                                {{ $ticket->first_response_due_at->diffForHumans() }}
                                            </span>
                                        @else
                                            <span style="color: #94a3b8;">—</span>
                                        @endif
                                    </td>
                                    <td style="font-size: 0.8125rem;">
                                        @if ($ticket->resolution_due_at)
                                            <span style="{{ $ticket->isResolutionBreached() ? 'color: #dc2626; font-weight: 700;' : '' }}">
                                                {{ $ticket->resolution_due_at->diffForHumans() }}
                                            </span>
                                        @else
                                            <span style="color: #94a3b8;">—</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: inline-flex; gap: 0.375rem;">
                                            @if ($ticket->owner_id === null)
                                                <button
                                                    type="button"
                                                    wire:click="claimTicket({{ $ticket->id }})"
                                                    class="sc-btn sc-btn-primary"
                                                >
                                                    Claim
                                                </button>
                                            @else
                                                <button
                                                    type="button"
                                                    wire:click="openReplyModal({{ $ticket->id }})"
                                                    class="sc-btn sc-btn-primary"
                                                >
                                                    Reply
                                                </button>
                                            @endif
                                            <a
                                                href="{{ \Focal\Filament\Resources\TicketResource::getUrl('edit', ['record' => $ticket->id]) }}"
                                                class="sc-btn sc-btn-secondary"
                                            >
                                                View
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif

        {{-- Tab Content 4: All Tickets --}}
        @if ($activeTab === 'all')
            <div class="sc-panel">
                @if ($this->allTickets->isEmpty())
                    <div class="sc-empty-state">
                        <x-filament::icon icon="heroicon-o-ticket" class="w-12 h-12 mx-auto text-slate-400 mb-2" />
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">No Tickets Found</h3>
                        <p class="text-sm text-slate-500">No tickets match your search or filters.</p>
                    </div>
                @else
                    <table class="sc-table">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Subject</th>
                                <th>Requester</th>
                                <th>Assigned To</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->allTickets as $ticket)
                                <tr>
                                    <td style="font-weight: 700; color: #0284c7;">
                                        <a href="{{ \Focal\Filament\Resources\TicketResource::getUrl('edit', ['record' => $ticket->id]) }}" class="hover:underline">
                                            {{ $ticket->ticket_number }}
                                        </a>
                                    </td>
                                    <td style="font-weight: 600; color: #334155;" class="dark:text-slate-200">
                                        {{ Str::limit($ticket->subject, 50) }}
                                    </td>
                                    <td>
                                        @if ($ticket->contact)
                                            <div>{{ $ticket->contact->full_name }}</div>
                                        @else
                                            <span style="color: #94a3b8;">Guest</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($ticket->owner)
                                            <div>{{ $ticket->owner->name }}</div>
                                        @else
                                            <span style="color: #ea580c; font-weight: 600;">Unassigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="sc-tag sc-tag-{{ $ticket->priority->value }}">
                                            {{ $ticket->priority->getLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">
                                            {{ $ticket->status->getLabel() }}
                                        </span>
                                    </td>
                                    <td style="color: #64748b; font-size: 0.8125rem;">
                                        {{ $ticket->created_at?->diffForHumans() }}
                                    </td>
                                    <td style="text-align: right;">
                                        <a
                                            href="{{ \Focal\Filament\Resources\TicketResource::getUrl('edit', ['record' => $ticket->id]) }}"
                                            class="sc-btn sc-btn-secondary"
                                        >
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif

        {{-- Quick Reply Modal --}}
        @if ($showReplyModal)
            @php $replyTicket = $this->getActiveTicketForReply(); @endphp
            <div class="sc-modal-backdrop">
                <div class="sc-modal-content">
                    <div class="sc-modal-header">
                        <div>
                            <div style="font-size: 1rem; font-weight: 700;">
                                Quick Reply: {{ $replyTicket?->ticket_number }}
                            </div>
                            <div style="font-size: 0.8125rem; color: #64748b;">
                                {{ $replyTicket?->subject }}
                            </div>
                        </div>
                        <button type="button" wire:click="closeReplyModal" style="background: none; border: none; cursor: pointer; color: #94a3b8;">
                            <x-filament::icon icon="heroicon-m-x-mark" class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="sc-modal-body">
                        {{-- Canned Response Selector --}}
                        @if ($this->cannedResponses->isNotEmpty())
                            <div>
                                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 0.25rem;">
                                    INSERT CANNED RESPONSE
                                </label>
                                <select
                                    wire:change="insertCannedResponse($event.target.value)"
                                    class="sc-input"
                                    style="width: 100%;"
                                >
                                    <option value="">Select a template...</option>
                                    @foreach ($this->cannedResponses as $canned)
                                        <option value="{{ $canned->id }}">
                                            [{{ $canned->category }}] {{ $canned->title }} ({{ $canned->shortcut }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- AI Copilot Suggested Articles --}}
                        @if ($this->suggestedArticles->isNotEmpty())
                            <div style="background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 8px; padding: 0.75rem;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span style="font-size: 0.6875rem; font-weight: 700; color: #4338ca; text-transform: uppercase; letter-spacing: 0.05em;">
                                        ⚡ Copilot Recommended Articles
                                    </span>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                                    @foreach ($this->suggestedArticles as $suggested)
                                        <div style="display: flex; align-items: center; justify-content: space-between; background: #ffffff; border: 1px solid #e0e7ff; border-radius: 6px; padding: 0.375rem 0.625rem; font-size: 0.8125rem;">
                                            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-right: 0.5rem;">
                                                <span style="font-weight: 600; color: #1e1b4b;">{{ $suggested['title'] }}</span>
                                                <span style="font-size: 0.6875rem; color: #64748b; margin-left: 0.25rem;">({{ $suggested['category'] }})</span>
                                            </div>
                                            <button
                                                type="button"
                                                wire:click="insertArticleLink('{{ addslashes($suggested['title']) }}', '{{ $suggested['url'] }}')"
                                                class="sc-btn sc-btn-secondary"
                                                style="padding: 0.25rem 0.5rem; font-size: 0.6875rem; white-space: nowrap;"
                                            >
                                                + Insert Guide Link
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Internal Note Toggle --}}
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <label style="display: inline-flex; align-items: center; gap: 0.375rem; font-size: 0.875rem; cursor: pointer;">
                                <input type="checkbox" wire:model.live="replyIsInternalNote" />
                                <span style="font-weight: 600;">Internal Note (hidden from customer)</span>
                            </label>
                        </div>

                        {{-- Reply Body --}}
                        <div>
                            <textarea
                                wire:model="replyBody"
                                rows="5"
                                placeholder="Type your response to the customer or internal note..."
                                class="sc-input"
                                style="width: 100%; resize: vertical;"
                            ></textarea>
                        </div>

                        {{-- Next Status --}}
                        @if (! $replyIsInternalNote)
                            <div>
                                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 0.25rem;">
                                    UPDATE TICKET STATUS AFTER REPLY
                                </label>
                                <select wire:model="replyStatus" class="sc-input" style="width: 100%;">
                                    <option value="waiting_on_customer">Waiting on Customer</option>
                                    <option value="open">Keep Open</option>
                                    <option value="resolved">Mark as Resolved</option>
                                </select>
                            </div>
                        @endif
                    </div>

                    <div class="sc-modal-footer">
                        <button type="button" wire:click="closeReplyModal" class="sc-btn sc-btn-secondary">
                            Cancel
                        </button>
                        <button type="button" wire:click="sendQuickReply" class="sc-btn sc-btn-primary">
                            {{ $replyIsInternalNote ? 'Save Internal Note' : 'Send Customer Reply' }}
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Quick Resolve Modal --}}
        @if ($showResolveModal)
            @php $resolveTicket = $this->getActiveTicketForResolve(); @endphp
            <div class="sc-modal-backdrop">
                <div class="sc-modal-content">
                    <div class="sc-modal-header">
                        <div>
                            <div style="font-size: 1rem; font-weight: 700;">
                                Resolve Ticket: {{ $resolveTicket?->ticket_number }}
                            </div>
                            <div style="font-size: 0.8125rem; color: #64748b;">
                                {{ $resolveTicket?->subject }}
                            </div>
                        </div>
                        <button type="button" wire:click="closeResolveModal" style="background: none; border: none; cursor: pointer; color: #94a3b8;">
                            <x-filament::icon icon="heroicon-m-x-mark" class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="sc-modal-body">
                        <div>
                            <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 0.375rem;">
                                Resolution Summary Note (Optional)
                            </label>
                            <textarea
                                wire:model="resolveNote"
                                rows="3"
                                placeholder="Explain how the ticket was resolved..."
                                class="sc-input"
                                style="width: 100%; resize: vertical;"
                            ></textarea>
                        </div>
                    </div>

                    <div class="sc-modal-footer">
                        <button type="button" wire:click="closeResolveModal" class="sc-btn sc-btn-secondary">
                            Cancel
                        </button>
                        <button type="button" wire:click="quickResolveTicket" class="sc-btn sc-btn-success">
                            Confirm Resolution
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
