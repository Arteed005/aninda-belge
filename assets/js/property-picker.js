(function () {
  var pickers = document.querySelectorAll('.property-picker-select');
  if (!pickers.length) return;

  fetch('emlak-tasinmazlar-api.php')
    .then(function (res) { return res.json(); })
    .then(function (properties) {
      if (!Array.isArray(properties) || !properties.length) return;

      pickers.forEach(function (select) {
        properties.forEach(function (property) {
          var opt = document.createElement('option');
          opt.value = String(property.id);
          opt.textContent = property.title;
          select.appendChild(opt);
        });

        select.addEventListener('change', function () {
          var property = properties.find(function (p) { return String(p.id) === select.value; });
          if (!property) return;

          var map = JSON.parse(select.getAttribute('data-map') || '{}');
          Object.keys(map).forEach(function (propertyKey) {
            var fieldName = map[propertyKey];
            if (!fieldName) return;
            var input = document.querySelector('[data-field="' + fieldName + '"]');
            if (!input) return;
            input.value = property[propertyKey] || '';
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
          });
        });
      });
    })
    .catch(function () { /* sessizce yut, seçim kutusu boş kalır */ });
})();
