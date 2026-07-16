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

  // --- Resume ("kind": "resume") mirrors of lib/ResumeRenderer.php / templates/resume-shell.php ---

  function buildResumeData(config, values, groupEntries) {
    function get(name) { return (values[name] || '').toString().trim(); }
    function splitList(csv) {
      return csv.split(',').map(function (s) { return s.trim(); }).filter(function (s) { return s !== ''; });
    }

    var sections = (config.groups || []).map(function (group) {
      return { title: group.title, fields: group.fields, entries: groupEntries[group.key] || [] };
    });

    return {
      full_name: get('full_name'),
      title: get('title'),
      email: get('email'),
      phone: get('phone'),
      location: get('location'),
      linkedin: get('linkedin'),
      summary: get('summary'),
      skills: splitList(get('skills')),
      languages: splitList(get('languages')),
      sections: sections
    };
  }

  function renderResumePreview(container, data) {
    container.innerHTML = '';

    function addSection(titleText, bodyBuilder) {
      var sec = document.createElement('div');
      sec.className = 'resume-preview-section';
      var t = document.createElement('p');
      t.className = 'resume-preview-section-title';
      t.textContent = titleText;
      sec.appendChild(t);
      bodyBuilder(sec);
      container.appendChild(sec);
    }

    var header = document.createElement('div');
    header.className = 'resume-preview-header';
    var nameEl = document.createElement('p');
    nameEl.className = 'resume-preview-name';
    nameEl.textContent = data.full_name || 'Ad Soyad';
    header.appendChild(nameEl);
    if (data.title) {
      var titleEl = document.createElement('p');
      titleEl.className = 'resume-preview-title';
      titleEl.textContent = data.title;
      header.appendChild(titleEl);
    }
    var contact = [data.email, data.phone, data.location, data.linkedin].filter(function (v) { return v; });
    if (contact.length) {
      var contactEl = document.createElement('p');
      contactEl.className = 'resume-preview-contact';
      contactEl.textContent = contact.join(' · ');
      header.appendChild(contactEl);
    }
    container.appendChild(header);

    if (data.summary) {
      addSection('HAKKIMDA', function (sec) {
        var p = document.createElement('p');
        p.className = 'resume-preview-text';
        p.textContent = data.summary;
        sec.appendChild(p);
      });
    }

    data.sections.forEach(function (section) {
      if (!section.entries.length) return;
      var fieldNames = section.fields.map(function (f) { return f.name; });
      addSection(String(section.title).toLocaleUpperCase('tr-TR'), function (sec) {
        section.entries.forEach(function (entry) {
          var primary = entry[fieldNames[0]] || '';
          var secondary = entry[fieldNames[1]] || '';
          var dateRange = ((entry.start_date || '') + (entry.end_date ? ' — ' + entry.end_date : '')).trim();
          var description = entry.description || '';

          var entryEl = document.createElement('div');
          entryEl.className = 'resume-preview-entry';

          var row = document.createElement('div');
          row.className = 'resume-preview-entry-row';
          var main = document.createElement('span');
          main.className = 'resume-preview-entry-main';
          if (primary) {
            var pEl = document.createElement('span');
            pEl.className = 'resume-preview-entry-primary';
            pEl.textContent = primary;
            main.appendChild(pEl);
          }
          if (secondary) {
            var sEl = document.createElement('span');
            sEl.className = 'resume-preview-entry-secondary';
            sEl.textContent = (primary ? ' — ' : '') + secondary;
            main.appendChild(sEl);
          }
          row.appendChild(main);
          var datesEl = document.createElement('span');
          datesEl.className = 'resume-preview-entry-dates';
          datesEl.textContent = dateRange;
          row.appendChild(datesEl);
          entryEl.appendChild(row);

          description.split('\n').forEach(function (line) {
            if (!line) return;
            var descP = document.createElement('p');
            descP.className = 'resume-preview-entry-desc';
            descP.textContent = line;
            entryEl.appendChild(descP);
          });

          sec.appendChild(entryEl);
        });
      });
    });

    if (data.skills.length) {
      addSection('YETENEKLER', function (sec) {
        var p = document.createElement('p');
        p.className = 'resume-preview-text';
        p.textContent = data.skills.join(' · ');
        sec.appendChild(p);
      });
    }

    if (data.languages.length) {
      addSection('DİLLER', function (sec) {
        var p = document.createElement('p');
        p.className = 'resume-preview-text';
        p.textContent = data.languages.join(' · ');
        sec.appendChild(p);
      });
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    var configEl = document.getElementById('tpl-config');
    var form = document.getElementById('doc-form');
    if (!configEl || !form) return;

    var config = JSON.parse(configEl.textContent);
    var isResume = config.kind === 'resume';

    var previewEl = document.getElementById(isResume ? 'resume-preview' : 'preview-clauses');
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
    var updatePreview = function () {}; // assigned below, per mode

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

    // Shared between contract and resume modes: both use data-field for their flat fields.
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

    if (isResume) {
      var groupCounters = {};
      (config.groups || []).forEach(function (g) {
        var list = document.querySelector('.resume-group-list[data-group="' + g.key + '"]');
        groupCounters[g.key] = list ? list.querySelectorAll('.resume-group-card').length : 0;
      });

      function readGroupEntriesFromDom() {
        var result = {};
        (config.groups || []).forEach(function (g) { result[g.key] = []; });
        Array.prototype.slice.call(document.querySelectorAll('.resume-group-list')).forEach(function (list) {
          var groupKey = list.getAttribute('data-group');
          result[groupKey] = Array.prototype.slice.call(list.querySelectorAll('.resume-group-card')).map(function (card) {
            var entry = {};
            Array.prototype.slice.call(card.querySelectorAll('[data-group-field]')).forEach(function (el) {
              entry[el.getAttribute('data-group-field')] = el.value || '';
            });
            return entry;
          });
        });
        return result;
      }

      updatePreview = function () {
        var data = buildResumeData(config, values, readGroupEntriesFromDom());
        renderResumePreview(previewEl, data);
      };

      function groupDef(groupKey) {
        return (config.groups || []).filter(function (g) { return g.key === groupKey; })[0];
      }

      function renumberGroupCards(groupKey) {
        var list = document.querySelector('.resume-group-list[data-group="' + groupKey + '"]');
        var group = groupDef(groupKey);
        if (!list || !group) return;
        Array.prototype.slice.call(list.querySelectorAll('.resume-group-card')).forEach(function (card, i) {
          card.querySelector('.resume-group-card-title').textContent = group.title + ' ' + (i + 1);
        });
      }

      function wireGroupCard(card, groupKey) {
        card.querySelector('.resume-group-remove').addEventListener('click', function () {
          card.remove();
          renumberGroupCards(groupKey);
          updatePreview();
          updateNavState();
        });
        Array.prototype.slice.call(card.querySelectorAll('[data-group-field]')).forEach(function (input) {
          ['input', 'change'].forEach(function (evt) {
            input.addEventListener(evt, function () { updatePreview(); });
          });
        });
      }

      function buildGroupFieldEl(field, name) {
        var wrap = document.createElement('div');
        wrap.className = 'field';
        var label = document.createElement('label');
        label.textContent = field.label;
        wrap.appendChild(label);

        var input;
        if (field.type === 'textarea') {
          input = document.createElement('textarea');
          input.rows = 2;
          if (field.placeholder) input.placeholder = field.placeholder;
        } else if (field.type === 'select') {
          input = document.createElement('select');
          var blank = document.createElement('option');
          blank.value = '';
          blank.textContent = 'Seçiniz';
          input.appendChild(blank);
          (field.options || []).forEach(function (opt) {
            var o = document.createElement('option');
            o.value = opt.value;
            o.textContent = opt.label;
            input.appendChild(o);
          });
        } else {
          input = document.createElement('input');
          input.type = field.type === 'date' ? 'date' : 'text';
          if (field.placeholder) input.placeholder = field.placeholder;
        }
        input.name = name;
        input.setAttribute('data-group-field', field.name);
        if (field.required) input.required = true;
        wrap.appendChild(input);
        return wrap;
      }

      function addGroupEntry(groupKey) {
        var group = groupDef(groupKey);
        var list = document.querySelector('.resume-group-list[data-group="' + groupKey + '"]');
        if (!group || !list) return;

        var idx = groupCounters[groupKey]++;
        var card = document.createElement('div');
        card.className = 'resume-group-card';

        var head = document.createElement('div');
        head.className = 'resume-group-card-head';
        var titleEl = document.createElement('span');
        titleEl.className = 'resume-group-card-title';
        head.appendChild(titleEl);
        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'resume-group-remove';
        removeBtn.textContent = 'Kaldır';
        head.appendChild(removeBtn);
        card.appendChild(head);

        var fieldList = document.createElement('div');
        fieldList.className = 'field-list';
        group.fields.forEach(function (field) {
          fieldList.appendChild(buildGroupFieldEl(field, groupKey + '[' + idx + '][' + field.name + ']'));
        });
        card.appendChild(fieldList);

        list.appendChild(card);
        wireGroupCard(card, groupKey);
        renumberGroupCards(groupKey);
      }

      Array.prototype.slice.call(document.querySelectorAll('.resume-add-btn')).forEach(function (btn) {
        btn.addEventListener('click', function () {
          addGroupEntry(btn.getAttribute('data-group'));
          updatePreview();
          updateNavState();
        });
      });

      Array.prototype.slice.call(document.querySelectorAll('.resume-group-card')).forEach(function (card) {
        var list = card.closest('.resume-group-list');
        if (list) wireGroupCard(card, list.getAttribute('data-group'));
      });
    } else {
      var customClausesList = document.getElementById('custom-clauses-list');
      var addClauseBtn = document.getElementById('add-custom-clause-btn');

      function getCustomClauseTexts() {
        if (!customClausesList) return [];
        return Array.prototype.slice.call(customClausesList.querySelectorAll('.custom-clause-textarea'))
          .map(function (el) { return el.value; })
          .filter(function (v) { return v.trim().length > 0; });
      }

      updatePreview = function (changedField) {
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
      };

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
    }

    updatePreview(null);
    updateNavState();
  });
})();
