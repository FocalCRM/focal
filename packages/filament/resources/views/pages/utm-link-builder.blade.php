<x-filament-panels::page>
    <div class="max-w-4xl flex flex-col gap-6">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Inbound Campaign UTM Builder</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Generate tracked URLs for LinkedIn ads, partner newsletters, webinars, and search campaigns to feed directly into Focal attribution.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Configuration Controls --}}
            <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col gap-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Destination & Campaign</h3>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Select Hosted Landing Page (Optional)</label>
                    <select wire:model.live="selectedLandingPageId" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        <option value="">-- Custom Destination URL --</option>
                        @foreach ($this->landingPages as $lp)
                            <option value="{{ $lp->id }}">{{ $lp->title }} ({{ $lp->slug }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Destination Base URL *</label>
                    <input type="url" wire:model.live.debounce.300ms="baseUrl" placeholder="https://focal.test/demo" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white" required />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Attach to Existing Campaign (Optional)</label>
                    <select wire:model.live="selectedCampaignId" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        <option value="">-- Choose Campaign --</option>
                        @foreach ($this->campaigns as $camp)
                            <option value="{{ $camp->id }}">{{ $camp->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Campaign Name (utm_campaign) *</label>
                    <input type="text" wire:model.live.debounce.250ms="customCampaign" placeholder="e.g. q3-enterprise-announcement" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                </div>
            </div>

            {{-- Channel & Attribution Parameters --}}
            <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col gap-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Tracking Parameters</h3>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Campaign Source (utm_source) *</label>
                    <div class="flex gap-2">
                        <input type="text" wire:model.live.debounce.250ms="utmSource" placeholder="e.g. linkedin, google, newsletter" class="flex-grow text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                        <select wire:change="$set('utmSource', $event.target.value)" class="text-xs rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="">Presets</option>
                            <option value="linkedin">LinkedIn</option>
                            <option value="google">Google</option>
                            <option value="twitter">Twitter / X</option>
                            <option value="newsletter">Newsletter</option>
                            <option value="partner">Partner</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Campaign Medium (utm_medium) *</label>
                    <div class="flex gap-2">
                        <input type="text" wire:model.live.debounce.250ms="utmMedium" placeholder="e.g. social, cpc, email, referral" class="flex-grow text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                        <select wire:change="$set('utmMedium', $event.target.value)" class="text-xs rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="">Presets</option>
                            <option value="cpc">CPC (Paid Ad)</option>
                            <option value="social">Social</option>
                            <option value="email">Email</option>
                            <option value="referral">Referral</option>
                            <option value="event">Event / Webinar</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Content / Ad Variant (utm_content)</label>
                    <input type="text" wire:model.live.debounce.250ms="utmContent" placeholder="e.g. hero-banner, sidebar-cta, variant-b" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Search Keywords (utm_term)</label>
                    <input type="text" wire:model.live.debounce.250ms="utmTerm" placeholder="e.g. revops-crm, sales-automation" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                </div>
            </div>
        </div>

        {{-- Output Tracking URL Box --}}
        <div class="bg-sky-50 dark:bg-sky-950/40 p-6 rounded-xl border border-sky-200 dark:border-sky-800 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-sky-700 dark:text-sky-400">Generated Inbound Tracking URL</span>
                <span class="text-xs text-sky-600 dark:text-sky-300">Ready for distribution</span>
            </div>

            <div class="flex items-center gap-2">
                <input
                    id="generated-utm-url"
                    type="text"
                    readonly
                    value="{{ $this->generatedUrl }}"
                    class="flex-grow font-mono text-xs rounded-lg border-sky-300 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2.5 selection:bg-sky-200"
                />

                <button
                    type="button"
                    x-data="{ copied: false }"
                    @click="
                        navigator.clipboard.writeText($el.previousElementSibling.value);
                        copied = true;
                        setTimeout(() => copied = false, 2000);
                    "
                    class="bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs py-2.5 px-4 rounded-lg transition inline-flex items-center gap-1.5 shadow-sm"
                >
                    <x-filament::icon icon="heroicon-m-clipboard-document" class="w-4 h-4" />
                    <span x-text="copied ? 'Copied!' : 'Copy URL'"></span>
                </button>

                <a
                    href="{{ $this->generatedUrl }}"
                    target="_blank"
                    class="bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 font-semibold text-xs py-2.5 px-4 rounded-lg transition inline-flex items-center gap-1 shadow-sm"
                >
                    <span>Test</span>
                    <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" class="w-3.5 h-3.5" />
                </a>
            </div>
        </div>
    </div>
</x-filament-panels::page>
