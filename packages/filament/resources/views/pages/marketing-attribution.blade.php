<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        {{-- Header & Model Switcher --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Multi-Touch Attribution & Campaign ROI</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Evaluate campaign revenue influence, customer acquisition costs, and financial return on marketing investment.
                </p>
            </div>

            <div class="inline-flex items-center gap-2 bg-white dark:bg-slate-900 p-1.5 rounded-lg border border-slate-200 dark:border-slate-800 shadow-sm">
                <span class="text-xs font-semibold text-slate-500 pl-2">Model:</span>
                <select wire:model.live="selectedModel" class="text-xs font-medium rounded-md border-0 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white py-1 pl-2.5 pr-8 focus:ring-sky-500">
                    <option value="first_touch">First-Touch Attribution (Acquisition)</option>
                    <option value="last_touch">Last-Touch Attribution (Conversion)</option>
                    <option value="linear">Linear Attribution (Equal multi-touch)</option>
                    <option value="w_shaped">W-Shaped Attribution (30/30/30/10)</option>
                </select>
            </div>
        </div>

        {{-- 6 KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Allocated Budget</span>
                <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">
                    ${{ number_format($this->totalBudget, 2) }}
                </div>
                <span class="text-xs text-slate-400 mt-1">Across all campaigns</span>
            </div>

            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Actual Direct Spend</span>
                <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">
                    ${{ number_format($this->totalCost, 2) }}
                </div>
                <span class="text-xs text-slate-400 mt-1">Direct marketing costs</span>
            </div>

            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Attributed Won ARR</span>
                <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">
                    ${{ number_format($this->totalAttributedWon, 2) }}
                </div>
                <span class="text-xs text-slate-400 mt-1">Closed deals credited</span>
            </div>

            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Influenced Pipeline</span>
                <div class="text-2xl font-extrabold text-sky-600 dark:text-sky-400 mt-1">
                    ${{ number_format($this->totalAttributedPipeline, 2) }}
                </div>
                <span class="text-xs text-slate-400 mt-1">Active open opportunities</span>
            </div>

            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Marketing ROI</span>
                <div class="text-2xl font-extrabold {{ $this->overallRoi >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} mt-1">
                    {{ $this->overallRoi }}%
                </div>
                <span class="text-xs text-slate-400 mt-1">Net return on spend</span>
            </div>

            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Blended CPL</span>
                <div class="text-2xl font-extrabold text-purple-600 dark:text-purple-400 mt-1">
                    ${{ number_format($this->blendedCpl, 2) }}
                </div>
                <span class="text-xs text-slate-400 mt-1">{{ $this->totalLeads }} total leads generated</span>
            </div>
        </div>

        {{-- Campaign Performance Breakdown Table --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Campaign Financial ROI Breakdown</h3>
                <span class="text-xs text-slate-500">Weighted via {{ ucwords(str_replace('_', ' ', $this->selectedModel)) }} Model</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 uppercase tracking-wider font-semibold">
                            <th class="py-3 px-4">Campaign</th>
                            <th class="py-3 px-4">Topic Channel</th>
                            <th class="py-3 px-4 text-right">Spend</th>
                            <th class="py-3 px-4 text-center">Leads</th>
                            <th class="py-3 px-4 text-center">Deals</th>
                            <th class="py-3 px-4 text-right">Attributed Revenue</th>
                            <th class="py-3 px-4 text-right">Net Profit</th>
                            <th class="py-3 px-4 text-right">ROI %</th>
                            <th class="py-3 px-4 text-right">CPL</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($this->campaignsData as $row)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                    <a href="{{ \Odden\Filament\Resources\CampaignResource::getUrl('edit', ['record' => $row['campaign']->id]) }}" class="hover:underline text-sky-600 dark:text-sky-400">
                                        {{ $row['campaign_name'] }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $row['campaign']->topic ? ucfirst(str_replace('_', ' ', $row['campaign']->topic)) : 'General' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-medium">
                                    ${{ number_format($row['actual_cost'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $row['leads_count'] }}</span>
                                </td>
                                <td class="py-3 px-4 text-center font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $row['deals_count'] }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                    ${{ number_format($row['attributed_won_revenue'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold {{ $row['net_profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    ${{ number_format($row['net_profit'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-extrabold {{ $row['roi_percentage'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ $row['roi_percentage'] }}%
                                </td>
                                <td class="py-3 px-4 text-right text-purple-600 dark:text-purple-400 font-medium">
                                    ${{ number_format($row['cost_per_lead'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-500">
                                    No marketing campaigns found. Create campaigns to track attribution.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
