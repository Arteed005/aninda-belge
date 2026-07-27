(function () {
  var configEl = document.getElementById('calc-config');
  var resultEl = document.getElementById('calc-result');
  var btn = document.getElementById('calc-btn');
  if (!configEl || !resultEl || !btn) return;

  var config = JSON.parse(configEl.textContent);

  function formatCurrencyTry(n) {
    return '₺' + n.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function parseTLNumber(str) {
    if (!str) return NaN;
    var s = String(str).trim().replace(/[^\d.,]/g, '');
    if (s.includes('.') && s.includes(',')) {
      s = s.replace(/\./g, '').replace(',', '.');
    } else if (s.includes(',')) {
      s = s.replace(',', '.');
    } else if ((s.match(/\./g) || []).length > 1) {
      s = s.replace(/\./g, '');
    }
    return parseFloat(s);
  }

  function parseDateInput(value) {
    if (!value) return null;
    var d = new Date(value + 'T00:00:00');
    return isNaN(d.getTime()) ? null : d;
  }

  function diffDays(start, end) {
    return Math.round((end.getTime() - start.getTime()) / 86400000);
  }

  function fieldValue(name) {
    var el = document.querySelector('[data-field="' + name + '"]');
    return el ? el.value : '';
  }

  function yearsMonthsDaysText(days) {
    var years = Math.floor(days / 365);
    var rem = days % 365;
    var months = Math.floor(rem / 30);
    var restDays = rem % 30;
    var parts = [];
    if (years > 0) parts.push(years + ' yıl');
    if (months > 0) parts.push(months + ' ay');
    if (restDays > 0 || parts.length === 0) parts.push(restDays + ' gün');
    return parts.join(' ');
  }

  function renderResult(rows, highlightRow, label, notes, ctaHtml) {
    var html = '<div class="calc-result-panel">';
    html += '<div class="calc-result-title">' + label + '</div>';
    html += '<table class="calc-result-table">';
    rows.forEach(function (r) {
      html += '<tr><td>' + r[0] + '</td><td>' + r[1] + '</td></tr>';
    });
    html += '<tr class="calc-result-highlight"><td>' + highlightRow[0] + '</td><td>' + highlightRow[1] + '</td></tr>';
    html += '</table>';
    if (notes && notes.length) {
      html += '<div class="calc-result-notes">';
      notes.forEach(function (note) { html += '<p class="calc-result-note">' + note + '</p>'; });
      html += '</div>';
    }
    if (ctaHtml) html += ctaHtml;
    html += '</div>';
    resultEl.innerHTML = html;
  }

  function renderError(message) {
    resultEl.innerHTML = '<div class="calc-result-panel"><div class="calc-result-error">' + message + '</div></div>';
  }

  function calcKidemTazminati() {
    var start = parseDateInput(fieldValue('start_date'));
    var end = parseDateInput(fieldValue('end_date'));
    var salary = parseTLNumber(fieldValue('gross_salary'));
    var reason = fieldValue('separation_reason');

    if (!start || !end) return renderError('Lütfen işe başlama ve ayrılış tarihlerini gir.');
    if (end <= start) return renderError('Ayrılış tarihi, işe başlama tarihinden sonra olmalı.');
    if (!salary || salary <= 0) return renderError('Lütfen geçerli bir brüt maaş tutarı gir.');

    var days = diffDays(start, end);
    var tavan = (config.constants && config.constants.tavan) || Infinity;
    var dailyWage = salary / 30;
    var dailyWageCapped = Math.min(dailyWage, tavan / 30);
    var amount = dailyWageCapped * 30 * (days / 365);

    var notes = [];
    if (days < 365) {
      notes.push('⚠️ Toplam çalışma süren 1 yıldan az (' + yearsMonthsDaysText(days) + '). Kıdem tazminatı hakkı normalde en az 1 yıl çalışmayı gerektirir — aşağıdaki tutar sadece bilgilendirme amaçlıdır.');
    }
    if (reason === 'istifa-siradan') {
      notes.push('⚠️ "Sıradan istifa" seçtin — bu durumda kıdem tazminatı hakkı normalde doğmaz. Tutar sadece bilgilendirme amaçlıdır.');
    }
    if (dailyWage * 30 > tavan) {
      notes.push('Brüt maaşın, kıdem tazminatı tavanının (' + formatCurrencyTry(tavan) + ' — ' + (config.constants.tavanGecerlilik || '') + ') üzerinde olduğu için hesaplama tavan tutarı üzerinden yapıldı.');
    }
    notes.push('Bu hesaplama bilgilendirme amaçlıdır, kesin tutar için bir uzmana danışmanı öneririz.');

    var rows = [
      ['Toplam çalışma süresi', yearsMonthsDaysText(days)],
      ['Günlük brüt ücret (tavan uygulanmış)', formatCurrencyTry(dailyWageCapped)]
    ];
    var highlight = [config.resultLabel || 'Sonuç', formatCurrencyTry(Math.max(amount, 0))];

    var cta = '<a href="sablon.php?slug=' + (reason === 'istifa-siradan' || reason === 'istifa-hakli' ? 'istifa-dilekcesi' : 'fesih-dilekcesi') + '" class="calc-result-cta">' + (reason === 'istifa-siradan' || reason === 'istifa-hakli' ? 'İstifa Dilekçesi Hazırla' : 'Fesih Dilekçesi Hazırla') + ' →</a>';

    renderResult(rows, highlight, 'Hesaplama Sonucu', notes, cta);
  }

  function calcIhbarSuresi() {
    var start = parseDateInput(fieldValue('start_date'));
    var end = parseDateInput(fieldValue('end_date'));
    var salaryRaw = fieldValue('gross_salary');
    var salary = parseTLNumber(salaryRaw);

    if (!start || !end) return renderError('Lütfen işe başlama ve hesaplama tarihlerini gir.');
    if (end <= start) return renderError('Hesaplama tarihi, işe başlama tarihinden sonra olmalı.');

    var days = diffDays(start, end);
    var weeks;
    if (days < 182) weeks = 2;
    else if (days < 547) weeks = 4;
    else if (days < 1095) weeks = 6;
    else weeks = 8;

    var rows = [['Toplam çalışma süresi', yearsMonthsDaysText(days)]];
    var notes = ['Bu hesaplama bilgilendirme amaçlıdır, kesin durum için bir uzmana danışmanı öneririz.'];

    if (salaryRaw && !isNaN(salary) && salary > 0) {
      var amount = (weeks * 7 / 30) * salary;
      rows.push(['Yaklaşık ihbar tazminatı', formatCurrencyTry(amount)]);
    }

    var highlight = [config.resultLabel || 'Sonuç', weeks + ' hafta'];
    var cta = '<a href="sablon.php?slug=fesih-dilekcesi" class="calc-result-cta">Fesih Dilekçesi Hazırla →</a>';
    renderResult(rows, highlight, 'Hesaplama Sonucu', notes, cta);
  }

  function calcYillikIzin() {
    var start = parseDateInput(fieldValue('start_date'));
    var end = parseDateInput(fieldValue('end_date'));
    var specialAge = fieldValue('special_age');

    if (!start || !end) return renderError('Lütfen işe başlama ve hesaplama tarihlerini gir.');
    if (end <= start) return renderError('Hesaplama tarihi, işe başlama tarihinden sonra olmalı.');

    var days = diffDays(start, end);
    var fullYears = Math.floor(days / 365);
    var result;
    var notes = ['Bu hesaplama bilgilendirme amaçlıdır, işyeri uygulaman veya sözleşmen daha lehe bir süre öngörüyor olabilir.'];

    if (fullYears < 1) {
      result = 0;
      notes.unshift('⚠️ Yıllık izin hakkının doğması için en az 1 yıl çalışmış olman gerekir. Toplam çalışma süren: ' + yearsMonthsDaysText(days) + '.');
    } else if (fullYears >= 15) {
      result = 26;
    } else if (fullYears > 5) {
      result = 20;
    } else {
      result = 14;
    }

    if (specialAge === 'evet' && fullYears >= 1 && result < 20) {
      result = 20;
      notes.push('18 yaş altı / 50 yaş üstü çalışanlar için izin süresi en az 20 gün olarak uygulandı.');
    }

    var rows = [['Toplam çalışma süresi', yearsMonthsDaysText(days)]];
    var highlight = [config.resultLabel || 'Sonuç', result + ' gün'];
    var cta = '<a href="sablon.php?slug=izin-talep-dilekcesi" class="calc-result-cta">İzin Talep Dilekçesi Hazırla →</a>';
    renderResult(rows, highlight, 'Hesaplama Sonucu', notes, cta);
  }

  var dispatch = {
    'kidem-tazminati': calcKidemTazminati,
    'ihbar-suresi': calcIhbarSuresi,
    'yillik-izin': calcYillikIzin
  };

  btn.addEventListener('click', function () {
    var fn = dispatch[config.calcType];
    if (fn) fn();
  });
})();
