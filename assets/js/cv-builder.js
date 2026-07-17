(function () {
  'use strict';

  // Mirrors lib/ResumeRenderer.php's renderResumeData() shape, plus a local
  // (never-uploaded) photo preview so the picker feels instant.
  function buildResumeData(config, values, groupEntries, photoDataUrl) {
    function get(name) { return (values[name] || '').toString().trim(); }
    function splitList(csv) {
      return csv.split(',').map(function (s) { return s.trim(); }).filter(function (s) { return s !== ''; });
    }

    var sections = (config.groups || []).map(function (group) {
      return { title: group.title, fields: group.fields, entries: groupEntries[group.key] || [] };
    });

    return {
      photo: photoDataUrl || null,
      full_name: get('full_name'),
      title: get('title'),
      email: get('email'),
      phone: get('phone'),
      location: get('location'),
      linkedin: get('linkedin'),
      summary: get('summary'),
      skills: splitList(get('skills')),
      languages: splitList(get('languages')),
      hobbies: splitList(get('hobbies')),
      sections: sections
    };
  }

  function renderCvPreview(container, data) {
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
    if (data.photo) {
      var photoEl = document.createElement('img');
      photoEl.className = 'resume-preview-photo';
      photoEl.src = data.photo;
      photoEl.alt = '';
      header.appendChild(photoEl);
    }
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
      addSection('Hakkımda', function (sec) {
        var p = document.createElement('p');
        p.className = 'resume-preview-text';
        p.textContent = data.summary;
        sec.appendChild(p);
      });
    }

    data.sections.forEach(function (section) {
      if (!section.entries.length) return;
      var fieldNames = section.fields.map(function (f) { return f.name; });
      addSection(section.title, function (sec) {
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
          if (dateRange) {
            var datesEl = document.createElement('span');
            datesEl.className = 'resume-preview-entry-dates';
            datesEl.textContent = dateRange;
            row.appendChild(datesEl);
          }
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

    function addTagSection(titleText, items) {
      if (!items.length) return;
      addSection(titleText, function (sec) {
        var p = document.createElement('p');
        p.className = 'resume-preview-tags';
        items.forEach(function (item) {
          var span = document.createElement('span');
          span.className = 'tag-pill';
          span.textContent = item;
          p.appendChild(span);
        });
        sec.appendChild(p);
      });
    }

    addTagSection('Yetenekler', data.skills);
    addTagSection('Diller', data.languages);
    addTagSection('Hobiler', data.hobbies);
  }

  document.addEventListener('DOMContentLoaded', function () {
    var configEl = document.getElementById('tpl-config');
    var form = document.getElementById('doc-form');
    var previewEl = document.getElementById('resume-preview');
    if (!configEl || !form || !previewEl) return;

    var config = JSON.parse(configEl.textContent);

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
    var photoDataUrl = null;
    var currentStep = 0;
    var maxReachedStep = 0;

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

    function updatePreview() {
      var data = buildResumeData(config, values, readGroupEntriesFromDom(), photoDataUrl);
      renderCvPreview(previewEl, data);
    }

    // Shared flat fields (text/textarea) — photo (file input) is wired separately below.
    Array.prototype.slice.call(form.querySelectorAll('[data-field]')).forEach(function (input) {
      if (input.type === 'file') return;
      var name = input.getAttribute('data-field');
      values[name] = input.value || '';
      ['input', 'change'].forEach(function (evt) {
        input.addEventListener(evt, function () {
          values[name] = input.value;
          updatePreview();
          updateNavState();
        });
      });
    });

    var photoInput = form.querySelector('[data-photo-input]');
    if (photoInput) {
      photoInput.addEventListener('change', function () {
        var file = photoInput.files && photoInput.files[0];
        if (!file) { photoDataUrl = null; updatePreview(); return; }
        var reader = new FileReader();
        reader.onload = function (e) {
          photoDataUrl = e.target.result;
          updatePreview();
        };
        reader.readAsDataURL(file);
      });
    }

    var groupCounters = {};
    (config.groups || []).forEach(function (g) {
      var list = document.querySelector('.resume-group-list[data-group="' + g.key + '"]');
      groupCounters[g.key] = list ? list.querySelectorAll('.resume-group-card').length : 0;
    });

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

    updatePreview();
    updateNavState();
  });
})();
