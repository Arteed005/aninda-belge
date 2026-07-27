(function () {
  var modal = document.getElementById('person-modal');
  if (!modal) return;

  var closeBtn = document.getElementById('person-modal-close');
  var title = document.getElementById('person-modal-title');
  var actionField = document.getElementById('person-form-action');
  var idField = document.getElementById('person-form-id');
  var nameField = document.getElementById('person-full-name');
  var typeField = document.getElementById('person-type');
  var tcField = document.getElementById('person-tc-no');
  var phoneField = document.getElementById('person-phone');
  var emailField = document.getElementById('person-email');
  var addressField = document.getElementById('person-address');
  var notesField = document.getElementById('person-notes');
  var addressesContainer = document.getElementById('person-extra-addresses');
  var addAddressBtn = document.getElementById('person-add-address-row');

  var addressRowIndex = 0;

  function addAddressRow(label, address) {
    var i = addressRowIndex++;
    var row = document.createElement('div');
    row.className = 'person-address-row';
    row.innerHTML =
      '<input type="text" name="addresses[' + i + '][label]" placeholder="Etiket (ör: İş adresi)" value="' + (label ? String(label).replace(/"/g, '&quot;') : '') + '">' +
      '<textarea name="addresses[' + i + '][address]" rows="2" placeholder="Adres">' + (address ? String(address) : '') + '</textarea>' +
      '<button type="button" class="person-address-remove">✕</button>';
    row.querySelector('.person-address-remove').addEventListener('click', function () { row.remove(); });
    addressesContainer.appendChild(row);
  }

  function openModal(btn) {
    var personId = btn.getAttribute('data-person-id');
    addressesContainer.innerHTML = '';
    addressRowIndex = 0;
    if (personId) {
      title.textContent = 'Kişiyi Düzenle';
      actionField.value = 'update';
      idField.value = personId;
      nameField.value = btn.getAttribute('data-person-name') || '';
      typeField.value = btn.getAttribute('data-person-type') || '';
      tcField.value = btn.getAttribute('data-person-tc') || '';
      phoneField.value = btn.getAttribute('data-person-phone') || '';
      emailField.value = btn.getAttribute('data-person-email') || '';
      addressField.value = btn.getAttribute('data-person-address') || '';
      notesField.value = btn.getAttribute('data-person-notes') || '';
      var addressesJson = btn.getAttribute('data-person-addresses');
      if (addressesJson) {
        try {
          var addresses = JSON.parse(addressesJson);
          addresses.forEach(function (a) { addAddressRow(a.label, a.address); });
        } catch (e) { /* geçersiz JSON, boş liste ile devam */ }
      }
    } else {
      title.textContent = 'Yeni Kişi Ekle';
      actionField.value = 'create';
      idField.value = '';
      nameField.value = '';
      typeField.value = '';
      tcField.value = '';
      phoneField.value = '';
      emailField.value = '';
      addressField.value = '';
      notesField.value = '';
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
  addAddressBtn.addEventListener('click', function () { addAddressRow('', ''); });
})();
