(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('category-search-input');
    var grid = document.getElementById('category-grid');
    var emptyState = document.getElementById('category-empty');
    var emptyQuery = document.getElementById('category-empty-query');
    if (!input || !grid) return;

    var cards = Array.prototype.slice.call(grid.querySelectorAll('.category-card'));

    input.addEventListener('input', function () {
      var q = input.value.trim().toLocaleLowerCase('tr');
      var visibleCount = 0;

      cards.forEach(function (card) {
        var matches = !q
          || card.getAttribute('data-name').indexOf(q) !== -1
          || card.getAttribute('data-desc').indexOf(q) !== -1;
        card.style.display = matches ? '' : 'none';
        if (matches) visibleCount++;
      });

      if (emptyState) {
        emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
      }
      if (emptyQuery) {
        emptyQuery.textContent = '"' + input.value.trim() + '" ile eşleşen bir şablon yok.';
      }
    });
  });
})();
