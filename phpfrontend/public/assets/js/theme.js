function getTheme() {
    const currentTheme = document.documentElement.dataset.theme;

    if (currentTheme === 'dark' || currentTheme === 'light') {
        return currentTheme;
    }

    const savedTheme = localStorage.getItem('theme');

    if (savedTheme === 'dark' || savedTheme === 'light') {
        return savedTheme;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function applyTheme(theme) {
    const isDark = theme === 'dark';
    const html = document.documentElement;

    html.dataset.theme = isDark ? 'dark' : 'light';
    html.classList.toggle('dark', isDark);
    localStorage.setItem('theme', isDark ? 'dark' : 'light');

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        const nextTheme = isDark ? 'light' : 'dark';
        const label = `Switch to ${nextTheme} mode`;

        button.setAttribute('aria-label', label);
        button.setAttribute('title', label);
        button.setAttribute('aria-pressed', String(isDark));
    });
}

function toggleTheme() {
    applyTheme(getTheme() === 'dark' ? 'light' : 'dark');
}

document.addEventListener('DOMContentLoaded', () => {
    applyTheme(getTheme());

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', toggleTheme);
    });
});
