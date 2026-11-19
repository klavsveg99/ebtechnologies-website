(() => {
  const list = document.getElementById('news-list');
  const pager = document.getElementById('news-pagination');
  const status = document.getElementById('news-status');
  if (!list || !pager) return;

  const section = list.closest('.section');
  let controller = null;

  const setStatus = (message, isError) => {
    if (!status) return;
    status.textContent = message;
    status.classList.toggle('visually-hidden', !isError);
    status.classList.toggle('form-status', isError);
  };

  const load = async (fetchUrl, historyUrl, moveFocus) => {
    if (controller) controller.abort();
    controller = new AbortController();
    pager.setAttribute('aria-busy', 'true');
    setStatus('Ielādē...');

    try {
      const response = await fetch(fetchUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        signal: controller.signal
      });
      if (!response.ok) throw new Error('HTTP ' + response.status);
      const data = await response.json();
      list.innerHTML = data.html;
      pager.innerHTML = data.pagination;
      list.dataset.page = data.page;
      setStatus('Lapa ' + data.page + ' no ' + data.pages);
      if (historyUrl !== null) history.pushState({ page: data.page }, '', historyUrl);
      if (section) {
        const top = section.getBoundingClientRect().top + window.scrollY - 24;
        window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
      }
      if (moveFocus) {
        const heading = list.querySelector('h3 a');
        if (heading) heading.focus({ preventScroll: true });
      }
    } catch (error) {
      if (error.name === 'AbortError') return;
      setStatus('Neizdevās ielādēt rakstus. Pārlādējiet lapu.', true);
    } finally {
      pager.removeAttribute('aria-busy');
    }
  };

  const fetchFor = (url) => {
    const target = new URL(url, location.href);
    target.searchParams.set('ajax', '1');
    return target.pathname + target.search;
  };

  pager.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (!link) return;
    event.preventDefault();
    load(fetchFor(link.href), link.href, true);
  });

  window.addEventListener('popstate', () => {
    load(fetchFor(location.href), null, false);
  });
})();
