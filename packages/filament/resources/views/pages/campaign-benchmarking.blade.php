<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Multi-Campaign Benchmarking & Performance Comparison</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Compare engagement rates, click-to-open ratios (CTOR), unsubscribes, and lead generation efficiency across broadcasts.
            </p>
        </div>

        {{-- Campaign Selector Card --}}
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Select Campaigns to Compare (Up to 5)</span>
                <span class="text-xs text-sky-600 font-semibold">{{ count($this->selectedCampaignIds) }} selected</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                @foreach ($this->availableCampaigns as $c)
                    <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer text-xs transition {{ in_array($c->id, $this->selectedCampaignIds, true) ? 'bg-sky-50/50 dark:bg-sky-950/30 border-sky-300 dark:border-sky-700' : '' }}">
                        <input
                            type="checkbox"
                            value="{{ $c->id }}"
                            wire:model.live="selectedCampaignIds"
                            class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 w-3.5 h-3.5"
                        />
                        <div class="truncate">
                            <span class="font-medium text-slate-800 dark:text-slate-200 block truncate">{{ $c->name }}</span>
                            <span class="text-[10px] text-slate-400">{{ $c->delivered_count }} sent</span>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        @php
            $compared = $this->comparedCampaigns;
            $averages = $this->averages;

            $maxOpen = !empty($compared) ? max(array_column($compared, 'open_rate')) : 0;
            $maxClick = !empty($compared) ? max(array_column($compared, 'click_rate')) : 0;
            $maxCtor = !empty($compared) ? max(array_column($compared, 'ctor')) : 0;
            $minUnsub = !empty($compared) ? min(array_column($compared, 'unsub_rate')) : 0;
        @endphp

        {{-- Portfolio Benchmarks Bar --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Cohort Avg Open Rate</span>
                <div class="text-2xl font-extrabold text-sky-600 dark:text-sky-400 mt-1">{{ $averages['open_rate'] }}%</div>
                <span class="text-[11px] text-slate-400">Industry B2B avg: ~21.5%</span>
            </div>

            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Cohort Avg CTR</span>
                <div class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1">{{ $averages['click_rate'] }}%</div>
                <span class="text-[11px] text-slate-400">Industry B2B avg: ~2.8%</span>
            </div>

            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Avg Click-to-Open (CTOR)</span>
                <div class="text-2xl font-extrabold text-purple-600 dark:text-purple-400 mt-1">{{ $averages['ctor'] }}%</div>
                <span class="text-[11px] text-slate-400">Content resonance index</span>
            </div>

            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Avg Unsubscribe Rate</span>
                <div class="text-2xl font-extrabold text-slate-700 dark:text-slate-300 mt-1">{{ $averages['unsub_rate'] }}%</div>
                <span class="text-[11px] text-slate-400">Target: &lt; 0.5%</span>
            </div>
        </div>

        {{-- Comparative Side-by-Side Table --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 uppercase tracking-wider font-semibold">
                            <th class="py-3 px-4">Campaign</th>
                            <th class="py-3 px-4">Topic</th>
                            <th class="py-3 px-4 text-center">Delivered</th>
                            <th class="py-3 px-4 text-center">Open Rate</th>
                            <th class="py-3 px-4 text-center">Click Rate</th>
                            <th class="py-3 px-4 text-center">CTOR</th>
                            <th class="py-3 px-4 text-center">Unsub %</th>
                            <th class="py-3 px-4 text-center">Leads</th>
                            <th class="py-3 px-4 text-right">CPL</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($compared as $row)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                    <a href="{{ \Focal\Filament\Resources\CampaignResource::getUrl('edit', ['record' => $row['id']]) }}" class="hover:underline text-sky-600 dark:text-sky-400">
                                        {{ $row['name'] }}
                                    </a>
                                    <div class="text-[10px] text-slate-400 font-normal truncate max-w-xs">{{ $row['subject'] }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ ucfirst(str_replace('_', ' ', (string) $row['topic'])) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center font-medium">{{ number_format($row['delivered']) }}</td>
                                <td class="py-3 px-4 text-center font-bold text-sky-600 dark:text-sky-400">
                                    {{ $row['open_rate'] }}%
                                    @if ($row['open_rate'] === $maxOpen && $maxOpen > 0)
                                        <span class="inline-block ml-1 text-[9px] px-1 bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300 rounded font-bold">Top</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ $row['click_rate'] }}%
                                    @if ($row['click_rate'] === $maxClick && $maxClick > 0)
                                        <span class="inline-block ml-1 text-[9px] px-1 bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 rounded font-bold">Top</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-purple-600 dark:text-purple-400">
                                    {{ $row['ctor'] }}%
                                    @if ($row['ctor'] === $maxCtor && $maxCtor > 0)
                                        <span class="inline-block ml-1 text-[9px] px-1 bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300 rounded font-bold">Top</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center font-medium text-slate-600 dark:text-slate-400">
                                    {{ $row['unsub_rate'] }}%
                                    @if ($row['unsub_rate'] === $minUnsub && count($compared) > 1)
                                        <span class="inline-block ml-1 text-[9px] px-1 bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 rounded font-bold">Lowest</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $row['leads'] }}
                                </td>
                                <td class="py-3 px-4 text-right font-medium text-slate-700 dark:text-slate-300">
                                    ${{ number_format($row['cpl'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-500">
                                    Select one or more campaigns above to view benchmark comparison.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
