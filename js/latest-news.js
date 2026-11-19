(() => {
  const grid = document.querySelector('[data-latest-news]');
  if (!grid) return;

  fetch('jaunumi/?ajax=1&page=1', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then((response) => (response.ok ? response.json() : null))
    .then((data) => {
      if (!data || typeof data.html !== 'string') return;
      const holder = document.createElement('div');
      holder.innerHTML = data.html;
      const cards = Array.prototype.slice.call(holder.querySelectorAll('.news-card'), 0, 3);
      if (!cards.length) return;
      cards.forEach((card) => {
        card.querySelectorAll('a[href^="../"]').forEach((link) => {
          link.setAttribute('href', link.getAttribute('href').slice(3));
        });
      });
      grid.replaceChildren.apply(grid, cards);
    })
    .catch(() => {});
})();
