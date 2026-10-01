<div class="space-y-4 text-xs">
    <div class="flex items-center justify-between p-3 rounded-lg border {{ $briefing['sentiment'] === 'positive' ? 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800' : ($briefing['sentiment'] === 'at_risk' ? 'bg-rose-50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-800' : 'bg-gray-50 dark:bg-gray-900/50 border-gray-200 dark:border-gray-700') }}">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Account Standing</span>
            <div class="text-sm font-bold capitalize {{ $briefing['sentiment'] === 'positive' ? 'text-emerald-700 dark:text-emerald-400' : ($briefing['sentiment'] === 'at_risk' ? 'text-rose-700 dark:text-rose-400' : 'text-gray-700 dark:text-gray-300') }}">
                {{ str_replace('_', ' ', $briefing['sentiment']) }}
            </div>
        </div>
        <div class="text-right">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Touchpoints Analyzed</span>
            <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $briefing['touchpoints_analyzed'] }}</div>
        </div>
    </div>

    <div>
        <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-1">Executive Summary</h4>
        <p class="text-gray-600 dark:text-gray-300 leading-relaxed bg-gray-50 dark:bg-gray-900/40 p-3 rounded-lg border border-gray-200 dark:border-gray-700">
            {{ $briefing['executive_summary'] }}
        </p>
    </div>

    @if (! empty($briefing['key_milestones']))
        <div>
            <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-1.5">Recent Key Milestones</h4>
            <div class="space-y-1.5">
                @foreach ($briefing['key_milestones'] as $milestone)
                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 p-2 rounded border border-gray-100 dark:border-gray-700">
                        <x-filament::icon icon="heroicon-m-check-circle" class="w-3.5 h-3.5 text-primary-500 shrink-0" style="width: 0.875rem; height: 0.875rem;" />
                        <span class="truncate">{{ $milestone }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="p-3 bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-800 rounded-lg">
        <h4 class="text-[11px] font-bold text-indigo-700 dark:text-indigo-400 uppercase tracking-wider mb-1 flex items-center gap-1.5">
            <x-filament::icon icon="heroicon-m-light-bulb" class="w-3.5 h-3.5" style="width: 0.875rem; height: 0.875rem;" />
            Recommended Next Step
        </h4>
        <p class="text-indigo-900 dark:text-indigo-200 text-xs">
            {{ $briefing['recommended_next_action'] }}
        </p>
    </div>
</div>
