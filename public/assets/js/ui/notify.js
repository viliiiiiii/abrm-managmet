export class Toasts {
  constructor(container) {
    this.container = container;
  }

  push(message, type = 'info') {
    if (!this.container) return;
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    this.container.appendChild(toast);
    setTimeout(() => {
      toast.classList.add('fade');
      toast.addEventListener('transitionend', () => toast.remove(), { once: true });
      toast.remove();
    }, 4000);
  }
}
