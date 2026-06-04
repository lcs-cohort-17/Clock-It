<div x-show="showCalendar" x-cloak class="staff-dashboard-modal" data-staff-modal="calendar" @click.self="showCalendar = false">

<div class="staff-dashboard-modal-card">

<div class="d-flex justify-content-between align-items-center gap-3 mb-3">

<button
type="button"
class="modal-back-button"
aria-label="Back"
title="Back"
@click="showCalendar=false; selectedDate = null"
>
<i class="bi bi-arrow-left" aria-hidden="true"></i>
</button>

<div class="flex-grow-1">

<h2>Calendar</h2>

<p>Approved leave and attendance status are marked on the calendar.</p>

</div>

<button
type="button"
class="btn-close"
aria-label="Close"
@click="showCalendar=false; selectedDate = null"
></button>

</div>

<div class="calendar-container">

<div class="calendar-header">

<button 
class="btn btn-sm btn-outline-secondary"
@click="previousMonth()"
>
<i class="bi bi-chevron-left" aria-hidden="true"></i>
</button>

<h5 x-text="getMonthYear()" class="flex-grow-1 text-center"></h5>

<button 
class="btn btn-sm btn-outline-secondary"
@click="nextMonth()"
>
<i class="bi bi-chevron-right" aria-hidden="true"></i>
</button>

</div>

<div class="d-flex flex-wrap gap-2 mb-3">
  <span class="badge rounded-pill bg-success">Present</span>
  <span class="badge rounded-pill bg-danger">Absent</span>
  <span class="badge rounded-pill bg-primary">Approved Leave</span>
  <span class="badge rounded-pill bg-warning text-dark">Pending Leave</span>
  <span class="badge rounded-pill bg-secondary">Other Leave Status</span>
</div>

<div class="calendar-grid mt-4">

<div class="calendar-header-day">Su</div>
<div class="calendar-header-day">Mo</div>
<div class="calendar-header-day">Tu</div>
<div class="calendar-header-day">We</div>
<div class="calendar-header-day">Th</div>
<div class="calendar-header-day">Fr</div>
<div class="calendar-header-day">Sa</div>

<template x-for="(day, index) in calendarDays" :key="`${currentYear}-${currentMonth}-${index}`">

<button
  type="button"
  class="calendar-day"
  :class="{ 
    'empty': !day,
    'today': day && isToday(day.day),
    'selected': day && isSelected(day.day),
    'status-present': day && day.event && day.event.status === 'Present',
    'status-absent': day && day.event && day.event.status === 'Absent',
    'status-leave-approved': day && day.event && day.event.type === 'leave' && day.event.status === 'Approved',
    'status-leave-pending': day && day.event && day.event.type === 'leave' && day.event.status === 'Pending',
    'status-leave-other': day && day.event && day.event.type === 'leave' && day.event.status !== 'Approved' && day.event.status !== 'Pending'
  }"
  @click="selectDate(day && day.day)"
>

  <span class="calendar-date-number" x-text="day ? day.day : ''"></span>
  <template x-if="day && day.event">
    <div class="calendar-status-badge" x-text="day.event.label"></div>
  </template>

</button>

</template>

</div>

<div class="mt-3" x-show="selectedDate">

<p class="text-muted">Selected: <strong x-text="selectedDate ? selectedDate.toDateString() : ''"></strong></p>
<template x-if="selectedDate && getEventForDay(selectedDate.getDate())">
  <p class="text-muted mb-2">
    Status:
    <strong x-text="getEventForDay(selectedDate.getDate()).label"></strong>
  </p>
</template>

<button class="btn btn-primary btn-sm" @click="showCalendar=false">Confirm</button>

</div>

</div>

</div>

</div>
