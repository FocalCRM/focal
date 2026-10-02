<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        {{-- Navigation Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Marketing Campaign Calendar</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Monthly timeline of email broadcasts, scheduled drops, and A/B experiments.
                </p>
            </div>

            <div class="inline-flex items-center gap-2 bg-white dark:bg-slate-900 p-1 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <button
                    type="button"
                    wire:click="previousMonth"
                    class="p-2 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    title="Previous Month"
                >
                    <x-filament::icon icon="heroicon-m-chevron-left" class="w-5 h-5" />
                </button>

                <span class="px-3 font-bold text-sm text-slate-800 dark:text-slate-200 min-w-36 text-center">
                    {{ $this->currentDate->format('F Y') }}
                </span>

                <button
                    type="button"
                    wire:click="nextMonth"
                    class="p-2 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    title="Next Month"
                >
                    <x-filament::icon icon="heroicon-m-chevron-right" class="w-5 h-5" />
                </button>

                <button
                    type="button"
                    wire:click="currentMonth"
                    class="ml-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition"
                >
                    Today
                </button>
            </div>
        </div>

        @php
            $startOfMonth = $this->currentDate->copy()->startOfMonth();
            $daysInMonth = $startOfMonth->daysInMonth;
            $startDayOfWeek = $startOfMonth->dayOfWeek; // 0 = Sunday
            $today = now();
            $campaignsByDay = $this->campaignsByDay;
        @endphp

        {{-- Monthly Calendar Grid --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            {{-- Days of Week Headers --}}
            <div class="grid grid-cols-7 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 text-center text-xs font-bold text-slate-500 uppercase tracking-wider py-2.5">
                <div>Sun</div>
                <div>Mon</div>
                <div>Tue</div>
                <div>Wed</div>
                <div>Thu</div>
                <div>Fri</div>
                <div>Sat</div>
            </div>

            {{-- Calendar Day Cells --}}
            <div class="grid grid-cols-7 auto-rows-fr divide-x divide-y divide-slate-100 dark:divide-slate-800 border-b border-slate-200 dark:border-slate-800">
                {{-- Empty Leading Cells --}}
                @for ($offset = 0; $offset < $startDayOfWeek; $offset++)
                    <div class="min-h-28 bg-slate-50/50 dark:bg-slate-950/30 p-2"></div>
                @endfor

                {{-- Actual Month Days --}}
                @for ($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $isToday = $today->year === $this->year && $today->month === $this->month && $today->day === $day;
                        $dayCampaigns = $campaignsByDay[$day] ?? collect();
                    @endphp
                    <div class="min-h-28 p-2 flex flex-col justify-between transition hover:bg-slate-50/80 dark:hover:bg-slate-800/40 {{ $isToday ? 'bg-sky-50/40 dark:bg-sky-950/20' : '' }}">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold {{ $isToday ? 'w-6 h-6 rounded-full bg-sky-600 text-white flex items-center justify-center -ml-1' : 'text-slate-700 dark:text-slate-300' }}">
                                {{ $day }}
                            </span>
                            @if ($dayCampaigns->isNotEmpty())
                                <span class="text-[10px] text-slate-400 font-semibold">
                                    {{ $dayCampaigns->count() }} {{ Str::plural('event', $dayCampaigns->count()) }}
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-col gap-1 flex-grow overflow-y-auto max-h-32">
                            @foreach ($dayCampaigns as $c)
                                <a
                                    href="{{ \Odden\Filament\Resources\CampaignResource::getUrl('edit', ['record' => $c->id]) }}"
                                    class="block p-1.5 rounded text-xs border border-slate-200 dark:border-slate-700 hover:shadow-xs transition {{ $c->status === \Odden\Marketing\Enums\CampaignStatus::Sent ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 text-emerald-900 dark:text-emerald-200' : ($c->status === \Odden\Marketing\Enums\CampaignStatus::Scheduled ? 'bg-sky-50 dark:bg-sky-950/40 border-sky-200 text-sky-900 dark:text-sky-200' : 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200') }}"
                                >
                                    <div class="font-bold truncate text-[11px]">{{ $c->name }}</div>
                                    <div class="text-[10px] opacity-75 flex items-center justify-between mt-0.5">
                                        <span>{{ $c->status->getLabel() }}</span>
                                        @if ($c->delivered_count > 0)
                                            <span>{{ $c->delivered_count }} sent</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endfor

                {{-- Trailing Padding Cells --}}
                @php
                    $totalCells = $startDayOfWeek + $daysInMonth;
                    $trailing = (7 - ($totalCells % 7)) % 7;
                @endphp
                @for ($offset = 0; $offset < $trailing; $offset++)
                    <div class="min-h-28 bg-slate-50/50 dark:bg-slate-950/30 p-2"></div>
                @endfor
            </div>
        </div>
    </div>
</x-filament-panels::page>
