function initCalendar(events) {
    return {
        eventsList: events,
        days: [],
        currentMonth: 4,
        currentYear: 2026,
        monthNames: [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December',
        ],

        generateCalendar() {
            this.days = [];
            const firstDayIndex = new Date(this.currentYear, this.currentMonth, 1).getDay();
            const totalDays = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();

            for (let index = 0; index < firstDayIndex; index += 1) {
                this.days.push({
                    id: `blank-${index}`,
                    dateNumber: '',
                    isToday: false,
                    hasEvent: false,
                    eventInfo: '',
                });
            }

            for (let day = 1; day <= totalDays; day += 1) {
                const date = `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

                this.days.push({
                    id: date,
                    dateNumber: day,
                    isToday: date === new Date().toISOString().split('T')[0],
                    hasEvent: Boolean(this.eventsList[date]),
                    eventInfo: this.eventsList[date] || '',
                });
            }

            this.$nextTick(() => this.initPopovers());
        },

        prevMonth() {
            if (this.currentMonth === 0) {
                this.currentMonth = 11;
                this.currentYear -= 1;
            } else {
                this.currentMonth -= 1;
            }

            this.generateCalendar();
        },

        nextMonth() {
            if (this.currentMonth === 11) {
                this.currentMonth = 0;
                this.currentYear += 1;
            } else {
                this.currentMonth += 1;
            }

            this.generateCalendar();
        },

        initPopovers() {
            document.querySelectorAll('[data-bs-toggle="popover"]').forEach((element) => {
                bootstrap.Popover.getOrCreateInstance(element);
            });
        },
    };
}
