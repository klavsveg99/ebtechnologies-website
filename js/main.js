(() => {
  'use strict';

  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

  /* Mobile navigation */
  const menuToggle = $('#menu-toggle');
  const siteNav = $('#site-nav');

  if (menuToggle && siteNav) {
    const setOpen = (open) => {
      menuToggle.setAttribute('aria-expanded', String(open));
      menuToggle.textContent = open ? 'Aizvērt' : 'Izvēlne';
      siteNav.classList.toggle('is-open', open);
    };

    menuToggle.addEventListener('click', () => {
      setOpen(menuToggle.getAttribute('aria-expanded') !== 'true');
    });

    document.addEventListener('keydown', (event) => {
      if (event.key !== 'Escape' || menuToggle.getAttribute('aria-expanded') !== 'true') return;
      setOpen(false);
      menuToggle.focus();
    });

    document.addEventListener('click', (event) => {
      if (menuToggle.getAttribute('aria-expanded') !== 'true') return;
      if (siteNav.contains(event.target) || menuToggle.contains(event.target)) return;
      setOpen(false);
    });

    siteNav.addEventListener('click', (event) => {
      if (event.target.closest('a')) setOpen(false);
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth > 860) setOpen(false);
    });
  }

  /* Cookie consent */
  const CONSENT_KEY = 'eb-cookie-consent';
  const banner = $('#cookie-banner');
  const cookieDialog = $('#cookie-dialog');
  const analyticsToggle = $('#cookie-analytics');
  const marketingToggle = $('#cookie-marketing');

  const readConsent = () => {
    try {
      return JSON.parse(localStorage.getItem(CONSENT_KEY) || 'null');
    } catch {
      return null;
    }
  };

  const syncToggles = () => {
    const consent = readConsent();
    if (analyticsToggle) analyticsToggle.checked = Boolean(consent && consent.analytics);
    if (marketingToggle) marketingToggle.checked = Boolean(consent && consent.marketing);
  };

  const saveConsent = (analytics, marketing) => {
    const consent = {
      necessary: true,
      analytics: Boolean(analytics),
      marketing: Boolean(marketing),
      updated: new Date().toISOString()
    };
    try {
      localStorage.setItem(CONSENT_KEY, JSON.stringify(consent));
    } catch {
      /* Storage can be blocked; the choice still applies to this page view. */
    }
    if (banner) banner.hidden = true;
    if (cookieDialog && cookieDialog.open) cookieDialog.close();
  };

  const openCookieDialog = () => {
    if (!cookieDialog) return;
    syncToggles();
    if (typeof cookieDialog.showModal === 'function') {
      cookieDialog.showModal();
    } else {
      cookieDialog.setAttribute('open', '');
    }
  };

  if (banner) {
    banner.hidden = readConsent() !== null;
  }

  const acceptAll = $('#cookie-accept');
  const necessaryOnly = $('#cookie-necessary');
  const openSettings = $('#cookie-open-settings');
  const settingsFooter = $('#cookie-settings-footer');
  const settingsPolicy = $('#cookie-settings-policy');
  const saveSettings = $('#cookie-save');
  const acceptAllDialog = $('#cookie-accept-dialog');
  const closeDialog = $('#cookie-close');

  if (acceptAll) acceptAll.addEventListener('click', () => saveConsent(true, true));
  if (necessaryOnly) necessaryOnly.addEventListener('click', () => saveConsent(false, false));
  if (openSettings) openSettings.addEventListener('click', openCookieDialog);
  if (settingsFooter) settingsFooter.addEventListener('click', openCookieDialog);
  if (settingsPolicy) settingsPolicy.addEventListener('click', openCookieDialog);
  if (closeDialog && cookieDialog) closeDialog.addEventListener('click', () => cookieDialog.close());
  if (saveSettings) {
    saveSettings.addEventListener('click', () => {
      saveConsent(analyticsToggle && analyticsToggle.checked, marketingToggle && marketingToggle.checked);
    });
  }
  if (acceptAllDialog) acceptAllDialog.addEventListener('click', () => saveConsent(true, true));

  if (cookieDialog) {
    cookieDialog.addEventListener('click', (event) => {
      if (event.target === cookieDialog) cookieDialog.close();
    });
  }

  /* Copy account number on the contact page */
  const copyIban = $('#copy-iban');
  if (copyIban) {
    const status = $('#copy-iban-status');
    copyIban.addEventListener('click', async () => {
      const value = copyIban.getAttribute('data-copy') || '';
      const announce = (message) => {
        if (status) status.textContent = message;
        copyIban.textContent = message;
        setTimeout(() => {
          copyIban.textContent = 'Kopēt';
        }, 2500);
      };
      try {
        await navigator.clipboard.writeText(value);
        announce('Nokopēts');
      } catch {
        const field = document.createElement('textarea');
        field.value = value;
        field.setAttribute('readonly', '');
        field.style.position = 'fixed';
        field.style.left = '-9999px';
        document.body.append(field);
        field.select();
        try {
          document.execCommand('copy');
          announce('Nokopēts');
        } catch {
          announce('Neizdevās');
        } finally {
          field.remove();
        }
      }
    });
  }

  /* Contact forms */
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  const fieldMessage = (field) => {
    if (field.type === 'checkbox') {
      return field.required && !field.checked ? 'Lūdzu, apstipriniet datu apstrādi.' : '';
    }
    const value = field.value.trim();
    if (field.required && !value) return 'Šis lauks ir obligāts.';
    if (field.type === 'email' && value && !emailPattern.test(value)) return 'Ievadiet derīgu e-pasta adresi.';
    if (field.type === 'tel' && value && !/^[+\d][\d\s()-]{6,}$/.test(value)) {
      return 'Ievadiet derīgu tālruņa numuru.';
    }
    return '';
  };

  const errorFor = (field) => {
    const formId = field.form ? field.form.id || 'form' : 'form';
    const id = `${formId}-${field.name}-error`;
    let error = document.getElementById(id);
    if (!error) {
      error = document.createElement('p');
      error.className = 'field-error';
      error.id = id;
      error.hidden = true;
      const wrapper = field.closest('.field');
      (wrapper || field).append(error);
      field.setAttribute('aria-describedby', id);
    }
    return error;
  };

  const validateField = (field) => {
    const message = fieldMessage(field);
    const error = errorFor(field);
    error.textContent = message;
    error.hidden = !message;
    field.setAttribute('aria-invalid', message ? 'true' : 'false');
    return !message;
  };

  $$('form[data-contact-form]').forEach((form) => {
    const status = $('.form-status', form);
    const submit = $('button[type="submit"]', form);
    const fields = $$('input:not([type="hidden"]), select, textarea', form).filter(
      (field) => field.name !== 'website'
    );

    fields.forEach((field) => {
      field.addEventListener('blur', () => {
        if (field.value || field.required) validateField(field);
      });
      field.addEventListener('change', () => validateField(field));
    });

    const setStatus = (message, isError) => {
      if (!status) return;
      status.textContent = message;
      status.hidden = false;
      status.classList.toggle('form-status--error', Boolean(isError));
    };

    form.addEventListener('submit', async (event) => {
      event.preventDefault();

      /* Honeypot: a filled hidden field means a bot. Show success and stop. */
      const honeypot = $('input[name="website"]', form);
      if (honeypot && honeypot.value) {
        form.reset();
        setStatus('Paldies! Jūsu ziņa ir nosūtīta.', false);
        return;
      }

      const invalid = fields.map(validateField).filter((valid) => !valid).length > 0;
      if (invalid) {
        const firstInvalid = fields.find((field) => field.getAttribute('aria-invalid') === 'true');
        if (firstInvalid) firstInvalid.focus();
        setStatus('Lūdzu, pārbaudiet atzīmētos laukus.', true);
        return;
      }

      const originalLabel = submit ? submit.textContent : '';
      if (submit) {
        submit.disabled = true;
        submit.textContent = 'Nosūta...';
      }
      if (status) status.hidden = true;

      try {
        const response = await fetch(form.getAttribute('action') || 'contact.php', {
          method: 'POST',
          body: new FormData(form),
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const payload = await response.json().catch(() => null);
        if (!response.ok || !payload || payload.success !== true) {
          throw new Error((payload && payload.message) || 'Neizdevās nosūtīt ziņu.');
        }
        form.reset();
        fields.forEach((field) => {
          field.removeAttribute('aria-invalid');
          const error = document.getElementById(`${form.id || 'form'}-${field.name}-error`);
          if (error) error.hidden = true;
        });
        setStatus(payload.message || 'Paldies! Jūsu ziņa ir nosūtīta.', false);
      } catch (error) {
        const fallback =
          'Neizdevās nosūtīt ziņu. Lūdzu, rakstiet uz info@ebtechnologies.lv vai zvaniet +371 29 179 536.';
        setStatus(error && error.message !== 'Failed to fetch' ? error.message : fallback, true);
      } finally {
        if (submit) {
          submit.disabled = false;
          submit.textContent = originalLabel;
        }
      }
    });
  });
})();
