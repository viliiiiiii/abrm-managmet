export function initContextMenus() {
  document.querySelectorAll('[data-context]').forEach((target) => {
    const menuId = target.getAttribute('data-context');
    const menu = document.getElementById(menuId);
    if (!menu) return;
    target.addEventListener('contextmenu', (event) => {
      event.preventDefault();
      menu.style.left = event.pageX + 'px';
      menu.style.top = event.pageY + 'px';
      menu.classList.add('active');
    });
  });
  document.addEventListener('click', () => {
    document.querySelectorAll('.context-menu').forEach((menu) => menu.classList.remove('active'));
  });
}
