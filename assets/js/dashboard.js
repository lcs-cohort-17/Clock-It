function dashboard() {

return {

sidebarOpen: window.innerWidth > 992,

isMobile: window.innerWidth <= 992,

showCalendar: false,

showLeave: false,

currentMonth: new Date().getMonth(),

currentYear: new Date().getFullYear(),

selectedDate: null,

calendarDays: [],

init() {
this.handleResize()
this.generateCalendar()
window.addEventListener('resize', () => this.handleResize())
},

handleResize() {
this.isMobile = window.innerWidth <= 992
this.sidebarOpen = !this.isMobile
},

closeSidebarOnMobile() {
if (this.isMobile) {
this.sidebarOpen = false
}
},

generateCalendar() {
const firstDay = new Date(this.currentYear, this.currentMonth, 1)
const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0)
const daysInMonth = lastDay.getDate()
const startingDayOfWeek = firstDay.getDay()

this.calendarDays = []

// Add empty cells for days before month starts
for (let i = 0; i < startingDayOfWeek; i++) {
this.calendarDays.push(null)
}

// Add days of the month
for (let day = 1; day <= daysInMonth; day++) {
this.calendarDays.push(day)
}
},

previousMonth() {
if (this.currentMonth === 0) {
this.currentMonth = 11
this.currentYear--
} else {
this.currentMonth--
}
this.generateCalendar()
},

nextMonth() {
if (this.currentMonth === 11) {
this.currentMonth = 0
this.currentYear++
} else {
this.currentMonth++
}
this.generateCalendar()
},

selectDate(day) {
if (day) {
this.selectedDate = new Date(this.currentYear, this.currentMonth, day)
}
},

isToday(day) {
const today = new Date()
return day && 
day === today.getDate() && 
this.currentMonth === today.getMonth() && 
this.currentYear === today.getFullYear()
},

isSelected(day) {
return this.selectedDate && 
day === this.selectedDate.getDate() && 
this.currentMonth === this.selectedDate.getMonth() && 
this.currentYear === this.selectedDate.getFullYear()
},

getMonthYear() {
const months = ['January', 'February', 'March', 'April', 'May', 'June', 
'July', 'August', 'September', 'October', 'November', 'December']
return `${months[this.currentMonth]} ${this.currentYear}`
}

}

}
