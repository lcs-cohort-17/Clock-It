import React, { useMemo, useState } from 'react';
import { MdChevronLeft, MdChevronRight } from 'react-icons/md';

export interface AttendanceEvent {
  date: string;
  type: 'clock-in' | 'clock-out';
  time: string;
}

interface CalendarProps {
  events?: AttendanceEvent[];
}

const Calendar: React.FC<CalendarProps> = ({ events = [] }) => {
  const today = new Date();
  const todayKey = formatDateKey(today);
  const [visibleMonth, setVisibleMonth] = useState(() => new Date(today.getFullYear(), today.getMonth(), 1));
  const [selectedDate, setSelectedDate] = useState<string | null>(todayKey);
  const monthStart = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth(), 1);
  const daysInMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + 1, 0).getDate();
  const previousMonthDays = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth(), 0).getDate();
  const monthName = visibleMonth.toLocaleString('default', { month: 'long', year: 'numeric' });

  const eventsByDate = useMemo(() => {
    return events.reduce<Record<string, AttendanceEvent[]>>((grouped, event) => {
      grouped[event.date] = [...(grouped[event.date] ?? []), event];
      return grouped;
    }, {});
  }, [events]);

  const selectedEvents = selectedDate ? eventsByDate[selectedDate] ?? [] : [];
  const selectedDateLabel = selectedDate
    ? new Date(`${selectedDate}T00:00:00`).toLocaleDateString('en-ZA', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
      })
    : '';

  const goToPreviousMonth = () => {
    setVisibleMonth(prev => new Date(prev.getFullYear(), prev.getMonth() - 1, 1));
  };

  const goToNextMonth = () => {
    setVisibleMonth(prev => new Date(prev.getFullYear(), prev.getMonth() + 1, 1));
  };

  const buildDateKey = (year: number, month: number, day: number) =>
    `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

  const renderDateButton = (date: string, day: number, isCurrentMonth: boolean) => {
    const hasEvents = Boolean(eventsByDate[date]?.length);
    const isSelected = selectedDate === date;
    const isToday = date === todayKey;

    return (
      <button
        key={date}
        type="button"
        onClick={() => setSelectedDate(date)}
        className={`relative flex h-11 w-11 items-center justify-center rounded-xl text-lg font-semibold transition ${
          isSelected
            ? 'bg-[#093C5D] text-white shadow-lg shadow-[#093C5D]/20'
            : hasEvents
              ? 'bg-[#2563eb] text-white shadow-sm shadow-blue-500/20 hover:bg-[#1d4ed8]'
              : isToday
                ? 'border border-[#2563eb] bg-[#DBEAFE] text-[#0f172a] shadow-sm dark:border-[#3b82f6] dark:bg-[#1e3a8a] dark:text-[#eff6ff]'
                : isCurrentMonth
                  ? 'text-[#164068] hover:bg-slate-100 dark:text-[#eff6ff] dark:hover:bg-[#164068]'
                  : 'text-slate-400'
        }`}
        aria-label={`${date}${hasEvents ? ' has attendance events' : ''}`}
      >
        {day}
        {hasEvents && !isSelected && (
          <span
            aria-label="attendance event"
            className="absolute bottom-1 left-1/2 h-1.5 w-1.5 -translate-x-1/2 rounded-full bg-white"
          />
        )}
        {hasEvents && isSelected && (
          <span
            aria-label="attendance event"
            className="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 rounded-full bg-[#f8fafc]"
          />
        )}
        {isToday && !hasEvents && !isSelected && (
          <span className="absolute bottom-1 left-1/2 h-1.5 w-1.5 -translate-x-1/2 rounded-full bg-[#2563eb]" />
        )}
      </button>
    );
  };

  return (
    <div className="calendar-component mx-auto max-w-sm rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition dark:border-[#163856] dark:bg-[#0b2142]">
      <div className="mb-4 grid grid-cols-[44px_1fr_44px] items-center gap-4">
        <button
          type="button"
          onClick={goToPreviousMonth}
          className="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-[#164068] transition hover:bg-slate-100 dark:border-[#23456f] dark:text-[#eff6ff] dark:hover:bg-[#164068]"
          aria-label="Previous month"
        >
          <MdChevronLeft size={22} />
        </button>

        <h2 className="text-center text-lg font-bold text-[#093C5D] dark:text-[#eff6ff]">{monthName}</h2>

        <button
          type="button"
          onClick={goToNextMonth}
          className="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-[#164068] transition hover:bg-slate-100 dark:border-[#23456f] dark:text-[#eff6ff] dark:hover:bg-[#164068]"
          aria-label="Next month"
        >
          <MdChevronRight size={22} />
        </button>
      </div>

      <div className="grid grid-cols-7 gap-2 text-center text-base font-semibold text-[#164068] dark:text-[#c7d89a]">
        {['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'].map(day => (
          <div key={day}>{day}</div>
        ))}
      </div>

      <div className="mt-4 grid grid-cols-7 justify-items-center gap-x-2 gap-y-3">
        {Array.from({ length: monthStart.getDay() }).map((_, index) => {
          const day = previousMonthDays - monthStart.getDay() + index + 1;
          const previousMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() - 1, 1);
          const date = buildDateKey(previousMonth.getFullYear(), previousMonth.getMonth(), day);
          return renderDateButton(date, day, false);
        })}

        {Array.from({ length: daysInMonth }).map((_, index) => {
          const day = index + 1;
          const date = buildDateKey(visibleMonth.getFullYear(), visibleMonth.getMonth(), day);
          return renderDateButton(date, day, true);
        })}

        {Array.from({ length: (7 - ((monthStart.getDay() + daysInMonth) % 7)) % 7 }).map((_, index) => {
          const day = index + 1;
          const nextMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + 1, 1);
          const date = buildDateKey(nextMonth.getFullYear(), nextMonth.getMonth(), day);
          return renderDateButton(date, day, false);
        })}
      </div>

      {selectedDate && (
        <div role="status" className="mt-10 text-center text-lg text-[#3B5C74]">
          <p>
            Selected: <span className="font-bold text-[#093C5D]">{selectedDateLabel}</span>
          </p>
          {selectedEvents.length > 0 ? (
            <ul className="mt-3 space-y-1 text-sm text-[#3B5C74]">
              {selectedEvents.map(event => (
                <li key={`${event.date}-${event.type}-${event.time}`}>
                  {event.type === 'clock-in' ? 'Clock in' : 'Clock out'}: {event.time}
                </li>
              ))}
            </ul>
          ) : (
            <p className="sr-only">No attendance events for this day.</p>
          )}
        </div>
      )}
    </div>
  );
};

function formatDateKey(date: Date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

export default Calendar;
