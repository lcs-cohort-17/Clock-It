<section class="page-stack" x-data="profileView()">
    <div class="split-grid">
        <aside class="surface">
            <div class="d-flex align-items-center gap-3">
                <span class="avatar" style="width:4rem;height:4rem;font-size:1.1rem" x-text="form.avatar"></span>
                <div>
                    <h2 class="section-title" x-text="form.name"></h2>
                    <p class="surface-subtitle" x-text="`${form.employeeId} / ${form.jobTitle}`"></p>
                </div>
            </div>

            <div class="metadata-grid mt-4">
                <div class="metadata-item"><span>Department</span><strong x-text="form.department"></strong></div>
                <div class="metadata-item"><span>Manager</span><strong x-text="form.manager"></strong></div>
                <div class="metadata-item"><span>Location</span><strong x-text="form.location"></strong></div>
                <div class="metadata-item"><span>Start date</span><strong x-text="formatDate(form.startDate)"></strong></div>
            </div>
        </aside>

        <section class="surface">
            <div class="surface-header">
                <div>
                    <h2>Contact Details</h2>
                    <p class="surface-subtitle">Personal contact information for future backend sync.</p>
                </div>
                <button class="btn btn-primary" type="button" x-on:click="save()"><?= ui_icon('check') ?> Save profile</button>
            </div>

            <template x-if="saved">
                <div class="feedback-panel success mb-3">Profile saved in local state.</div>
            </template>
            <template x-if="error">
                <div class="feedback-panel danger mb-3" x-text="error"></div>
            </template>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label">Name</label>
                    <input class="form-control" x-model="form.name">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Email</label>
                    <input class="form-control" type="email" x-model="form.email">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Phone</label>
                    <input class="form-control" x-model="form.phone">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Location</label>
                    <input class="form-control" x-model="form.location">
                </div>
            </div>
        </section>
    </div>

    <section class="surface">
        <div class="surface-header">
            <div>
                <h2>Account Settings</h2>
                <p class="surface-subtitle">Static controls ready for authentication integration.</p>
            </div>
        </div>
        <div class="three-grid">
            <label class="mini-row">
                <span>
                    <strong>Email attendance summaries</strong>
                    <span>Weekly digest preference.</span>
                </span>
                <input class="form-check-input" type="checkbox" checked>
            </label>
            <label class="mini-row">
                <span>
                    <strong>Mobile scan alerts</strong>
                    <span>Notify on successful scans.</span>
                </span>
                <input class="form-check-input" type="checkbox" checked>
            </label>
            <label class="mini-row">
                <span>
                    <strong>Remember this device</strong>
                    <span>Session placeholder.</span>
                </span>
                <input class="form-check-input" type="checkbox">
            </label>
        </div>
    </section>
</section>
