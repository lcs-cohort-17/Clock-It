import ThemeToggle from '../ThemeToggle';

const Header = () => {
  return (
    <header className="flex justify-end items-center p-4">
      <ThemeToggle />
      <div className="status-badge">Online</div>
      <div className="user-badge">Admin</div>
    </header>
  );
};