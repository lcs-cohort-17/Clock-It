<?php
/**
 * DataRetention Component
 *
 * Renders the data retention settings card for the admin settings page.
 */
function render_data_retention_component(): void {
    echo <<<HTML
<div class="w-full rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
  <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
      <h2 class="text-xl font-semibold text-slate-900">Data Retention</h2>
      <p class="mt-2 text-sm text-slate-600">Manage how long attendance records are kept before they are purged.</p>
    </div>
    <span class="inline-flex items-center rounded-full border border-slate-300 bg-slate-100 px-3 py-1 text-sm text-slate-700">Safe cleanup</span>
  </div>

  <div class="mt-6 grid gap-4 md:grid-cols-[1fr_auto] md:items-end">
    <div>
      <label for="retention-days" class="block text-sm font-medium text-slate-700">Keep records for (days)</label>
      <input id="retention-days" type="number" min="1" step="1" value="30" class="mt-2 w-full max-w-[180px] rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" />
    </div>
    <button id="purge-btn" type="button" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60">Purge Old Records</button>
  </div>

  <div id="data-retention-message" class="mt-4 min-h-[2.5rem]" aria-live="polite"></div>
</div>
<script>
(function(){
  const input = document.getElementById('retention-days');
  const btn = document.getElementById('purge-btn');
  const messageArea = document.getElementById('data-retention-message');

  function setMessage(text, type){
    if (!messageArea) return;
    messageArea.innerHTML = text ? '<div class="rounded-2xl px-4 py-3 ' + (type === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200') + '">' + text + '</div>' : '';
  }

  function isValid(value){
    const num = Number(value);
    return Number.isInteger(num) && num > 0;
  }

  if (!btn) return;
  btn.addEventListener('click', function(){
    setMessage('', '');
    const days = input.value;
    if (!isValid(days)) {
      setMessage('Please enter a positive whole number of days.', 'error');
      return;
    }
    if (!confirm('⚠️ Are you sure you want to permanently delete all attendance records older than ' + days + ' days? This action cannot be undone.')) return;

    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<svg class="mr-2 h-4 w-4 animate-spin" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25" fill="none"></circle><path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round" fill="none"></path></svg>Purge in progress...';

    setTimeout(() => {
      const success = Math.random() > 0.1;
      if (success) {
        setMessage('✅ Successfully purged all attendance records older than ' + days + ' days.', 'success');
      } else {
        setMessage('❌ Error: Simulated server error. Please try again.', 'error');
      }
      btn.disabled = false;
      btn.innerHTML = originalText;
    }, 1400);
  });
})();
</script>
HTML;
}
