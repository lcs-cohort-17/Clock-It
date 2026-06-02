<div x-show="showCalendar" x-cloak class="staff-dashboard-modal" @click.self="showCalendar = false">

<div class="staff-dashboard-modal-card">

<div class="d-flex justify-content-between align-items-center mb-3">

<div>

<h2>Calendar</h2>

<p>Pick a date to view your schedule.</p>

</div>

<button
class="btn-close"
@click="showCalendar=false"
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
'today': isToday(day),
'selected': isSelected(day)
}"
@click="selectDate(day)"
>

<span x-text="day || ''"></span>

</button>

</template>

</div>

<div class="mt-3" x-show="selectedDate">

<p class="text-muted">Selected: <strong x-text="selectedDate ? selectedDate.toDateString() : ''"></strong></p>

<button class="btn btn-primary btn-sm" @click="showCalendar=false">Confirm</button>

</div>

</div>

</div>

</div>
