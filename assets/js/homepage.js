(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var catalogEl = document.getElementById('catalog-data');
    var catalog = catalogEl ? JSON.parse(catalogEl.textContent) : [];

    var form = document.getElementById('search-form');
    var input = document.getElementById('search-input');
    var dropdown = document.getElementById('search-dropdown');
    var chipsArea = document.getElementById('chips-area');
    var modal = document.getElementById('template-modal');
    var modalTitle = document.getElementById('modal-title');
    var modalClose = document.getElementById('modal-close');
    var modalOk = document.getElementById('modal-ok');
    if (!form || !input || !dropdown) return;

    function openModal(name) {
      modalTitle.textContent = name;
      modal.classList.remove('hidden');
    }
    function closeModal() {
      modal.classList.add('hidden');
    }
    modalClose.addEventListener('click', closeModal);
    modalOk.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });

    function goToOrModal(item) {
      if (item.available) {
        window.location.href = item.href || ('sablon.php?slug=' + encodeURIComponent(item.slug));
      } else {
        openModal(item.name);
      }
    }

    function renderDropdown() {
      var q = input.value.trim().toLocaleLowerCase('tr');
      dropdown.innerHTML = '';
      if (!q) {
        dropdown.classList.remove('open');
        chipsArea.classList.remove('hidden');
        return;
      }
      var results = catalog.filter(function (t) {
        return t.name.toLocaleLowerCase('tr').indexOf(q) !== -1;
      }).slice(0, 5);

      if (results.length === 0) {
        dropdown.classList.remove('open');
        chipsArea.classList.remove('hidden');
        return;
      }

      chipsArea.classList.add('hidden');
      results.forEach(function (item) {
        var row = document.createElement('div');
        row.className = 'search-result';

        var name = document.createElement('span');
        name.className = 'search-result-name';
        name.textContent = item.name;

        var cat = document.createElement('span');
        cat.className = 'search-result-cat';
        cat.textContent = item.cat;

        row.appendChild(name);
        row.appendChild(cat);
        row.addEventListener('mousedown', function (e) {
          e.preventDefault();
          goToOrModal(item);
        });
        dropdown.appendChild(row);
      });
      dropdown.classList.add('open');
    }

    input.addEventListener('input', renderDropdown);
    input.addEventListener('focus', renderDropdown);
    input.addEventListener('blur', function () {
      setTimeout(function () {
        dropdown.classList.remove('open');
        chipsArea.classList.remove('hidden');
      }, 120);
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var q = input.value.trim();
      if (!q) return;
      var qLower = q.toLocaleLowerCase('tr');
      var match = catalog.find(function (t) { return t.name.toLocaleLowerCase('tr').indexOf(qLower) !== -1; });
      goToOrModal(match || { name: q, available: false });
    });

    document.querySelectorAll('[data-chip]').forEach(function (btn) {
      btn.addEventListener('mousedown', function (e) {
        e.preventDefault();
        input.value = btn.getAttribute('data-chip');
        renderDropdown();
        input.focus();
      });
    });

    document.querySelectorAll('[data-modal-template]').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        openModal(btn.getAttribute('data-modal-template'));
      });
    });
  });
})();
