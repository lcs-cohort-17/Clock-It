<?php
/**
 * SecuritySettings Component
 *
 * Renders the security settings card for the admin settings page.
 */
function render_security_settings_component(): void {
    echo <<<HTML
<div class="w-full rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
  <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div class="flex items-start gap-4">
      <div class="flex h-12 w-12 items-center justify-center rounded-3xl bg-slate-100 text-slate-700">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <div>
        <h2 class="text-xl font-semibold text-slate-900">Security</h2>
        <p class="mt-2 text-sm text-slate-600">Session management, encryption, and access controls for your admin portal.</p>
      </div>
    </div>
    <span class="inline-flex items-center rounded-full border border-slate-300 bg-slate-100 px-3 py-1 text-sm text-slate-700">Compliance ready</span>
  </div>

  <div class="mt-6 space-y-6">
    <div class="grid gap-4 sm:grid-cols-[1fr_auto] sm:items-end">
      <div>
        <label for="timeout-input" class="block text-sm font-medium text-slate-700">Session timeout (minutes)</label>
        <input id="timeout-input" type="number" min="1" max="1440" value="30" class="mt-2 w-full max-w-[160px] rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" />
        <p id="msg-hint" class="mt-3 text-sm text-slate-500">Users will be automatically logged out after <span id="timeout-val">30</span> minutes of inactivity.</p>
        <p id="msg-error" class="mt-2 text-sm text-rose-600 hidden"></p>
      </div>
      <button id="save-btn" type="button" class="inline-flex h-12 items-center justify-center rounded-2xl bg-slate-900 px-6 text-sm font-semibold text-white transition hover:bg-slate-800">Save</button>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 sm:p-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center">
        <div class="flex items-start gap-3">
          <div class="flex h-11 w-11 items-center justify-center rounded-3xl bg-white text-slate-900 shadow-sm">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-slate-900">Data encryption (in transit &amp; at rest)</p>
            <p class="mt-1 text-sm text-slate-500">Recommended for compliance.</p>
          </div>
        </div>
        <button id="enc-toggle" aria-pressed="true" class="relative inline-flex h-11 w-20 items-center rounded-full bg-slate-900 p-1 transition" title="Toggle encryption">
          <span class="knob absolute left-1 top-1 h-9 w-9 rounded-full bg-white shadow-sm transition-all"></span>
        </button>
      </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 text-sm text-slate-600">
      <p class="font-semibold text-slate-900">Role-based access (RBAC)</p>
      <p class="mt-2 leading-7">Always enforced. Staff cannot access admin areas. All role checks are server-side.</p>
    </div>
  </div>
</div>
<script>
(function(){
  const input = document.getElementById('timeout-input');
  const saveBtn = document.getElementById('save-btn');
  const err = document.getElementById('msg-error');
  const hintVal = document.getElementById('timeout-val');
  const encToggle = document.getElementById('enc-toggle');
  const knob = encToggle.querySelector('.knob');

  let timeout = 30;
  let encryptionEnabled = true;

  function setError(text){
    if (!err) return;
    if (!text) {
      err.classList.add('hidden');
      err.textContent = '';
    } else {
      err.classList.remove('hidden');
      err.textContent = text;
    }
  }

  function setSavedState(){
    saveBtn.classList.add('bg-emerald-600');
    saveBtn.textContent = 'Saved';
    setTimeout(() => {
      saveBtn.classList.remove('bg-emerald-600');
      saveBtn.textContent = 'Save';
    }, 2200);
  }

  if (saveBtn) {
    saveBtn.addEventListener('click', function(){
      const value = parseInt(input.value, 10);
      if (Number.isNaN(value) || value < 1 || value > 1440) {
        setError('Please enter a value between 1 and 1440 minutes.');
        return;
      }
      setError('');
      timeout = value;
      hintVal.textContent = timeout + (timeout !== 1 ? 's' : '');
      setSavedState();
    });
  }

  if (input) {
    input.addEventListener('input', function(){ setError(''); });
    input.addEventListener('keydown', function(e){ if (e.key === 'Enter') saveBtn.click(); });
  }

  if (encToggle) {
    encToggle.addEventListener('click', function(){
      encryptionEnabled = !encryptionEnabled;
      encToggle.setAttribute('aria-pressed', encryptionEnabled);
      if (encryptionEnabled) {
        encToggle.classList.remove('bg-slate-300');
        encToggle.classList.add('bg-slate-900');
        knob.style.transform = 'translateX(32px)';
      } else {
        encToggle.classList.remove('bg-slate-900');
        encToggle.classList.add('bg-slate-300');
        knob.style.transform = 'translateX(0)';
      }
    });
    encToggle.classList.toggle('bg-slate-900', encryptionEnabled);
    knob.style.transform = encryptionEnabled ? 'translateX(32px)' : 'translateX(0)';
  }
})();
</script>
HTML;
}
