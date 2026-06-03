<?php
// Simple staff leave request view (stub)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Leave Request | Clock-It</title>
    <link href="/assets/css/app.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body x-data="leaveRequestApp()">
<div class="container">
    <h1 class="mt-4">Request Leave</h1>
    <form @submit.prevent="submitRequest">
        <div class="mb-3">
            <label class="form-label">Leave Type</label>
            <select x-model="form.type" class="form-select">
                <option>Annual Leave</option>
                <option>Sick Leave</option>
                <option>Study Leave</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Start Date</label>
            <input type="date" x-model="form.start_date" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">End Date</label>
            <input type="date" x-model="form.end_date" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Reason</label>
            <textarea x-model="form.reason" class="form-control"></textarea>
        </div>
        <button class="btn btn-primary">Submit Request</button>
    </form>

    <div class="mt-4">
        <h4>Your Requests</h4>
        <ul>
            <template x-for="r in requests" :key="r.id">
                <li x-text="r.employee_name + ' — ' + r.status"></li>
            </template>
        </ul>
    </div>
</div>

<script>
function leaveRequestApp(){
    return {
        form: { type: 'Annual Leave', start_date: '', end_date: '', reason: '' },
        requests: [],
        submitRequest(){
            this.requests.push({ id: Date.now(), employee_name: 'Demo Staff', status: 'Pending', ...this.form });
            this.form = { type: 'Annual Leave', start_date: '', end_date: '', reason: '' };
        }
    }
}
</script>
</body>
</html>
