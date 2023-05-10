/* global esFormsLocalization */

import { Utils } from "../assets/utilities";

export class TestApi {
	constructor(options) {
		/** @type Utils */
		this.utils = options.utils ?? new Utils();

		this.selector = options.selector;

		this.testApiRestUrl = options.testApiRestUrl;
	}

	init() {
		const elements = document.querySelectorAll(this.selector);

		console.log(elements);
		[...elements].forEach((element) => {
			element.addEventListener('click', this.onClick, true);
		});
	}

	// Handle form submit and all logic.
	onClick = (event) => {
		event.preventDefault();

		const element = event.target;

		const formData = new FormData();

		const integrationType = element.getAttribute('data-type');

		formData.append('type', integrationType);

		// Populate body data.
		const body = {
			method: 'POST',
			mode: 'same-origin',
			headers: {
				Accept: 'application/json',
				'X-WP-Nonce': esFormsLocalization.nonce,
			},
			body: formData,
			credentials: 'same-origin',
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		fetch(`${this.testApiRestUrl}-${integrationType}`, body)
			.then((response) => {
				return response.json();
			})
			.then((response) => {

				console.log();
				const formElement = element.closest(this.utils.formSelector);
				this.utils.setGlobalMsg(formElement, response.message, response.status);

				if (element.getAttribute('data-reload') === 'true') {
					setTimeout(() => {
						location.reload();
					}, 1000);
				} else {
					setTimeout(() => {
						this.utils.hideGlobalMsg(formElement);
					}, 6000);
				}
			});
	};
}
