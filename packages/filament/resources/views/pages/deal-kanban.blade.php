<x-filament-panels::page>
    <style>
        .focal-kanban-wrapper {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .focal-header-bar {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        :where(.dark, .dark *) .focal-header-bar {
            background: #111827;
            border-color: #1f2937;
        }

        @media (min-width: 640px) {
            .focal-header-bar {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .focal-stats-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }

        @media (min-width: 640px) {
            .focal-stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {
            .focal-stats-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        .focal-stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }

        :where(.dark, .dark *) .focal-stat-card {
            background: #111827;
            border-color: #1f2937;
        }

        .focal-stat-label {
            font-size: 0.8125rem;
            font-weight: 500;
            color: #64748b;
        }

        :where(.dark, .dark *) .focal-stat-label {
            color: #94a3b8;
        }

        .focal-stat-value {
            font-size: 1.625rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 2rem;
            letter-spacing: -0.025em;
        }

        :where(.dark, .dark *) .focal-stat-value {
            color: #f8fafc;
        }

        .focal-stat-subtext {
            font-size: 0.75rem;
            font-weight: 500;
            color: #64748b;
        }

        :where(.dark, .dark *) .focal-stat-subtext {
            color: #94a3b8;
        }

        .focal-kanban-board {
            display: flex;
            flex-direction: row;
            gap: 1.25rem;
            overflow-x: auto;
            padding-bottom: 1.5rem;
            align-items: flex-start;
            min-height: 480px;
        }

        .focal-stage-column {
            flex: 0 0 21rem;
            width: 21rem;
            min-width: 21rem;
            max-height: calc(100vh - 16rem);
            display: flex;
            flex-direction: column;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
        }

        :where(.dark, .dark *) .focal-stage-column {
            background: #0f172a;
            border-color: #1e293b;
        }

        .focal-stage-header {
            padding: 0.875rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }

        :where(.dark, .dark *) .focal-stage-header {
            background: #1e293b;
            border-color: #334155;
        }

        .focal-stage-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }

        .focal-stage-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #0f172a;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        :where(.dark, .dark *) .focal-stage-title {
            color: #f8fafc;
        }

        .focal-stage-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.75rem;
            color: #64748b;
        }

        :where(.dark, .dark *) .focal-stage-meta {
            color: #94a3b8;
        }

        .focal-stage-dropzone {
            padding: 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            overflow-y: auto;
            flex: 1 1 auto;
            min-height: 280px;
            transition: background-color 0.15s ease, outline 0.15s ease;
        }

        .focal-stage-dropzone.is-dragging-over {
            background-color: rgba(249, 115, 22, 0.08);
            outline: 2px dashed #f97316;
            outline-offset: -2px;
        }

        .focal-deal-card {
            cursor: grab;
            user-select: none;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.875rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
        }

        :where(.dark, .dark *) .focal-deal-card {
            background: #1e293b;
            border-color: #334155;
        }

        .focal-deal-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        .focal-deal-card:active {
            cursor: grabbing;
        }

        .focal-deal-card.is-dragging {
            opacity: 0.35;
            transform: scale(0.97);
            border-style: dashed;
            border-color: #f97316;
        }

        .focal-card-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #0f172a;
            text-decoration: none;
            line-height: 1.25rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        :where(.dark, .dark *) .focal-card-title {
            color: #f8fafc;
        }

        .focal-card-title:hover {
            color: #f97316;
        }

        .focal-card-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .focal-card-amount {
            font-size: 0.9375rem;
            font-weight: 700;
            color: #0f172a;
        }

        :where(.dark, .dark *) .focal-card-amount {
            color: #f8fafc;
        }

        .focal-card-date {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.5rem;
        }

        :where(.dark, .dark *) .focal-card-date {
            color: #94a3b8;
        }

        .focal-card-entities {
            display: flex;
            flex-wrap: wrap;
            gap: 0.375rem;
            font-size: 0.75rem;
            color: #475569;
            margin-top: 0.5rem;
        }

        :where(.dark, .dark *) .focal-card-entities {
            color: #cbd5e1;
        }

        .focal-card-entity {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .focal-card-footer {
            margin-top: 0.625rem;
            padding-top: 0.5rem;
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }

        :where(.dark, .dark *) .focal-card-footer {
            border-top-color: #334155;
        }

        .focal-card-footer-label {
            font-size: 0.6875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
        }

        .focal-card-select {
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
            border-radius: 0.375rem;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #334155;
        }

        :where(.dark, .dark *) .focal-card-select {
            background: #0f172a;
            border-color: #334155;
            color: #e2e8f0;
        }

        .focal-empty-dropzone {
            padding: 2.5rem 1rem;
            text-align: center;
            font-size: 0.75rem;
            color: #94a3b8;
            border: 1px dashed #cbd5e1;
            border-radius: 0.5rem;
        }

        :where(.dark, .dark *) .focal-empty-dropzone {
            border-color: #334155;
            color: #64748b;
        }
    </style>

    <div class="focal-kanban-wrapper">
        {{-- Pipeline Header & Switcher --}}
        <div class="focal-header-bar">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span style="font-size: 0.875rem; font-weight: 600; color: #475569;" class="dark:text-gray-300">Active Pipeline:</span>
                <div style="min-width: 220px;">
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="pipelineId">
                            @foreach ($this->pipelines as $pipe)
                                <option value="{{ $pipe->id }}">{{ $pipe->name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>

            <div style="font-size: 0.75rem; color: #64748b;" class="dark:text-gray-400">
                Tip: Drag and drop deal cards directly into stage columns to update stages.
            </div>
        </div>

        {{-- Revenue Forecast KPI Cards --}}
        <div class="focal-stats-grid">
            <div class="focal-stat-card">
                <div class="focal-stat-label">Open Pipeline</div>
                <div class="focal-stat-value">
                    ${{ number_format($this->forecast['open_value'], 2) }}
                </div>
                <div class="focal-stat-subtext" style="color: #0284c7;">
                    {{ $this->forecast['open_count'] }} active open {{ \Illuminate\Support\Str::plural('deal', $this->forecast['open_count']) }}
                </div>
                @if ($this->forecast['stale_deals_count'] > 0)
                    <div style="font-size: 0.6875rem; color: #dc2626; font-weight: 600; margin-top: 0.25rem;">
                        ⚠️ {{ $this->forecast['stale_deals_count'] }} {{ \Illuminate\Support\Str::plural('deal', $this->forecast['stale_deals_count']) }} exceeding stage limit
                    </div>
                @endif
            </div>

            <div class="focal-stat-card">
                <div class="focal-stat-label">Weighted Forecast</div>
                <div class="focal-stat-value" style="color: #f97316;">
                    ${{ number_format($this->forecast['weighted_forecast'], 2) }}
                </div>
                <div class="focal-stat-subtext">
                    Probability-adjusted revenue
                </div>
            </div>

            <div class="focal-stat-card">
                <div class="focal-stat-label">Closed Won</div>
                <div class="focal-stat-value" style="color: #16a34a;">
                    ${{ number_format($this->forecast['won_value'], 2) }}
                </div>
                <div class="focal-stat-subtext" style="color: #16a34a;">
                    {{ $this->forecast['won_count'] }} {{ \Illuminate\Support\Str::plural('deal', $this->forecast['won_count']) }} won
                </div>
            </div>

            <div class="focal-stat-card">
                <div class="focal-stat-label">Win Rate</div>
                <div class="focal-stat-value" style="color: {{ $this->forecast['win_rate'] >= 50.0 ? '#16a34a' : '#d97706' }};">
                    {{ $this->forecast['win_rate'] }}%
                </div>
                <div class="focal-stat-subtext">
                    Avg deal: ${{ number_format($this->forecast['average_deal_size'], 0) }}
                </div>
            </div>
        </div>

        {{-- Kanban Columns Container with Drag & Drop --}}
        <div class="focal-kanban-board">
            @forelse ($this->stages as $stage)
                <div class="focal-stage-column">
                    {{-- Stage Header --}}
                    <div class="focal-stage-header">
                        <div class="focal-stage-header-row">
                            <h3 class="focal-stage-title">
                                {{ $stage->name }}
                            </h3>
                            <x-filament::badge :color="$stage->is_closed_won ? 'success' : ($stage->is_closed_lost ? 'danger' : 'gray')" size="sm">
                                {{ $stage->probability }}%
                            </x-filament::badge>
                        </div>
                        <div class="focal-stage-meta">
                            <span>{{ $stage->deals->count() }} {{ \Illuminate\Support\Str::plural('deal', $stage->deals->count()) }}</span>
                            <span style="font-weight: 600;">
                                ${{ number_format($stage->deals->sum('amount'), 2) }}
                            </span>
                        </div>
                    </div>

                    {{-- Stage Drop Zone --}}
                    <div
                        x-data="{ isDraggingOver: false }"
                        x-on:dragover.prevent="isDraggingOver = true"
                        x-on:dragleave.prevent="isDraggingOver = false"
                        x-on:drop.prevent="
                            isDraggingOver = false;
                            const dealId = event.dataTransfer.getData('deal-id');
                            if (dealId) {
                                $wire.moveDeal(dealId, {{ $stage->id }});
                            }
                        "
                        :class="{ 'is-dragging-over': isDraggingOver }"
                        class="focal-stage-dropzone"
                    >
                        @forelse ($stage->deals as $deal)
                            {{-- Draggable Deal Card --}}
                            <div
                                draggable="true"
                                x-data="{ isDragging: false }"
                                x-on:dragstart="
                                    event.dataTransfer.setData('deal-id', '{{ $deal->id }}');
                                    event.dataTransfer.effectAllowed = 'move';
                                    isDragging = true;
                                "
                                x-on:dragend="isDragging = false"
                                :class="{ 'is-dragging': isDragging }"
                                class="focal-deal-card"
                            >
                                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.5rem;">
                                    <a
                                        href="{{ \Focal\Filament\Resources\DealResource::getUrl('view', ['record' => $deal->id]) }}"
                                        class="focal-card-title"
                                    >
                                        {{ $deal->name }}
                                    </a>
                                </div>

                                <div class="focal-card-row">
                                    <span class="focal-card-amount">
                                        ${{ number_format((float) $deal->amount, 2) }}
                                    </span>
                                    <div style="display: flex; align-items: center; gap: 0.375rem;">
                                        @php $health = $deal->getHealthScore(); @endphp
                                        <x-filament::badge :color="$health['badge_color']" size="sm" :title="count($health['recommendations']) > 0 ? $health['recommendations'][0] : 'Healthy deal progression'">
                                            {{ $health['badge_label'] }}
                                        </x-filament::badge>
                                        @if ($deal->isRotten())
                                            <span style="display: inline-flex; align-items: center; gap: 0.2rem; font-size: 0.6875rem; padding: 0.1rem 0.35rem; border-radius: 0.25rem; background: #fee2e2; color: #991b1b; font-weight: 600;" class="dark:bg-red-950/80 dark:text-red-300" title="Exceeded stage limit ({{ $deal->stage?->rot_after_days }}d)">
                                                <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::ExclamationTriangle" style="width: 0.75rem; height: 0.75rem;" />
                                                {{ $deal->daysInCurrentStage() }}d
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if ($deal->status->isLost() && $deal->lost_reason)
                                    <div style="font-size: 0.6875rem; color: #dc2626; margin-top: 0.375rem; font-weight: 500;" class="dark:text-red-400">
                                        Loss: {{ \Focal\Sales\Enums\LostReason::tryFrom($deal->lost_reason)?->label() ?? $deal->lost_reason }}
                                    </div>
                                @endif

                                @if ($deal->expected_close_date)
                                    <div class="focal-card-date">
                                        <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::Calendar" style="width: 0.875rem; height: 0.875rem;" />
                                        <span>Target: {{ $deal->expected_close_date->format('M j, Y') }}</span>
                                    </div>
                                @endif

                                @if ($deal->companies->isNotEmpty() || $deal->contacts->isNotEmpty())
                                    <div class="focal-card-entities">
                                        @if ($company = $deal->companies->first())
                                            <span class="focal-card-entity">
                                                <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::BuildingOffice" style="width: 0.875rem; height: 0.875rem;" />
                                                {{ $company->name }}
                                            </span>
                                        @endif
                                        @if ($contact = $deal->contacts->first())
                                            <span class="focal-card-entity">
                                                <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::User" style="width: 0.875rem; height: 0.875rem;" />
                                                {{ $contact->first_name }} {{ $contact->last_name }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                {{-- Quick Stage Move Selector (Fallback for keyboard/mobile) --}}
                                <div class="focal-card-footer">
                                    <span class="focal-card-footer-label">Move:</span>
                                    <select
                                        wire:change="moveDeal({{ $deal->id }}, $event.target.value)"
                                        class="focal-card-select"
                                    >
                                        @foreach ($this->stages as $targetStage)
                                            <option value="{{ $targetStage->id }}" {{ $targetStage->id === $deal->stage_id ? 'selected' : '' }}>
                                                {{ $targetStage->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @empty
                            <div class="focal-empty-dropzone">
                                Drag deals here
                            </div>
                        @endforelse
                    </div>
                </div>
            @empty
                <div style="padding: 2.5rem; text-align: center; font-size: 0.875rem; width: 100%; border-radius: 0.75rem; border: 1px solid #e2e8f0; background: #ffffff;" class="dark:bg-gray-900 dark:border-gray-800 dark:text-gray-400">
                    No stages found for this pipeline.
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
