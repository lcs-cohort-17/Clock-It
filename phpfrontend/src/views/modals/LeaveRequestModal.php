<div x-show="showLeave" x-cloak class="staff-dashboard-modal" @click.self="showLeave = false">

<div class="staff-dashboard-modal-card staff-leave-modal">

<div class="d-flex justify-content-between">

<div>

<h2>New Leave Request</h2>

<p>Submit a request for time off.</p>

</div>

<button
class="btn-close"
@click="showLeave=false"
></button>

</div>

<form @submit.prevent="showLeave = false">

<div class="mb-3">

<label>Type</label>

<select class="form-select">

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
>

</div>

<div class="col-12 col-sm-6">

<label>To</label>

<input
type="date"
class="form-control"
>

</div>

</div>

<div class="mt-3">

<label>Reason</label>

<textarea
class="form-control"
rows="4"
></textarea>

</div>

<div class="text-end mt-4">

<button
type="button"
class="btn btn-outline-secondary"
@click="showLeave=false"
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
