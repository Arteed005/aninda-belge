(function () {
  var modal = document.getElementById('property-modal');
  if (!modal) return;

  var closeBtn = document.getElementById('property-modal-close');
  var title = document.getElementById('property-modal-title');
  var actionField = document.getElementById('property-form-action');
  var idField = document.getElementById('property-form-id');
  var form = modal.querySelector('form');
  var ownerCheckboxes = document.querySelectorAll('.property-owner-checkbox');

  var fieldIds = [
    'title', 'province', 'district', 'neighborhood', 'unit_no', 'address', 'floor',
    'room_count', 'gross_sqm', 'block_no', 'parcel_no', 'independent_section_no',
    'title_deed_info', 'rent_amount', 'deposit_amount', 'description',
  ];

  function fieldEl(name) {
    return form.querySelector('[name="' + name + '"]');
  }

  function openModal(btn) {
    var propertyJson = btn.getAttribute('data-property');
    var property = propertyJson ? JSON.parse(propertyJson) : null;
    var ownerIdsJson = btn.getAttribute('data-property-owners');
    var ownerIds = ownerIdsJson ? JSON.parse(ownerIdsJson).map(String) : [];

    if (property) {
      title.textContent = 'Taşınmazı Düzenle';
      actionField.value = 'update';
      idField.value = property.id;
    } else {
      title.textContent = 'Yeni Taşınmaz Ekle';
      actionField.value = 'create';
      idField.value = '';
    }

    fieldIds.forEach(function (name) {
      var el = fieldEl(name);
      if (el) el.value = property && property[name] ? property[name] : '';
    });

    ownerCheckboxes.forEach(function (cb) {
      cb.checked = ownerIds.indexOf(cb.value) !== -1;
    });

    modal.classList.remove('hidden');
  }

  function closeModal() {
    modal.classList.add('hidden');
  }

  document.querySelectorAll('[data-open-property-modal]').forEach(function (btn) {
    btn.addEventListener('click', function () { openModal(btn); });
  });
  closeBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
})();
