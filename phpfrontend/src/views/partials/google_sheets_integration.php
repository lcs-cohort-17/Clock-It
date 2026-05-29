<?php
if (!function_exists('google_sheets_icon')) {
    function google_sheets_icon(string $class = ''): string {
        return <<<SVG
        <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" class="{$class}">
            <path d="M29 0H10C8.3 0 7 1.3 7 3v42c0 1.7 1.3 3 3 3h28c1.7 0 3-1.3 3-3V12L29 0z" fill="#9ACA3C"/>
            <path d="M29 0v12h12L29 0z" fill="#78A929"/>
            <rect x="14" y="22" width="20" height="2" rx="1" fill="#fff"/>
            <rect x="14" y="28" width="20" height="2" rx="1" fill="#fff"/>
            <rect x="14" y="34" width="20" height="2" rx="1" fill="#fff"/>
            <rect x="14" y="22" width="2" height="14" rx="1" fill="#fff"/>
            <rect x="22" y="22" width="2" height="14" rx="1" fill="#fff"/>
            <rect x="30" y="22" width="2" height="14" rx="1" fill="#fff"/>
        </svg>
        SVG;
    }
}

if (!function_exists('link2_icon')) {
    function link2_icon(): string {
        return '<svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 17H7a5 5 0 0 1 0-10h2"/><path d="M15 7h2a5 5 0 0 1 0 10h-2"/><line x1="8" y1="12" x2="16" y2="12"/></svg>';
    }
}

if (!function_exists('x_icon')) {
    function x_icon(): string {
        return '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
    }
}

if (!function_exists('render_modal_backdrop')) {
    function render_modal_backdrop(string $href): string {
        return '<a href="' . htmlspecialchars($href) . '" class="fixed inset-0 z-40 bg-black/20 backdrop-blur-sm no-underline" aria-label="Close modal"></a>';
    }
}

if (!function_exists('render_google_sheets_integration')) {
    function render_google_sheets_integration(
        ?bool $isConnected = null,
        ?string $connectedSheet = null,
        ?string $connectedSince = null,
        string $modal = '',
        string $selfUrl = ''
    ): void {
        $isConnected = $isConnected ?? ($_SESSION['gs_connected'] ?? false);
        $connectedSheet = $connectedSheet ?? ($_SESSION['gs_connected_sheet'] ?? '');
        $connectedSince = $connectedSince ?? ($_SESSION['gs_connected_since'] ?? '');
        $modal = $modal !== '' ? $modal : ($_GET['modal'] ?? '');
        $self = $selfUrl ?: htmlspecialchars(parse_url($_SERVER['REQUEST_URI'] ?? '/admin/google-sheets', PHP_URL_PATH) ?: '/admin/google-sheets');

        $badgeClass = $isConnected
            ? 'border-green-200 bg-green-50 text-green-700'
            : 'border-slate-200 bg-slate-100 text-slate-500';
        $badgeLabel = $isConnected ? 'Connected' : 'Not connected';
        ?>
        {{-- White card with shadow to pop off the light gray page background --}}
        <div class="w-full rounded-2xl border-0 bg-white px-6 py-6 text-[#0f2b40] shadow-[0_2px_12px_0_rgba(0,0,0,0.08)]">

            {{-- Header row: icon + title + badge --}}
            <div class="flex items-start justify-between gap-4 mb-1">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center">
                        <?= google_sheets_icon('h-7 w-7') ?>
                    </div>
                    <h2 class="text-2xl font-bold leading-tight text-[#0f2b40]">Google Sheets Integration</h2>
                </div>
                <span class="mt-1 w-fit shrink-0 select-none rounded-full border px-3 py-0.5 text-xs font-semibold <?= $badgeClass ?>">
                    <?= $badgeLabel ?>
                </span>
            </div>

            {{-- Subtitle --}}
            <p class="mb-5 text-sm text-slate-400 pl-12">Connect attendance export to Google Sheets.</p>

            {{-- =====================================================
                 REPLACE THIS BUTTON WITH YOUR OWN PHP/HTML BELOW
                 ===================================================== --}}
            <?php if (!$isConnected): ?>
                <a href="<?= htmlspecialchars($self) ?>?modal=connect"
                   class="flex w-full items-center justify-center h-12 rounded-xl bg-[#0d2d4a] px-5 text-base font-bold text-white no-underline hover:bg-[#0a2236] active:bg-[#071829] transition-colors">
                    <?= link2_icon() ?>
                    Connect
                </a>
            <?php else: ?>
                <a href="<?= htmlspecialchars($self) ?>?modal=disconnect"
                   class="flex w-full items-center justify-center h-12 rounded-xl bg-red-700 px-5 text-base font-bold text-white no-underline hover:bg-red-800 active:bg-red-900 transition-colors">
                    Disconnect
                </a>
            <?php endif; ?>
            {{-- =====================================================
                 END REPLACEABLE BUTTON
                 ===================================================== --}}

            <?php if ($isConnected): ?>
                <div class="mt-5 border-t border-slate-100 pt-4">
                    <div class="grid gap-3 text-sm sm:grid-cols-2 sm:gap-x-6">
                        <div>
                            <p class="mb-0.5 text-xs text-slate-400">Connected sheet</p>
                            <p class="font-mono text-green-600"><?= htmlspecialchars($connectedSheet) ?></p>
                        </div>
                        <div>
                            <p class="mb-0.5 text-xs text-slate-400">Connected since</p>
                            <p class="text-[#0f2b40]"><?= htmlspecialchars($connectedSince) ?></p>
                        </div>
                        <div>
                            <p class="mb-0.5 text-xs text-slate-400">Syncing</p>
                            <p class="text-[#0f2b40]">Clock-in &amp; clock-out events</p>
                        </div>
                        <div>
                            <p class="mb-0.5 text-xs text-slate-400">Last sync</p>
                            <p class="text-[#0f2b40]">Just now</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($modal === 'connect'): ?>
            <?= render_modal_backdrop($self) ?>
            <div class="fixed top-1/2 left-1/2 z-50 w-full max-w-[calc(100%-2rem)] sm:max-w-md -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white border-0 shadow-2xl p-5 text-[#0f2b40]">
                <a href="<?= htmlspecialchars($self) ?>" class="absolute top-3 right-3 flex items-center justify-center h-7 w-7 rounded-md hover:bg-slate-100 text-slate-400 no-underline">
                    <?= x_icon() ?><span class="sr-only">Close</span>
                </a>
                <div class="flex flex-col gap-1.5 mb-4">
                    <div class="flex items-center gap-2 text-base font-semibold text-[#0f2b40]">
                        <?= google_sheets_icon('h-5 w-5 flex-shrink-0') ?><span>Connect to Google Sheets</span>
                    </div>
                    <p class="text-sm text-slate-400">Please review the following before connecting.</p>
                </div>
                <div class="space-y-3 mb-4">
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <h4 class="mb-2 text-sm font-semibold text-[#0f2b40]">What will happen:</h4>
                        <ul class="list-disc space-y-1.5 pl-5 text-sm text-slate-500">
                            <li>You'll be redirected to Google to authorise access</li>
                            <li>A new Google Sheet will be created for attendance data</li>
                            <li>All future clock-ins will sync automatically</li>
                            <li>You can disconnect at any time from Settings</li>
                        </ul>
                    </div>
                    <div class="rounded-xl border border-amber-100 bg-amber-50 p-4">
                        <h4 class="mb-2 text-sm font-semibold text-amber-800">Permissions requested:</h4>
                        <ul class="list-disc space-y-1 pl-5 text-sm text-amber-700">
                            <li>View and manage your Google Sheets</li>
                            <li>Create and edit new spreadsheets</li>
                            <li>Read attendance data from your sheets</li>
                        </ul>
                    </div>
                    <div class="text-sm text-slate-500">
                        <p class="mb-1 font-medium text-[#0f2b40]">Data privacy</p>
                        <p>We only access sheets created by this integration. Your data is encrypted and never shared with third parties.</p>
                    </div>
                </div>
                <div class="-mx-5 -mb-5 flex flex-col-reverse gap-2 rounded-b-2xl border-t border-slate-100 bg-slate-50 p-4 sm:flex-row sm:justify-end">
                    <a href="<?= htmlspecialchars($self) ?>" class="inline-flex items-center justify-center h-9 px-4 rounded-lg border border-slate-200 bg-white text-sm font-medium text-[#0f2b40] hover:bg-slate-50 no-underline">Cancel</a>
                    <form method="POST" action="<?= htmlspecialchars($self) ?>" class="inline">
                        <input type="hidden" name="action" value="connect">
                        <button type="submit" class="inline-flex items-center justify-center h-9 px-4 rounded-lg bg-[#0d2d4a] text-sm font-medium text-white hover:bg-[#0a2236] border-0 cursor-pointer">Confirm &amp; Connect</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($modal === 'disconnect'): ?>
            <?= render_modal_backdrop($self) ?>
            <div class="fixed top-1/2 left-1/2 z-50 w-full max-w-[calc(100%-2rem)] sm:max-w-md -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white border-0 shadow-2xl p-5 text-[#0f2b40]">
                <a href="<?= htmlspecialchars($self) ?>" class="absolute top-3 right-3 flex items-center justify-center h-7 w-7 rounded-md hover:bg-slate-100 text-slate-400 no-underline">
                    <?= x_icon() ?><span class="sr-only">Close</span>
                </a>
                <div class="flex flex-col gap-1.5 mb-4">
                    <div class="text-base font-semibold text-[#0f2b40]">Disconnect Google Sheets?</div>
                    <p class="text-sm text-slate-400">Are you sure? This will stop all automatic data syncing.</p>
                </div>
                <div class="rounded-xl border border-red-100 bg-red-50 p-4 mb-4">
                    <p class="text-sm text-red-600"><span class="font-semibold">Warning:</span> Your existing sheets will not be deleted, but new attendance data will no longer sync automatically.</p>
                </div>
                <p class="text-sm text-slate-500 mb-4">You can reconnect at any time by clicking "Connect" again.</p>
                <div class="-mx-5 -mb-5 flex flex-col-reverse gap-2 rounded-b-2xl border-t border-slate-100 bg-slate-50 p-4 sm:flex-row sm:justify-end">
                    <a href="<?= htmlspecialchars($self) ?>" class="inline-flex items-center justify-center h-9 px-4 rounded-lg border border-slate-200 bg-white text-sm font-medium text-[#0f2b40] hover:bg-slate-50 no-underline">Keep connected</a>
                    <form method="POST" action="<?= htmlspecialchars($self) ?>" class="inline">
                        <input type="hidden" name="action" value="disconnect">
                        <button type="submit" class="inline-flex items-center justify-center h-9 px-4 rounded-lg bg-red-700 text-sm font-medium text-white hover:bg-red-800 border-0 cursor-pointer">Yes, disconnect</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
        <?php
    }
}