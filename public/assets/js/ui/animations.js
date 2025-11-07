export function initAnimations() {
  document.querySelectorAll('[data-animate]').forEach((element, index) => {
    element.style.opacity = '0';
    element.style.transform = 'translateY(10px)';
    requestAnimationFrame(() => {
      element.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
      element.style.transitionDelay = `${index * 60}ms`;
      element.style.opacity = '1';
      element.style.transform = 'translateY(0)';
    });
  });
}
