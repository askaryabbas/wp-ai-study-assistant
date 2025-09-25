/**
 * Public-facing script for WP AI Study Assistant.
 *
 * Handles the `[wpai_flashcards_3d]` shortcode UI:
 * - Submits text to the REST API to generate Q/A pairs
 * - Renders interactive 3D flip cards
 * - Manages busy/idle states and a11y attributes
 *
 * Globals:
 * - WPAI_PUBLIC (localized in PHP): { ns, nonce, root, i18n: { generate, generating, error } }
 *
 * @package
 * @since 1.0.0
 */

/* global WPAI_PUBLIC */

import './index.scss';

(function () {
	'use strict';

	// ===== Helpers =====

	/**
	 * Query a single element.
	 * @param {string}           sel    CSS selector.
	 * @param {Element|Document} [root] Root element.
	 * @return {Element|null} Found element or null.
	 */
	function qs(sel, root) {
		return (root || document).querySelector(sel);
	}

	/**
	 * Toggle aria-busy state for a grid.
	 * @param {HTMLElement} grid Grid element.
	 * @param {boolean}     busy Busy flag.
	 * @return {void}
	 */
	function setBusy(grid, busy) {
		grid.setAttribute('aria-busy', busy ? 'true' : 'false');
	}

	/**
	 * Handle flip interaction for a card.
	 * @param {MouseEvent} e Click event.
	 * @return {void}
	 */
	function flipHandler(e) {
		const card = e.currentTarget.closest('.wpai-card') || e.currentTarget;
		card.classList.toggle('is-flipped');
		card.setAttribute(
			'aria-pressed',
			card.classList.contains('is-flipped') ? 'true' : 'false'
		);
	}

	/**
	 * Render Q/A cards into the grid.
	 * @param {HTMLElement}                grid  Container element.
	 * @param {Array<{q:string,a:string}>} cards Q/A pairs.
	 * @param {number}                     max   Maximum number of cards to render.
	 * @return {void}
	 */
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

	/**
	 * Update button visual + label state.
	 * @param {HTMLButtonElement} btn        Button element.
	 * @param {'idle'|'busy'}     state      Desired state.
	 * @param {string}            textIdle   Idle label.
	 * @param {string}            [textBusy] Busy label (optional).
	 * @return {void}
	 */
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

	// ===== Init =====

	/**
	 * Initialize the shortcode UI: submit handler + REST call.
	 * @return {void}
	 */
	function init() {
		const form = qs('.wpai-form');
		if (!form) {
			return;
		}

		const textarea = qs('#wpai-text', form);
		const grid = qs('.wpai-grid');
		const btn = qs('.wpai-button', form);

		if (!textarea || !grid || !btn) {
			return;
		}

		const status = qs('.wpai-status', form);

		form.addEventListener('submit', async (e) => {
			e.preventDefault();

			const text = textarea.value.trim();
			if (!text) {
				textarea.focus();
				return;
			}

			const max = parseInt(grid.getAttribute('data-max') || '5', 10);
			setButtonState(
				btn,
				'busy',
				WPAI_PUBLIC?.i18n?.generate,
				WPAI_PUBLIC?.i18n?.generating
			);
			setBusy(grid, true);
			status.textContent = '';

			try {
				const ns = WPAI_PUBLIC?.ns || 'askary-ai/v1';
				const root = WPAI_PUBLIC?.root || '';
				const res = await fetch(`${root}${ns}/flashcards`, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': WPAI_PUBLIC?.nonce || '',
					},
					body: JSON.stringify({ text }),
				});

				const data = await res.json();
				if (!res.ok || !data?.ok || !Array.isArray(data.cards)) {
					throw new Error(data?.error || 'Provider error');
				}

				renderCards(grid, data.cards, max);
				status.textContent = '';
			} catch (err) {
				status.textContent =
					WPAI_PUBLIC?.i18n?.error ||
					'Something went wrong. Please try again.';
				// eslint-disable-next-line no-console
				console.error(err);
			} finally {
				setButtonState(btn, 'idle', WPAI_PUBLIC?.i18n?.generate);
				setBusy(grid, false);
			}
		});
	}

	// DOM ready.
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
