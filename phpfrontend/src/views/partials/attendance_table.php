<div
class="bg-white dark:bg-slate-800
border border-slate-200 dark:border-slate-700
rounded-2xl overflow-hidden">

    <table class="w-full">

        <thead>

        <tr class="bg-slate-50 dark:bg-slate-900">

            <th class="p-4 text-left">Staff</th>
            <th class="p-4 text-left">Event</th>
            <th class="p-4 text-left">Time</th>
            <th class="p-4 text-left">Location</th>
            <th class="p-4 text-left">Status</th>

        </tr>

        </thead>

        <tbody>

        <?php foreach ($filteredRecords as $row): ?>

            <tr class="border-t">

                <td class="p-4">
                    <?= htmlspecialchars($row['staff']) ?>
                </td>

                <td class="p-4">
                    <?= htmlspecialchars($row['type']) ?>
                </td>

                <td class="p-4">
                    <?= htmlspecialchars($row['timestamp']) ?>
                </td>

                <td class="p-4">
                    <?= htmlspecialchars($row['location']) ?>
                </td>

                <td class="p-4">

                    <?php
                    $color = match(strtolower($row['syncStatus'])) {
                        'synced' => 'bg-green-100 text-green-700',
                        'pending' => 'bg-yellow-100 text-yellow-700',
                        default => 'bg-red-100 text-red-700'
                    };
                    ?>

                    <span class="px-3 py-1 rounded-full text-xs <?= $color ?>">
                        <?= $row['syncStatus'] ?>
                    </span>

                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</div>