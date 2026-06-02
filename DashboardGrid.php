<section class="dashboard-content" id="dashboard">

<h1 class="dashboard-title">
Hi, Sarah
</h1>

<p class="dashboard-date">
Monday, 1 June 2026
</p>

<div class="row g-4">

<div class="col-lg-8">

<div class="dashboard-card" id="scan">

<span class="badge bg-secondary-subtle text-dark mb-4">
OFFSITE
</span>

<p class="text-muted">
You are currently
</p>

<h2 class="status-title">
Clocked Out
</h2>

<p class="text-muted">
Last action: 08:46 - 14 days ago
</p>

<button class="scan-button" type="button">
<i class="fas fa-qrcode"></i>
Scan QR
</button>

</div>

</div>

<div class="col-lg-4">

<div class="dashboard-card" id="activity">

<h4>
<i class="fas fa-location-dot"></i>
Today's activity
</h4>

<div class="activity-box">
No clock events today yet.
</div>

</div>

</div>

</div>

<div class="row mt-4 g-4">

<div class="col-md-4">

<div
class="quick-card"
@click="showCalendar=true"
>
<i class="fas fa-calendar-days quick-icon"></i>
<h4>Calendar</h4>
<p>View your schedule</p>
</div>

</div>

<div class="col-md-4">

<div
class="quick-card"
@click="showLeave=true"
>
<i class="fas fa-file-lines quick-icon"></i>
<h4>Leave Requests</h4>
<p>Submit a new request</p>
</div>

</div>

<div class="col-md-4">

<div class="quick-card" id="profile">
<i class="fas fa-user quick-icon"></i>
<h4>Profile</h4>
<p>Manage your account</p>
</div>

</div>

</div>

</section>
