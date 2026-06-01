<?php
/**
 * Staff Leave Requests Page
 */
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . route_url('/login'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Requests - Clock-It</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="<?= asset_url('js/utilities.js') ?>"></script>
</head>
<body x-data="{
    requests: [
        { type: 'Annual Leave', dates: 'Jun 18 - Jun 20, 2026', status: 'Approved' },
        { type: 'Sick Leave', dates: 'May 9, 2026', status: 'Pending' }
    ],
    form: { type: 'Annual Leave', start: '', end: '', reason: '' },
    submitRequest() {
        if (!this.form.start || !this.form.end) {
            alert('Please choose a start and end date.');
            return;
        }

        this.requests.unshift({
            type: this.form.type,
            dates: this.form.start + ' to ' + this.form.end,
            status: 'Pending'
        });
        this.form = { type: 'Annual Leave', start: '', end: '', reason: '' };
        alert('Leave request submitted.');
    }
}" @init="window.themeManager.initTheme()">
    <div style="display: flex; min-height: 100vh;">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div style="flex: 1; display: flex; flex-direction: column;">
            <?php include __DIR__ . '/../partials/header.php'; ?>

            <main class="dashboard-section">
                <div class="container-fluid">
                    <h2 class="mb-4">Leave Requests</h2>

                    <div class="row">
                        <div class="col-lg-5 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">New Request</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Leave Type</label>
                                        <select class="form-select" x-model="form.type">
                                            <option>Annual Leave</option>
                                            <option>Sick Leave</option>
                                            <option>Family Responsibility</option>
                                            <option>Unpaid Leave</option>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Start Date</label>
                                            <input class="form-control" type="date" x-model="form.start">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">End Date</label>
                                            <input class="form-control" type="date" x-model="form.end">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Reason</label>
                                        <textarea class="form-control" rows="4" x-model="form.reason"></textarea>
                                    </div>
                                    <button class="btn btn-primary" type="button" @click="submitRequest()">
                                        <i class="bi bi-send me-1" aria-hidden="true"></i>Submit Request
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">My Requests</h5>
                                </div>
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Dates</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="request in requests" :key="request.type + request.dates">
                                                <tr>
                                                    <td x-text="request.type"></td>
                                                    <td x-text="request.dates"></td>
                                                    <td>
                                                        <span class="badge" :class="request.status === 'Approved' ? 'bg-success' : 'bg-warning'" x-text="request.status"></span>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
