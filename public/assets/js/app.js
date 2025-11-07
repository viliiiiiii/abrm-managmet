import { initModals } from './ui/modal.js';
import { initContextMenus } from './ui/contextmenu.js';
import { Toasts } from './ui/notify.js';
import { initAnimations } from './ui/animations.js';

document.addEventListener('DOMContentLoaded', () => {
  initModals();
  initContextMenus();
  initAnimations();
  setupTheme();
  registerServiceWorker();
});

function setupTheme() {
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  const stored = localStorage.getItem('abrm-theme');
  if (stored) {
    document.body.classList.toggle('dark', stored === 'dark');
    syncThemeIcon(stored === 'dark');
  } else {
    document.body.classList.toggle('dark', prefersDark);
    syncThemeIcon(prefersDark);
  }
  document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const isDark = document.body.classList.toggle('dark');
      localStorage.setItem('abrm-theme', isDark ? 'dark' : 'light');
      syncThemeIcon(isDark);
    });
  });
}

function syncThemeIcon(isDark) {
  document.querySelectorAll('[data-icon]').forEach((icon) => {
    icon.textContent = isDark ? '☀' : '☾';
  });
}

function registerServiceWorker() {
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/service-worker.js');
  }
}

window.toasts = new Toasts(document.querySelector('.toast-container'));
