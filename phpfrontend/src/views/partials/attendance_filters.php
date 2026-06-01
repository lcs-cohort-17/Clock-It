<div
class="bg-white dark:bg-slate-800
border border-slate-200 dark:border-slate-700
rounded-2xl p-4">

    <form class="grid gap-4 md:grid-cols-3">

        <input
            type="text"
            name="search"
            value="<?= htmlspecialchars($search) ?>"
            placeholder="Search staff..."
            class="rounded-xl border px-4 py-3">

        <select
            name="status"
            class="rounded-xl border px-4 py-3">

            <option value="all">All Statuses</option>
            <option value="synced">Synced</option>
            <option value="pending">Pending</option>
            <option value="failed">Failed</option>

        </select>

        <select
            name="view"
            class="rounded-xl border px-4 py-3">

            <option value="list">List View</option>
            <option value="calendar">Calendar View</option>

        </select>

    </form>
</div>