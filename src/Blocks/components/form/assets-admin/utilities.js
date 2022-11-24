/**
 * All form custom state selectors.
 */
 export const FORM_SELECTORS = {
	CLASS_ACTIVE: 'is-active',
};

/**
 * Set global Msg
 *
 * @param {string} selector Selector to find.
 * @param {string} msg Msg to show.
 * @param {string} status Status to show.
 *
 * @returns void
 */
export const setGlobalMsg = (selector, msg, status) => {
	const messageContainer = document.querySelector(selector);

	if (!messageContainer) {
		return;
	}

	messageContainer.classList.add(FORM_SELECTORS.CLASS_ACTIVE);
	messageContainer.dataset.status = status;
	messageContainer.innerHTML = `<span>${msg}</span>`;
};

/**
 * Hide global Msg
 *
 * @param {string} selector Selector to find.
 *
 * @returns void
 */
export const hideGlobalMsg = (selector) => {
	const messageContainer = document.querySelector(selector);

	if (!messageContainer) {
		return;
	}

	messageContainer.classList.remove(FORM_SELECTORS.CLASS_ACTIVE);
};
