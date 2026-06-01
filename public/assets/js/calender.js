function initCalendar(events) {
    return {
        eventsList: events,
        days: [],
        currentMonth: 4, // May (0-indexed)
        currentYear: 2026,
        monthNames: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
        
        generateCalendar() {
            this.days = [];
            
            // Get first day of the selected month configuration
            const firstDayIndex = new Date(this.currentYear, this.currentMonth, 1).getDay();
            // Get total days in the selected month
            const totalDays = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
            // Get total days of previous month for padding
            const prevTotalDays = new Date(this.currentYear, this.currentMonth, 0).getDate();
            
            const today = new Date();
            const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

            // 1. Previous Month Padding Days
            for (let i = firstDayIndex - 1; i >= 0; i--) {
                const dayNum = prevTotalDays - i;
                const mockMonth = this.currentMonth === 0 ? 11 : this.currentMonth - 1;
                const mockYear = this.currentMonth === 0 ? this.currentYear - 1 : this.currentYear;
                const dateString = `${mockYear}-${String(mockMonth + 1).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;
                
                this.days.push({
                    id: 'prev-' + dayNum,
                    dateNumber: dayNum,
                    isCurrentMonth: false,
                    isToday: dateString === todayStr,
                    hasEvent: !!this.eventsList[dateString],
                    eventInfo: this.eventsList[dateString] || ''
                });
            }

            // 2. Current Month Target Days
            for (let i = 1; i <= totalDays; i++) {
                const dateString = `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                
                this.days.push({
                    id: 'curr-' + i,
                    dateNumber: i,
                    isCurrentMonth: true,
                    isToday: dateString === todayStr,
                    hasEvent: !!this.eventsList[dateString],
                    eventInfo: this.eventsList[dateString] || ''
                });
            }

            // 3. Next Month Padding Days to complete standard grid layout
            const remainingGridCells = 42 - this.days.length; // 6 rows * 7 days
            for (let i = 1; i <= remainingGridCells; i++) {
                const mockMonth = this.currentMonth === 11 ? 0 : this.currentMonth + 1;
                const mockYear = this.currentMonth === 11 ? this.currentYear + 1 : this.currentYear;
                const dateString = `${mockYear}-${String(mockMonth + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;

                this.days.push({
                    id: 'next-' + i,
                    dateNumber: i,
                    isCurrentMonth: false,
                    isToday: dateString === todayStr,
                    hasEvent: !!this.eventsList[dateString],
                    eventInfo: this.eventsList[dateString] || ''
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
            this.updateCalendarView();
        },
        
        nextMonth() {
            if (this.currentMonth === 11) {
                this.currentMonth = 0;
                this.currentYear++;
            } else {
                this.currentMonth++;
            }
            this.updateCalendarView();
        },

        goToToday() {
            const today = new Date();
            this.currentMonth = today.getMonth();
            this.currentYear = today.getFullYear();
            this.updateCalendarView();
        },

        updateCalendarView() {
            this.destroyPopovers();
            this.generateCalendar();
            // Allow Alpine to render DOM update before re-attaching triggers
            this.$nextTick(() => { this.initPopovers(); });
        },

        initPopovers() {
            const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
            popoverTriggerList.forEach(popoverTriggerEl => {
                new bootstrap.Popover(popoverTriggerEl);
            });
        },

        destroyPopovers() {
            const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
            popoverTriggerList.forEach(popoverTriggerEl => {
                const instance = bootstrap.Popover.getInstance(popoverTriggerEl);
                if (instance) instance.dispose();
            });
        }
    }
}
