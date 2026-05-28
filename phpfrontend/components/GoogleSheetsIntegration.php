<?php
/**
 * GoogleSheetsIntegration Component
 *
 * Renders the Google Sheets integration card with connect/disconnect modals.
 * State (isConnected, connectedSheet, connectedSince) is managed via PHP session.
 *
 * Usage: include this file and call render_google_sheets_integration();
 */

session_start();

// ── Defaults ──────────────────────────────────────────────────────────────────
if (!isset($_SESSION['gs_connected']))       $_SESSION['gs_connected']       = false;
if (!isset($_SESSION['gs_connected_sheet'])) $_SESSION['gs_connected_sheet'] = '';
if (!isset($_SESSION['gs_connected_since'])) $_SESSION['gs_connected_since'] = '';

// ── Action handlers ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'connect') {
        $_SESSION['gs_connected']       = true;
        $_SESSION['gs_connected_sheet'] = 'attendance_export_2026';
        $_SESSION['gs_connected_since'] = (new DateTime())->format('j M Y');
    }

    if ($action === 'disconnect') {
        $_SESSION['gs_connected']       = false;
        $_SESSION['gs_connected_sheet'] = '';
        $_SESSION['gs_connected_since'] = '';
    }

    // Redirect to avoid form re-submission on refresh
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// ── State ─────────────────────────────────────────────────────────────────────
$isConnected    = $_SESSION['gs_connected'];
$connectedSheet = $_SESSION['gs_connected_sheet'];
$connectedSince = $_SESSION['gs_connected_since'];

// ── Modal open state via GET param ────────────────────────────────────────────
$showConnectModal    = isset($_GET['modal']) && $_GET['modal'] === 'connect';
$showDisconnectModal = isset($_GET['modal']) && $_GET['modal'] === 'disconnect';

// ── Helper: SVG icon ──────────────────────────────────────────────────────────
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

function link2_icon(): string {
    return '<svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 17H7a5 5 0 0 1 0-10h2"/><path d="M15 7h2a5 5 0 0 1 0 10h-2"/><line x1="8" y1="12" x2="16" y2="12"/></svg>';
}

function x_icon(): string {
    return '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
}

/**
 * Render the full component (card + modals).
 */
function render_google_sheets_integration(): void {
    global $isConnected, $connectedSheet, $connectedSince, $showConnectModal, $showDisconnectModal;

    $badgeClass = $isConnected
        ? 'border-[#9ccf57] bg-[#f1f8e8] text-[#5d9a1b]'
        : 'border-slate-300 bg-slate-100 text-slate-600';
    $badgeLabel = $isConnected ? 'Connected' : 'Not connected';
    $self       = htmlspecialchars($_SERVER['PHP_SELF']);
    ?>

    <!-- ── Card ──────────────────────────────────────────────────────────────── -->
    <div class="w-full rounded-xl border border-[#cbd7df] bg-white px-7 py-7 text-[#002f4f] shadow-sm">

        <!-- Header -->
        <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between p-0">
            <div class="flex items-start gap-5">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-[#eef6df] p-3">
                    <?= google_sheets_icon('h-6 w-6') ?>
                </div>
                <div>
                    <div class="text-xl font-bold text-[#002f4f]">Google Sheets Integration</div>
                    <div class="mt-2 text-base text-[#245575]">Two-way sync of attendance data with auto field mapping.</div>
                </div>
            </div>
            <span class="w-fit shrink-0 select-none rounded-full border px-3 py-1 text-sm font-semibold <?= $badgeClass ?>">
                <?= $badgeLabel ?>
            </span>
        </div>

        <!-- Content -->
        <div class="p-0 pt-5 sm:pl-[68px]">
            <?php if (!$isConnected): ?>
                <a href="<?= $self ?>?modal=connect"
                   class="inline-flex items-center h-12 rounded-lg border-0 bg-[#06466b] px-5 text-base font-bold text-white shadow-none hover:bg-[#003957] active:bg-[#002f4f] no-underline">
                    <?= link2_icon() ?>
                    Connect Google Sheet
                </a>
            <?php else: ?>
                <a href="<?= $self ?>?modal=disconnect"
                   class="inline-flex items-center h-12 rounded-lg border-0 bg-red-700 px-5 text-base font-bold text-white hover:bg-red-800 no-underline">
                    Disconnect
                </a>
            <?php endif; ?>

            <?php if ($isConnected): ?>
                <div class="mt-5 border-t border-[#dce5eb] pt-4">
                    <div class="grid gap-3 text-sm sm:grid-cols-2 sm:gap-x-6">
                        <div>
                            <p class="mb-0.5 text-xs text-[#5b7890]">Connected sheet</p>
                            <p class="font-mono text-[#5d9a1b]"><?= htmlspecialchars($connectedSheet) ?></p>
                        </div>
                        <div>
                            <p class="mb-0.5 text-xs text-[#5b7890]">Connected since</p>
                            <p class="text-[#245575]"><?= htmlspecialchars($connectedSince) ?></p>
                        </div>
                        <div>
                            <p class="mb-0.5 text-xs text-[#5b7890]">Syncing</p>
                            <p class="text-[#245575]">Clock-in &amp; clock-out events</p>
                        </div>
                        <div>
                            <p class="mb-0.5 text-xs text-[#5b7890]">Last sync</p>
                            <p class="text-[#245575]">Just now</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Connect Modal ─────────────────────────────────────────────────────── -->
    <?php if ($showConnectModal): ?>
        <?= render_modal_backdrop($self) ?>
        <div class="fixed top-1/2 left-1/2 z-50 w-full max-w-[calc(100%-2rem)] sm:max-w-md -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white border border-[#cbd7df] p-4 text-[#002f4f] shadow-xl">

            <!-- Close button -->
            <a href="<?= $self ?>" class="absolute top-2 right-2 flex items-center justify-center h-7 w-7 rounded-md hover:bg-gray-100 text-gray-600 no-underline">
                <?= x_icon() ?>
                <span class="sr-only">Close</span>
            </a>

            <!-- Header -->
            <div class="flex flex-col gap-2 mb-4">
                <div class="flex items-center gap-2 text-base font-medium text-[#002f4f]">
                    <?= google_sheets_icon('h-5 w-5 flex-shrink-0') ?>
                    <span>Connect to Google Sheets</span>
                </div>
                <p class="text-sm text-[#245575]">Please review the following before connecting.</p>
            </div>

            <!-- Body -->
            <div class="space-y-4 mb-4">
                <div class="rounded-lg border border-[#dce5eb] bg-[#f8fafb] p-4">
                    <h4 class="mb-2 text-sm font-semibold text-[#002f4f]">What will happen:</h4>
                    <ul class="list-disc space-y-1.5 pl-5 text-sm text-[#245575]">
                        <li>You'll be redirected to Google to authorise access</li>
                        <li>A new Google Sheet will be created for attendance data</li>
                        <li>All future clock-ins will sync automatically</li>
                        <li>You can disconnect at any time from Settings</li>
                    </ul>
                </div>
                <div class="rounded-lg border border-[#f1d28b] bg-[#fff8e8] p-4">
                    <h4 class="mb-2 text-sm font-semibold text-[#8a5a00]">Permissions requested:</h4>
                    <ul class="list-disc space-y-1 pl-5 text-sm text-[#6f4b08]">
                        <li>View and manage your Google Sheets</li>
                        <li>Create and edit new spreadsheets</li>
                        <li>Read attendance data from your sheets</li>
                    </ul>
                </div>
                <div class="text-sm text-[#245575]">
                    <p class="mb-1 font-medium text-[#002f4f]">Data privacy</p>
                    <p>We only access sheets created by this integration. Your data is encrypted and never shared with third parties.</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="-mx-4 -mb-4 flex flex-col-reverse gap-2 rounded-b-xl border-t bg-gray-50 p-4 sm:flex-row sm:justify-end">
                <a href="<?= $self ?>"
                   class="inline-flex items-center justify-center h-9 px-4 rounded-lg border border-[#cbd7df] bg-white text-sm font-medium text-[#002f4f] hover:bg-[#f4f4f4] no-underline">
                    Cancel
                </a>
                <form method="POST" action="<?= $self ?>" class="inline">
                    <input type="hidden" name="action" value="connect">
                    <button type="submit"
                            class="inline-flex items-center justify-center h-9 px-4 rounded-lg bg-[#06466b] text-sm font-medium text-white hover:bg-[#003957] border-0 cursor-pointer">
                        Confirm &amp; Connect
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- ── Disconnect Modal ───────────────────────────────────────────────────── -->
    <?php if ($showDisconnectModal): ?>
        <?= render_modal_backdrop($self) ?>
        <div class="fixed top-1/2 left-1/2 z-50 w-full max-w-[calc(100%-2rem)] sm:max-w-md -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white border border-[#cbd7df] p-4 text-[#002f4f] shadow-xl">

            <!-- Close button -->
            <a href="<?= $self ?>" class="absolute top-2 right-2 flex items-center justify-center h-7 w-7 rounded-md hover:bg-gray-100 text-gray-600 no-underline">
                <?= x_icon() ?>
                <span class="sr-only">Close</span>
            </a>

            <!-- Header -->
            <div class="flex flex-col gap-2 mb-4">
                <div class="text-base font-medium text-[#002f4f]">Disconnect Google Sheets?</div>
                <p class="text-sm text-[#245575]">Are you sure? This will stop all automatic data syncing.</p>
            </div>

            <!-- Body -->
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 mb-4">
                <p class="text-sm text-red-700">
                    <span class="font-semibold">Warning:</span> Your existing sheets will not
                    be deleted, but new attendance data will no longer sync automatically.
                </p>
            </div>
            <p class="text-sm text-[#245575] mb-4">
                You can reconnect at any time by clicking "Connect Google Sheet" again.
            </p>

            <!-- Footer -->
            <div class="-mx-4 -mb-4 flex flex-col-reverse gap-2 rounded-b-xl border-t bg-gray-50 p-4 sm:flex-row sm:justify-end">
                <a href="<?= $self ?>"
                   class="inline-flex items-center justify-center h-9 px-4 rounded-lg border border-[#cbd7df] bg-white text-sm font-medium text-[#002f4f] hover:bg-[#f4f4f4] no-underline">
                    Keep connected
                </a>
                <form method="POST" action="<?= $self ?>" class="inline">
                    <input type="hidden" name="action" value="disconnect">
                    <button type="submit"
                            class="inline-flex items-center justify-center h-9 px-4 rounded-lg bg-red-700 text-sm font-medium text-white hover:bg-red-800 border-0 cursor-pointer">
                        Yes, disconnect
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php
}

/**
 * Renders a semi-transparent backdrop that links back to close the modal.
 */
function render_modal_backdrop(string $href): string {
    return '<a href="' . htmlspecialchars($href) . '" class="fixed inset-0 z-40 bg-black/10 backdrop-blur-sm no-underline" aria-label="Close modal"></a>';
}
