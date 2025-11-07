export function initModals() {
  document.querySelectorAll('[data-modal]').forEach((trigger) => {
    const targetId = trigger.getAttribute('data-modal');
    const target = document.getElementById(targetId);
    if (!target) return;
    trigger.addEventListener('click', () => openModal(target));
  });
  document.querySelectorAll('.modal [data-close]').forEach((button) => {
    const modal = button.closest('.modal-backdrop');
    button.addEventListener('click', () => closeModal(modal));
  });
}

function openModal(backdrop) {
  backdrop.classList.add('active');
  const focusable = backdrop.querySelector('input, button, [tabindex]');
  if (focusable) focusable.focus();
}

function closeModal(backdrop) {
  backdrop.classList.remove('active');
}

export { openModal, closeModal };
