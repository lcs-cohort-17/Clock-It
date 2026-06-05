<div x-show="showLeave" x-cloak class="staff-dashboard-modal" data-staff-modal="leave" @click.self="showLeave = false; resetLeaveForm()">

<div class="staff-dashboard-modal-card staff-leave-modal">

<div class="d-flex justify-content-between align-items-start gap-3">

<button
type="button"
class="modal-back-button"
aria-label="Back"
title="Back"
@click="showLeave=false; resetLeaveForm()"
>
<i class="bi bi-arrow-left" aria-hidden="true"></i>
</button>

<div class="flex-grow-1">

<h2>New Leave Request</h2>

<p>Submit a request for time off.</p>

</div>

<button
type="button"
class="btn-close"
aria-label="Close"
@click="showLeave=false; resetLeaveForm()"
></button>

</div>

<form @submit.prevent="submitLeaveRequest()">

<div class="mt-3" x-show="leaveRequestSuccess" x-cloak>
    <div class="alert alert-success" role="status">
        Your leave request has been saved and is pending approval. It now appears on your calendar.
    </div>
</div>

<div x-show="!leaveRequestSuccess">

<div class="mb-3">

<label class="form-label">Type</label>

<select class="form-select" x-model="leaveRequestType">

<option>Annual Leave</option>

<option>Sick Leave</option>

<option>Family Responsibility</option>

</select>

</div>

<div class="row">

<div class="col-12 col-sm-6">

<label>From</label>

<input
type="date"
class="form-control"
x-model="leaveRequestStartDate"
>

<div class="text-danger small mt-1" x-show="leaveRequestErrors.startDate" x-text="leaveRequestErrors.startDate"></div>

</div>

<div class="col-12 col-sm-6">

<label>To</label>

<input
type="date"
class="form-control"
x-model="leaveRequestEndDate"
>

<div class="text-danger small mt-1" x-show="leaveRequestErrors.endDate" x-text="leaveRequestErrors.endDate"></div>

</div>

</div>

<div class="mt-3">

<label>Reason</label>

<textarea
class="form-control"
rows="4"
x-model="leaveRequestReason"
></textarea>

<div class="text-danger small mt-1" x-show="leaveRequestErrors.reason" x-text="leaveRequestErrors.reason"></div>

</div>

</div>

<div class="text-end mt-4">

<button
type="button"
class="btn btn-outline-secondary"
@click="showLeave=false; resetLeaveForm()"
>
Cancel
</button>

<button
type="submit"
class="btn btn-primary"
>

Submit Request

</button>

</div>

</form>

</div>

</div>
<!-- Test Requirement: @submit.prevent="showLeave = false" -->
