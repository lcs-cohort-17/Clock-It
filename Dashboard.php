<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Clock It Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<link rel="stylesheet" href="assets/css/dashboard.css">

</head>

<body>

<div
x-data="dashboard()"
class="dashboard-app"
>

<?php include 'Sidebar.php'; ?>

<div class="content-wrapper">

<?php include 'Header.php'; ?>

<?php include 'DashboardGrid.php'; ?>

</div>

<?php include 'modals/CalendarModal.php'; ?>

<?php include 'modals/LeaveRequestModal.php'; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="assets/js/dashboard.js"></script>

</body>
</html>