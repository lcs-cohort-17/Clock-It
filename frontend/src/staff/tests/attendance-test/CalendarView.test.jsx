export default function CalendarView({ records }) {
  // A simple placeholder layout matching dashboard style.
  // Ideally, map your filtered records into a grid based on their days.
  return (
    <div className="calendar-grid-mock">
      <h3>Calendar View</h3>
      <p>Showing {records.length} items on the schedule.</p>
      <div className="calendar-placeholder-box">
        {records.map(record => (
          <div key={record.id} className="calendar-event-tag">
            {/* Swapped record.title for clock-in data so it displays nicely */}
            <strong>Clock In: {record.clockIn}</strong> - {new Date(record.date).toLocaleDateString()}
          </div>
        ))}
      </div>
    </div>
  );
}