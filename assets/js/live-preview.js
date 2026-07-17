(function () {
  'use strict';

  function parseLineSegments(lineTemplate) {
    var parts = lineTemplate.split(/\{([a-zA-Z0-9_]+)\}/);
    var segments = [];
    for (var i = 0; i < parts.length; i++) {
      if (i % 2 === 0) {
        if (parts[i] !== '') segments.push({ type: 'text', value: parts[i] });
      } else {
        segments.push({ type: 'field', name: parts[i] });
      }
    }
    return segments;
  }

  function formatCurrencyTry(value) {
    var digits = value.replace(/[^0-9]/g, '');
    var n = parseInt(digits, 10);
    if (digits && n > 0) return n.toLocaleString('tr-TR') + ' TL';
    return value + ' TL';
  }

  function formatDateTr(value) {
    var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);
    return m ? (m[3] + '.' + m[2] + '.' + m[1]) : value;
  }

  function formatFieldValue(raw, format) {
    var value = (raw || '').toString().trim();
    if (!value) return { text: '—', empty: true };
    if (format === 'currency-try') return { text: formatCurrencyTry(value), empty: false };
    if (format === 'date-tr') return { text: formatDateTr(value), empty: false };
    return { text: value, empty: false };
  }

  function autoContext() {
    var d = new Date();
    var pad = function (n) { return String(n).padStart(2, '0'); };
    return { bugun_tarihi: pad(d.getDate()) + '.' + pad(d.getMonth() + 1) + '.' + d.getFullYear() };
  }

  function renderClauses(config, values) {
    var fieldFormats = {};
    (config.fields || []).forEach(function (f) { fieldFormats[f.name] = f.format || null; });
    var context = autoContext();

    return (config.clauses || []).map(function (clause) {
      return {
        title: clause.title,
        lines: String(clause.template || '').split('\n').map(function (lineTpl) {
          return parseLineSegments(lineTpl).map(function (seg) {
            if (seg.type === 'text') return seg;
            var name = seg.name;
            if (Object.prototype.hasOwnProperty.call(context, name) && !(name in values)) {
              return { type: 'text', value: context[name] };
            }
            var formatted = formatFieldValue(values[name], fieldFormats[name]);
            return { type: 'field', name: name, text: formatted.text, empty: formatted.empty };
          });
        })
      };
    });
  }

  function renderCustomClauses(texts) {
    return texts.map(function (text, i) {
      return {
        title: 'EK MADDE ' + (i + 1),
        lines: String(text).split('\n').map(function (line) {
          return line !== '' ? [{ type: 'text', value: line }] : [];
        })
      };
    });
  }

  function buildPreviewDom(container, renderedClauses) {
    container.innerHTML = '';
    renderedClauses.forEach(function (clause) {
      var block = document.createElement('div');
      block.className = 'madde-block';

      var title = document.createElement('p');
      title.className = 'madde-title';
      title.textContent = clause.title;
      block.appendChild(title);

      clause.lines.forEach(function (line) {
        var p = document.createElement('p');
        p.className = 'madde-line';
        line.forEach(function (seg) {
          if (seg.type === 'text') {
            p.appendChild(document.createTextNode(seg.value));
          } else {
            var span = document.createElement('span');
            span.className = 'fv ' + (seg.empty ? 'fv-empty' : 'fv-filled');
            span.setAttribute('data-field', seg.name);
            span.textContent = seg.text;
            p.appendChild(span);
          }
        });
        block.appendChild(p);
      });

      container.appendChild(block);
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var configEl = document.getElementById('tpl-config');
    var form = document.getElementById('doc-form');
    if (!configEl || !form) return;

    var config = JSON.parse(configEl.textContent);

    var previewEl = document.getElementById('preview-clauses');
    if (!previewEl) return;

    var submitBtn = document.getElementById('download-btn');
    var hintEl = document.getElementById('required-hint');
    var backBtn = document.getElementById('wizard-back-btn');
    var nextBtn = document.getElementById('wizard-next-btn');

    var panels = Array.prototype.slice.call(document.querySelectorAll('[data-step-panel]'));
    var dots = Array.prototype.slice.call(document.querySelectorAll('[data-step-dot]'));
    var stepFieldNames = panels.map(function (panel) {
      return Array.prototype.slice.call(panel.querySelectorAll('[data-field]')).map(function (el) {
        return el.getAttribute('data-field');
      });
    });

    var values = {};
    var currentStep = 0;
    var maxReachedStep = 0;
    var highlightTimer = null;

    function fieldDef(name) {
      return (config.fields || []).filter(function (f) { return f.name === name; })[0];
    }

    function stepRequiredOk(stepIndex) {
      return stepFieldNames[stepIndex].every(function (name) {
        var f = fieldDef(name);
        if (!f || !f.required) return true;
        return (values[name] || '').trim().length > 0;
      });
    }

    function allRequiredOk() {
      return (config.fields || []).filter(function (f) { return f.required; })
        .every(function (f) { return (values[f.name] || '').trim().length > 0; });
    }

    function updateNavState() {
      var isLast = currentStep === panels.length - 1;

      panels.forEach(function (panel, i) { panel.classList.toggle('active', i === currentStep); });
      dots.forEach(function (dot, i) {
        dot.classList.toggle('active', i === currentStep);
        dot.classList.toggle('done', i < currentStep);
      });

      backBtn.style.display = currentStep === 0 ? 'none' : 'inline-block';
      nextBtn.style.display = isLast ? 'none' : 'block';
      submitBtn.style.display = isLast ? 'flex' : 'none';

      nextBtn.disabled = !stepRequiredOk(currentStep);
      submitBtn.disabled = !allRequiredOk();
      hintEl.style.display = (isLast && submitBtn.disabled) ? 'block' : 'none';
    }

    function goToStep(i) {
      currentStep = Math.max(0, Math.min(panels.length - 1, i));
      maxReachedStep = Math.max(maxReachedStep, currentStep);
      updateNavState();
      form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    backBtn.addEventListener('click', function () { goToStep(currentStep - 1); });
    nextBtn.addEventListener('click', function () {
      if (!nextBtn.disabled) goToStep(currentStep + 1);
    });
    dots.forEach(function (dot, i) {
      dot.addEventListener('click', function () {
        if (i <= maxReachedStep) goToStep(i);
      });
    });

    var customClausesList = document.getElementById('custom-clauses-list');
    var addClauseBtn = document.getElementById('add-custom-clause-btn');

    function getCustomClauseTexts() {
      if (!customClausesList) return [];
      return Array.prototype.slice.call(customClausesList.querySelectorAll('.custom-clause-textarea'))
        .map(function (el) { return el.value; })
        .filter(function (v) { return v.trim().length > 0; });
    }

    function updatePreview(changedField) {
      var rendered = renderClauses(config, values).concat(renderCustomClauses(getCustomClauseTexts()));
      buildPreviewDom(previewEl, rendered);
      if (changedField) {
        var span = previewEl.querySelector('[data-field="' + CSS.escape(changedField) + '"]');
        if (span) {
          span.classList.add('hl-flash');
          clearTimeout(highlightTimer);
          highlightTimer = setTimeout(function () { span.classList.remove('hl-flash'); }, 650);
        }
      }
    }

    form.querySelectorAll('[data-field]').forEach(function (input) {
      var name = input.getAttribute('data-field');
      values[name] = input.value || '';
      ['input', 'change'].forEach(function (evt) {
        input.addEventListener(evt, function () {
          values[name] = input.value;
          updatePreview(name);
          updateNavState();
        });
      });
    });

    function renumberCustomClauses() {
      Array.prototype.slice.call(customClausesList.querySelectorAll('.custom-clause-item')).forEach(function (item, i) {
        item.querySelector('label').textContent = 'Ek Madde ' + (i + 1);
      });
    }

    function wireCustomClauseItem(item) {
      var textarea = item.querySelector('.custom-clause-textarea');
      var removeBtn = item.querySelector('.custom-clause-remove');
      textarea.addEventListener('input', function () { updatePreview(null); });
      removeBtn.addEventListener('click', function () {
        item.remove();
        renumberCustomClauses();
        updatePreview(null);
      });
    }

    function addCustomClauseBlock() {
      var idx = customClausesList.children.length;
      var item = document.createElement('div');
      item.className = 'custom-clause-item';

      var label = document.createElement('label');
      label.textContent = 'Ek Madde ' + (idx + 1);

      var textarea = document.createElement('textarea');
      textarea.name = 'extra_clauses[]';
      textarea.className = 'custom-clause-textarea';
      textarea.rows = 3;
      textarea.placeholder = 'Ek madde metnini yaz...';

      var removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'custom-clause-remove';
      removeBtn.textContent = 'Kaldır';

      item.appendChild(label);
      item.appendChild(textarea);
      item.appendChild(removeBtn);
      customClausesList.appendChild(item);
      wireCustomClauseItem(item);
    }

    if (addClauseBtn) {
      addClauseBtn.addEventListener('click', function () {
        addCustomClauseBlock();
        updatePreview(null);
      });
    }

    if (customClausesList) {
      Array.prototype.slice.call(customClausesList.querySelectorAll('.custom-clause-item')).forEach(wireCustomClauseItem);
    }

    updatePreview(null);
    updateNavState();
  });
})();
