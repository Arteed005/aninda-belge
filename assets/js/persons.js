(function () {
  var modal = document.getElementById('person-modal');
  if (!modal) return;

  var closeBtn = document.getElementById('person-modal-close');
  var title = document.getElementById('person-modal-title');
  var actionField = document.getElementById('person-form-action');
  var idField = document.getElementById('person-form-id');
  var nameField = document.getElementById('person-full-name');
  var tcField = document.getElementById('person-tc-no');
  var phoneField = document.getElementById('person-phone');
  var emailField = document.getElementById('person-email');
  var addressField = document.getElementById('person-address');

  function openModal(btn) {
    var personId = btn.getAttribute('data-person-id');
    if (personId) {
      title.textContent = 'Kişiyi Düzenle';
      actionField.value = 'update';
      idField.value = personId;
      nameField.value = btn.getAttribute('data-person-name') || '';
      tcField.value = btn.getAttribute('data-person-tc') || '';
      phoneField.value = btn.getAttribute('data-person-phone') || '';
      emailField.value = btn.getAttribute('data-person-email') || '';
      addressField.value = btn.getAttribute('data-person-address') || '';
    } else {
      title.textContent = 'Yeni Kişi Ekle';
      actionField.value = 'create';
      idField.value = '';
      nameField.value = '';
      tcField.value = '';
      phoneField.value = '';
      emailField.value = '';
      addressField.value = '';
    }
    modal.classList.remove('hidden');
  }

  function closeModal() {
    modal.classList.add('hidden');
  }

  document.querySelectorAll('[data-open-person-modal]').forEach(function (btn) {
    btn.addEventListener('click', function () { openModal(btn); });
  });
  closeBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
})();
