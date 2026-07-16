(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var card = document.querySelector('.auth-card');
    if (!card) return;

    var tabButtons = document.querySelectorAll('[data-tab-btn]');
    var panels = { giris: document.getElementById('panel-giris'), kayit: document.getElementById('panel-kayit') };
    var tabBtns = { giris: document.getElementById('tab-giris'), kayit: document.getElementById('tab-kayit') };

    function currentTab() {
      var hash = window.location.hash.replace('#', '');
      if (hash === 'giris' || hash === 'kayit') return hash;
      return card.getAttribute('data-initial-tab') === 'kayit' ? 'kayit' : 'giris';
    }

    function showTab(tab) {
      Object.keys(panels).forEach(function (key) {
        panels[key].style.display = key === tab ? 'block' : 'none';
        tabBtns[key].classList.toggle('active', key === tab);
      });
    }

    tabButtons.forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var tab = btn.getAttribute('data-tab-btn');
        if (window.location.hash === '#' + tab) {
          showTab(tab);
        } else {
          window.location.hash = tab;
        }
      });
    });

    window.addEventListener('hashchange', function () {
      showTab(currentTab());
    });

    showTab(currentTab());

    var password = document.getElementById('signup_password');
    var confirm = document.getElementById('signup_confirm');
    var mismatchMsg = document.getElementById('signup-mismatch');
    var agree = document.getElementById('signup_agree');
    var fullName = document.getElementById('full_name');
    var signupEmail = document.getElementById('signup_email');
    var submitBtn = document.getElementById('signup-submit');

    function updateSignupState() {
      var mismatch = confirm.value.length > 0 && password.value !== confirm.value;
      mismatchMsg.hidden = !mismatch;
      confirm.style.borderColor = mismatch ? '#dc2626' : '';

      var valid = fullName.value.trim() && signupEmail.value.trim() && password.value.length > 0 && confirm.value.length > 0 && !mismatch && agree.checked;
      submitBtn.disabled = !valid;
    }

    if (password && confirm && submitBtn) {
      [fullName, signupEmail, password, confirm, agree].forEach(function (el) {
        el.addEventListener('input', updateSignupState);
        el.addEventListener('change', updateSignupState);
      });
      updateSignupState();
    }
  });
})();
