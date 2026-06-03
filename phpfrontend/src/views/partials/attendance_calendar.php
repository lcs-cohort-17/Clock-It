<div
    class="bg-white dark:bg-slate-800
    border border-slate-200 dark:border-slate-700
    rounded-2xl p-6"
    x-data="initCalendar(<?= htmlspecialchars(json_encode($calendarEvents)) ?>)"
    x-init="generateCalendar()"
>

    <div class="flex items-center justify-between mb-6">

        <h2
            class="text-xl font-bold text-slate-900 dark:text-white"
            x-text="monthNames[currentMonth] + ' ' + currentYear">
        </h2>

        <div class="flex gap-2">

            <button
                @click="prevMonth()"
                class="px-3 py-2 rounded-lg border">
                ←
            </button>

            <button
                @click="goToToday()"
                class="px-4 py-2 rounded-lg border">
                Today
            </button>

            <button
                @click="nextMonth()"
                class="px-3 py-2 rounded-lg border">
                →
            </button>

        </div>

    </div>

    <!-- Days Header -->

    <div class="grid grid-cols-7 gap-2 text-center font-semibold text-sm mb-4">

        <div>Sun</div>
        <div>Mon</div>
        <div>Tue</div>
        <div>Wed</div>
        <div>Thu</div>
        <div>Fri</div>
        <div>Sat</div>

    </div>

    <!-- Calendar Days -->

    <div class="grid grid-cols-7 gap-2">

        <template x-for="day in days" :key="day.id">

            <div
                class="rounded-xl p-3 min-h-[70px] border relative text-center"
                :class="{
                    'opacity-40': !day.isCurrentMonth,
                    'border-sky-600 font-bold': day.isToday
                }"
            >

                <div x-text="day.dateNumber"></div>

                <template x-if="day.hasEvent">

                    <span
                        class="absolute bottom-2 left-1/2 -translate-x-1/2 h-2 w-2 rounded-full bg-green-500">
                    </span>

                </template>

            </div>

        </template>

    </div>

</div>

<script>
function initCalendar(events) {
    return {

        eventsList: events,
        days: [],

        currentMonth: new Date().getMonth(),
        currentYear: new Date().getFullYear(),

        monthNames: [
            "January","February","March",
            "April","May","June",
            "July","August","September",
            "October","November","December"
        ],

        generateCalendar() {

            this.days = [];

            const firstDay =
                new Date(
                    this.currentYear,
                    this.currentMonth,
                    1
                ).getDay();

            const totalDays =
                new Date(
                    this.currentYear,
                    this.currentMonth + 1,
                    0
                ).getDate();

            for (let i = 0; i < firstDay; i++) {

                this.days.push({
                    id: 'blank-' + i,
                    dateNumber: '',
                    isCurrentMonth: false,
                    hasEvent: false
                });

            }

            for (let day = 1; day <= totalDays; day++) {

                const dateString =
                    `${this.currentYear}-${String(this.currentMonth + 1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;

                this.days.push({

                    id: dateString,

                    dateNumber: day,

                    isCurrentMonth: true,

                    isToday:
                        dateString ===
                        new Date().toISOString().split('T')[0],

                    hasEvent:
                        !!this.eventsList[dateString]
                });
            }
        },

        prevMonth() {

            if (this.currentMonth === 0) {

                this.currentMonth = 11;
                this.currentYear--;

            } else {

                this.currentMonth--;

            }

            this.generateCalendar();
        },

        nextMonth() {

            if (this.currentMonth === 11) {

                this.currentMonth = 0;
                this.currentYear++;

            } else {

                this.currentMonth++;

            }

            this.generateCalendar();
        },

        goToToday() {

            const today = new Date();

            this.currentMonth = today.getMonth();
            this.currentYear = today.getFullYear();

            this.generateCalendar();
        }
    }
}
</script>