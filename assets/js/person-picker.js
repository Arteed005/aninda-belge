(function () {
  var pickers = document.querySelectorAll('.person-picker-select');
  if (!pickers.length) return;

  fetch('kisiler-api.php')
    .then(function (res) { return res.json(); })
    .then(function (persons) {
      if (!Array.isArray(persons) || !persons.length) return;

      pickers.forEach(function (select) {
        persons.forEach(function (person) {
          var opt = document.createElement('option');
          opt.value = String(person.id);
          opt.textContent = person.full_name;
          select.appendChild(opt);
        });

        select.addEventListener('change', function () {
          var person = persons.find(function (p) { return String(p.id) === select.value; });
          if (!person) return;

          var map = JSON.parse(select.getAttribute('data-map') || '{}');
          Object.keys(map).forEach(function (personKey) {
            var fieldName = map[personKey];
            if (!fieldName) return;
            var input = document.querySelector('[data-field="' + fieldName + '"]');
            if (!input) return;
            input.value = person[personKey] || '';
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
          });
        });
      });
    })
    .catch(function () { /* sessizce yut, seçim kutusu boş kalır */ });
})();
