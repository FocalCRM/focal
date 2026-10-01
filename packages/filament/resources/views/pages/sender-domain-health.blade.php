<x-filament-panels::page>
    <div class="max-w-5xl flex flex-col gap-6">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Email Authentication & Deliverability Health</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Verify SPF, DKIM, DMARC, and MX DNS records to prevent spoofing, bypass spam filters, and comply with Gmail and Yahoo inbox placement standards.
            </p>
        </div>

        {{-- Domain Search & Selector Bar --}}
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex-grow flex flex-col sm:flex-row gap-3">
                <div class="flex-grow">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Sender Domain</label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="domain"
                        placeholder="e.g. yourcompany.com"
                        class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                </div>
                <div class="w-full sm:w-48">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">DKIM Selector</label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="selector"
                        placeholder="e.g. focal, k1, s1"
                        class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                </div>
            </div>

            <div class="sm:self-end">
                <button
                    type="button"
                    wire:click="checkNow"
                    class="bg-sky-600 hover:bg-sky-700 text-white font-medium text-xs py-2.5 px-4 rounded-lg transition inline-flex items-center gap-1.5 shadow-sm"
                >
                    <x-filament::icon icon="heroicon-m-arrow-path" class="w-4 h-4" />
                    <span>Run Diagnostic</span>
                </button>
            </div>
        </div>

        @php
            $diag = $this->diagnostics;
        @endphp

        {{-- Overall Health Banner --}}
        <div class="p-4 rounded-xl border flex items-center justify-between {{ $diag['overall_status'] === 'pass' ? 'bg-emerald-50 border-emerald-200 dark:bg-emerald-950/40 dark:border-emerald-800' : ($diag['overall_status'] === 'warning' ? 'bg-amber-50 border-amber-200 dark:bg-amber-950/40 dark:border-amber-800' : 'bg-rose-50 border-rose-200 dark:bg-rose-950/40 dark:border-rose-800') }}">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg {{ $diag['overall_status'] === 'pass' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-800 dark:text-emerald-200' : ($diag['overall_status'] === 'warning' ? 'bg-amber-100 text-amber-700 dark:bg-amber-800 dark:text-amber-200' : 'bg-rose-100 text-rose-700 dark:bg-rose-800 dark:text-rose-200') }}">
                    @if ($diag['overall_status'] === 'pass')
                        ✓
                    @elseif ($diag['overall_status'] === 'warning')
                        !
                    @else
                        ✕
                    @endif
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">
                        Domain Health for {{ $diag['domain'] }}:
                        <span class="{{ $diag['overall_status'] === 'pass' ? 'text-emerald-600 dark:text-emerald-400' : ($diag['overall_status'] === 'warning' ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">
                            {{ strtoupper($diag['overall_status']) }}
                        </span>
                    </h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400">
                        @if ($diag['overall_status'] === 'pass')
                            All critical email authentication standards are fully configured and ready for high-volume broadcasts.
                        @elseif ($diag['overall_status'] === 'warning')
                            Some records are in monitor mode or missing optional parameters. Check recommendations below.
                        @else
                            Critical DNS records are missing. Broadcasts will suffer high bounce rates and spam filtering.
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- 4-Card Breakdown: SPF, DKIM, DMARC, MX --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- 1. SPF --}}
            <div class="bg-white dark:bg-slate-900 p-5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between gap-3">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-sm text-slate-900 dark:text-white">1. SPF (Sender Policy Framework)</span>
                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold uppercase {{ $diag['spf']['status'] === 'pass' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300' }}">
                        {{ $diag['spf']['status'] }}
                    </span>
                </div>
                <p class="text-xs text-slate-500">{{ $diag['spf']['note'] }}</p>
                <div class="bg-slate-50 dark:bg-slate-800/60 p-3 rounded-lg font-mono text-[11px] text-slate-800 dark:text-slate-200 break-all">
                    {{ $diag['spf']['found'] ?? 'No SPF record detected in DNS TXT queries.' }}
                </div>
                <div class="text-[11px] text-slate-500">
                    <strong class="text-slate-700 dark:text-slate-300">Recommended Record:</strong>
                    <div class="font-mono text-slate-600 dark:text-slate-400 mt-0.5">{{ $diag['spf']['recommendation'] }}</div>
                </div>
            </div>

            {{-- 2. DMARC --}}
            <div class="bg-white dark:bg-slate-900 p-5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between gap-3">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-sm text-slate-900 dark:text-white">2. DMARC Alignment & Policy</span>
                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold uppercase {{ $diag['dmarc']['status'] === 'pass' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : ($diag['dmarc']['status'] === 'warning' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300') }}">
                        {{ $diag['dmarc']['status'] }}
                    </span>
                </div>
                <p class="text-xs text-slate-500">{{ $diag['dmarc']['note'] }}</p>
                <div class="bg-slate-50 dark:bg-slate-800/60 p-3 rounded-lg font-mono text-[11px] text-slate-800 dark:text-slate-200 break-all">
                    {{ $diag['dmarc']['found'] ?? 'No _dmarc TXT record found.' }}
                </div>
                <div class="text-[11px] text-slate-500">
                    <strong class="text-slate-700 dark:text-slate-300">Recommended Record:</strong>
                    <div class="font-mono text-slate-600 dark:text-slate-400 mt-0.5">{{ $diag['dmarc']['recommendation'] }}</div>
                </div>
            </div>

            {{-- 3. DKIM --}}
            <div class="bg-white dark:bg-slate-900 p-5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between gap-3">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-sm text-slate-900 dark:text-white">3. DKIM Signature Key</span>
                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold uppercase {{ $diag['dkim']['status'] === 'pass' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' }}">
                        {{ $diag['dkim']['status'] }}
                    </span>
                </div>
                <p class="text-xs text-slate-500">{{ $diag['dkim']['note'] }}</p>
                <div class="bg-slate-50 dark:bg-slate-800/60 p-3 rounded-lg font-mono text-[11px] text-slate-800 dark:text-slate-200 break-all">
                    {{ $diag['dkim']['found'] ?? 'No DKIM public key found for selector "' . $diag['dkim']['selector'] . '".' }}
                </div>
                <div class="text-[11px] text-slate-500">
                    <strong class="text-slate-700 dark:text-slate-300">Recommended Record:</strong>
                    <div class="font-mono text-slate-600 dark:text-slate-400 mt-0.5">{{ $diag['dkim']['recommendation'] }}</div>
                </div>
            </div>

            {{-- 4. MX --}}
            <div class="bg-white dark:bg-slate-900 p-5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between gap-3">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-sm text-slate-900 dark:text-white">4. MX Mail Routing</span>
                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold uppercase {{ $diag['mx']['status'] === 'pass' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300' }}">
                        {{ $diag['mx']['status'] }}
                    </span>
                </div>
                <p class="text-xs text-slate-500">{{ $diag['mx']['note'] }}</p>
                <div class="bg-slate-50 dark:bg-slate-800/60 p-3 rounded-lg font-mono text-[11px] text-slate-800 dark:text-slate-200">
                    @if (!empty($diag['mx']['found']))
                        @foreach ($diag['mx']['found'] as $mx)
                            <div>{{ $mx }}</div>
                        @endforeach
                    @else
                        No MX records published for this domain.
                    @endif
                </div>
                <div class="text-[11px] text-slate-500">
                    <strong class="text-slate-700 dark:text-slate-300">Recommended Record:</strong>
                    <div class="font-mono text-slate-600 dark:text-slate-400 mt-0.5">{{ $diag['mx']['recommendation'] }}</div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
