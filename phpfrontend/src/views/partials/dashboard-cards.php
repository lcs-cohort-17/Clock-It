<?php

if (isset($_GET['api'])) {

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');

    echo json_encode([
        "currentlyOnsite" => 12,
        "clockedInToday" => 35,
        "pendingSync" => 0,
        "totalEventsToday" => 148
    ]);

    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Cards</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .card-box {
            min-height: 150px;
        }
    </style>
</head>

<body class="bg-light">

<div class="container py-5"
     x-data="dashboard()"
     x-init="init()">

    <!-- ERROR (GLOBAL) -->
    <template x-if="error">
        <div class="alert alert-danger mb-4" x-text="error"></div>
    </template>

    <div class="row g-4">

        <!-- CARD 1 -->
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm card-box p-3">

                <div class="d-flex justify-content-between">
                    <h6>Currently Onsite</h6>
                    <i class="bi bi-people-fill text-primary fs-3"></i>
                </div>

                <template x-if="loading">
                    <div class="spinner-border text-primary mt-3"></div>
                </template>

                <h2 class="mt-3" x-text="stats.currentlyOnsite"></h2>
            </div>
        </div>

        <!-- CARD 2 -->
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm card-box p-3">

                <div class="d-flex justify-content-between">
                    <h6>Clocked In</h6>
                    <i class="bi bi-clock-fill text-success fs-3"></i>
                </div>

                <template x-if="loading">
                    <div class="spinner-border text-success mt-3"></div>
                </template>

                <h2 class="mt-3" x-text="stats.clockedInToday"></h2>
            </div>
        </div>

        <!-- CARD 3 -->
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm card-box p-3">

                <div class="d-flex justify-content-between">
                    <h6>Pending Sync</h6>
                    <i class="bi bi-arrow-repeat text-warning fs-3"></i>
                </div>

                <template x-if="loading">
                    <div class="spinner-border text-warning mt-3"></div>
                </template>

                <h2 class="mt-3" x-text="stats.pendingSync"></h2>
            </div>
        </div>

        <!-- CARD 4 -->
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm card-box p-3">

                <div class="d-flex justify-content-between">
                    <h6>Total Events</h6>
                    <i class="bi bi-bar-chart-fill text-danger fs-3"></i>
                </div>

                <template x-if="loading">
                    <div class="spinner-border text-danger mt-3"></div>
                </template>

                <h2 class="mt-3" x-text="stats.totalEventsToday"></h2>
            </div>
        </div>

    </div>
</div>

<script>
function dashboard() {
    return {

        loading: true,
        error: '',

        stats: {
            currentlyOnsite: 0,
            clockedInToday: 0,
            pendingSync: 0,
            totalEventsToday: 0
        },

        async fetchStats() {
            try {
                const res = await fetch("dashboard-cards.php?api=1");

                const data = await res.json();

                this.stats = data;

            } catch (e) {
                this.error = "Failed to load dashboard data";
            } finally {
                this.loading = false;
            }
        },

        init() {
            this.fetchStats();

            setInterval(() => {
                this.fetchStats();
            }, 30000);
        }
    }
}
</script>

</body>
</html>