export default function ViewToggle({ currentView, onViewChange }) {
  return (
    <div className="inline-flex items-center space-x-1 rounded-xl bg-slate-100 p-1">
      <button
        onClick={() => onViewChange('list')}
        className={`rounded-lg px-4 py-1.5 text-sm font-medium transition ${
          currentView === 'list'
            ? 'bg-white text-slate-800 shadow-sm'
            : 'text-slate-500 hover:text-slate-700'
        }`}
      >
        List view
      </button>
      <button
        onClick={() => onViewChange('calendar')}
        className={`rounded-lg px-4 py-1.5 text-sm font-medium transition ${
          currentView === 'calendar'
            ? 'bg-white text-slate-800 shadow-sm'
            : 'text-slate-500 hover:text-slate-700'
        }`}
      >
        Calendar view
      </button>
    </div>
  );
}