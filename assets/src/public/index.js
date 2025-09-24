import './index.scss';

(function () {
  'use strict';

  function qs(sel, root) { return (root || document).querySelector(sel); }
  function qsa(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

  function setBusy(grid, busy) { grid.setAttribute('aria-busy', busy ? 'true' : 'false'); }

  function flipHandler(e) {
    const card = e.currentTarget.closest('.wpai-card') || e.currentTarget;
    card.classList.toggle('is-flipped');
    card.setAttribute('aria-pressed', card.classList.contains('is-flipped') ? 'true' : 'false');
  }

  function renderCards(grid, cards, max) {
    grid.innerHTML = '';
    const slice = Array.isArray(cards) ? cards.slice(0, max) : [];

    slice.forEach((c, i) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'wpai-card';
      btn.setAttribute('aria-pressed', 'false');
      btn.setAttribute('aria-label', `Flip flashcard ${i + 1}`);
      btn.addEventListener('click', flipHandler);

      const inner = document.createElement('div');
      inner.className = 'wpai-card__inner';

      const faceFront = document.createElement('div');
      faceFront.className = 'wpai-card__face wpai-card__face--front';
      const chipQ = document.createElement('span');
      chipQ.className = 'wpai-chip';
      chipQ.textContent = 'Q';
      const frontText = document.createElement('div');
      frontText.className = 'wpai-card__text';
      frontText.textContent = c.q || '';

      const faceBack = document.createElement('div');
      faceBack.className = 'wpai-card__face wpai-card__face--back';
      const chipA = document.createElement('span');
      chipA.className = 'wpai-chip';
      chipA.textContent = 'A';
      const backText = document.createElement('div');
      backText.className = 'wpai-card__text';
      backText.textContent = c.a || '';

      faceFront.appendChild(chipQ);
      faceFront.appendChild(frontText);
      faceBack.appendChild(chipA);
      faceBack.appendChild(backText);
      inner.appendChild(faceFront);
      inner.appendChild(faceBack);
      btn.appendChild(inner);
      grid.appendChild(btn);
    });
  }

  function setButtonState(btn, state, textIdle, textBusy) {
    const label = btn.querySelector('.wpai-button__label');
    btn.dataset.state = state;
    if (state === 'busy') {
      btn.classList.add('is-busy');
      label.textContent = textBusy || 'Generating…';
    } else {
      btn.classList.remove('is-busy');
      label.textContent = textIdle || 'Generate';
    }
  }

  function init() {
    const form = qs('.wpai-form');
    const textarea = qs('#wpai-text', form);
    const grid = qs('.wpai-grid');
    const btn = qs('.wpai-button', form);
    const status = qs('.wpai-status', form);
    if (!form || !textarea || !grid || !btn) { return; }

    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      const text = textarea.value.trim();
      if (!text) { textarea.focus(); return; }

      const max = parseInt(grid.getAttribute('data-max') || '5', 10);
      setButtonState(btn, 'busy', (WPAI_PUBLIC && WPAI_PUBLIC.i18n && WPAI_PUBLIC.i18n.generate), (WPAI_PUBLIC && WPAI_PUBLIC.i18n && WPAI_PUBLIC.i18n.generating));
      setBusy(grid, true);
      status.textContent = '';

      try {
        const res = await fetch((WPAI_PUBLIC.root || '') + (WPAI_PUBLIC.ns ? WPAI_PUBLIC.ns : 'askary-ai/v1') + '/flashcards', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': WPAI_PUBLIC.nonce || ''
          },
          body: JSON.stringify({ text })
        });

        const data = await res.json();
        if (!res.ok || !data || !data.ok || !Array.isArray(data.cards)) {
          throw new Error((data && data.error) || 'Provider error');
        }

        renderCards(grid, data.cards, max);
        status.textContent = '';
      } catch (err) {
        status.textContent = (WPAI_PUBLIC && WPAI_PUBLIC.i18n && WPAI_PUBLIC.i18n.error) || 'Something went wrong. Please try again.';
        console.error(err);
      } finally {
        setButtonState(btn, 'idle', (WPAI_PUBLIC && WPAI_PUBLIC.i18n && WPAI_PUBLIC.i18n.generate));
        setBusy(grid, false);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
