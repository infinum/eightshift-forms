import { setStateWindow, prefix } from './state-init';

/**
 * PaymentGateways class.
 */
export class PaymentGateways {
	constructor({ utils, state, response }) {
		/** @type {import('./utils').Utils} */
		this.utils = utils;
		/** @type {import('./state').State} */
		this.state = state;

		this.response = response;

		// Set all public methods.
		this.publicMethods();
	}

	/**
	 * Initialize the payment gateway.
	 * @returns {void}
	 */
	init(formId) {
		const { type, url, params } = this.response;

		this.utils.showLoader(formId);

		if (type === 'SUBMIT') {
			this.initFormActionSubmit(url, params);
		}

		if (type === 'POST') {
			this.initFormSubmitBuilder(url, params);
		}

		if (type === 'GET') {
			this.initUrlRedirect(url);
		}
	}

	/**
	 * Submit the form action.
	 *
	 * @returns {void}
	 */
	initFormActionSubmit() {
		this.state.getStateFormElement().submit();
	}

	/**
	 * Redirect to the given URL.
	 * @param {string} url
	 * @returns {void}
	 */
	initUrlRedirect(url) {
		window.location.href = url;
	}

	/**
	 * Submit a form with the given URL and parameters.
	 * @param {string} url
	 * @param {Record<string, string>} params
	 * @returns {void}
	 */
	initFormSubmitBuilder(url, params) {
		// Create a form element.
		const form = document.createElement('form');
		form.method = 'POST';
		form.action = url;

		// Populate hidden fields with parameters
		Object.entries(params).forEach(([key, value]) => {
			const hiddenField = document.createElement('input');
			hiddenField.type = 'hidden';
			hiddenField.name = key;
			hiddenField.value = String(value);
			form.appendChild(hiddenField);
		});

		// Append form to body, submit it, then remove it
		document.body.appendChild(form);
		form.submit();
		document.body.removeChild(form);
	}

	////////////////////////////////////////////////////////////////
	// Private methods - not shared to the public window object.
	////////////////////////////////////////////////////////////////

	/**
	 * Set all public methods.
	 *
	 * @returns {void}
	 */
	publicMethods() {
		setStateWindow();

		if (window[prefix].paymentGateways) {
			return;
		}

		window[prefix].paymentGateways = {
			init: () => {
				this.init();
			},
			initFormActionSubmit: () => {
				this.initFormActionSubmit();
			},
			initUrlRedirect: (url) => {
				this.initUrlRedirect(url);
			},
			initFormSubmitBuilder: (url, params) => {
				this.initFormSubmitBuilder(url, params);
			},
		};
	}
}
