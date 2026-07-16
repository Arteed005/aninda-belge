document.addEventListener('click', function (e) {
  const trigger = e.target.closest('.admin-row-action-trigger');
  document.querySelectorAll('.admin-row-action-menu.open').forEach(function (menu) {
    if (!trigger || menu !== trigger.nextElementSibling) {
      menu.classList.remove('open');
    }
  });
  if (trigger) {
    const menu = trigger.nextElementSibling;
    if (menu && menu.classList.contains('admin-row-action-menu')) {
      menu.classList.toggle('open');
    }
  }
});

document.addEventListener('submit', function (e) {
  const form = e.target.closest('[data-confirm]');
  if (form && !window.confirm(form.getAttribute('data-confirm'))) {
    e.preventDefault();
  }
});
