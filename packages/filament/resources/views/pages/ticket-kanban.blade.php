<x-filament-panels::page>
    <style>
        .ticket-kanban-board {
            display: flex;
            gap: 1.25rem;
            overflow-x: auto;
            padding-bottom: 1.5rem;
            align-items: flex-start;
            min-height: calc(100vh - 16rem);
        }

        .ticket-column {
            flex: 0 0 18rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 14rem);
        }

        :where(.dark, .dark *) .ticket-column {
            background: #0f172a;
            border-color: #1e293b;
        }

        .ticket-column-header {
            padding: 0.875rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        :where(.dark, .dark *) .ticket-column-header {
            border-color: #1e293b;
        }

        .ticket-column-cards {
            padding: 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            overflow-y: auto;
            flex: 1;
            min-height: 8rem;
        }

        .ticket-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.875rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            cursor: grab;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        :where(.dark, .dark *) .ticket-card {
            background: #1e293b;
            border-color: #334155;
        }

        .ticket-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        .ticket-card:active {
            cursor: grabbing;
        }

        .ticket-card.is-dragging {
            opacity: 0.4;
            border-style: dashed;
            border-color: #6366f1;
        }
    </style>

    <div x-data="{
        draggedTicketId: null,
        handleDragStart(e, id) {
            this.draggedTicketId = id;
            e.dataTransfer.effectAllowed = 'move';
            e.target.classList.add('is-dragging');
        },
        handleDragEnd(e) {
            e.target.classList.remove('is-dragging');
        },
        handleDrop(e, status) {
            e.preventDefault();
            if (this.draggedTicketId) {
                $wire.moveTicket(this.draggedTicketId, status);
                this.draggedTicketId = null;
            }
        }
    }">
        <div class="ticket-kanban-board">
            @foreach($this->columns as $col)
                @php
                    $status = $col['status'];
                    $tickets = $col['tickets'];
                @endphp
                <div class="ticket-column"
                     x-on:dragover.prevent
                     x-on:drop="handleDrop($event, '{{ $status->value }}')">
                    
                    <!-- Column Header -->
                    <div class="ticket-column-header">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-{{ $status->color() }}-500"></span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider">
                                {{ $status->label() }}
                            </span>
                        </div>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                            {{ $tickets->count() }}
                        </span>
                    </div>

                    <!-- Column Cards -->
                    <div class="ticket-column-cards">
                        @forelse($tickets as $ticket)
                            <div class="ticket-card"
                                 draggable="true"
                                 x-on:dragstart="handleDragStart($event, {{ $ticket->id }})"
                                 x-on:dragend="handleDragEnd($event)">
                                
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[11px] font-mono font-bold text-slate-500">
                                        {{ $ticket->ticket_number }}
                                    </span>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-{{ $ticket->priority->color() }}-50 text-{{ $ticket->priority->color() }}-700 border border-{{ $ticket->priority->color() }}-200">
                                        {{ $ticket->priority->label() }}
                                    </span>
                                </div>

                                <a href="{{ \Focal\Filament\Resources\TicketResource::getUrl('edit', ['record' => $ticket->id]) }}" 
                                   class="text-xs font-bold text-slate-900 dark:text-slate-100 hover:text-indigo-600 dark:hover:text-indigo-400 line-clamp-2 mb-2 transition">
                                    {{ $ticket->subject }}
                                </a>

                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mb-3 truncate">
                                    {{ $ticket->contact?->full_name ?? ($ticket->company?->name ?? 'No Contact') }}
                                </div>

                                <!-- SLA Timer Badge -->
                                <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800 text-[10px]">
                                    @if($ticket->isFirstResponseBreached())
                                        <span class="text-rose-600 dark:text-rose-400 font-bold flex items-center gap-1">
                                            🚨 Response Breached
                                        </span>
                                    @elseif($ticket->first_response_due_at && !$ticket->first_responded_at)
                                        <span class="text-slate-500 font-medium">
                                            ⏳ Due {{ $ticket->first_response_due_at->diffForHumans() }}
                                        </span>
                                    @elseif($ticket->status->isClosed())
                                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold">
                                            ✓ Completed
                                        </span>
                                    @else
                                        <span class="text-slate-400 font-medium">
                                            Active
                                        </span>
                                    @endif

                                    <span class="text-slate-400 font-medium truncate max-w-[80px]" title="{{ $ticket->owner?->name ?? 'Unassigned' }}">
                                        {{ $ticket->owner?->name ?? 'Unassigned' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="h-24 border border-dashed border-slate-200 dark:border-slate-800 rounded-lg flex items-center justify-center text-xs text-slate-400">
                                Drop tickets here
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
