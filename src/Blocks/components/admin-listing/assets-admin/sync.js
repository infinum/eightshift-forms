/* global esFormsLocalization */

import { Utils } from './../../form/assets/utilities';

export class Sync {
	constructor(options) {
		/** @type Utils */
		this.utils = options.utils ?? new Utils();

		this.selector = options.selector;

		this.syncRestUrl = options.syncRestUrl;
	}

	init() {
		const elements = document.querySelectorAll(this.selector);

		[...elements].forEach((element) => {
			element.addEventListener('click', this.onClick, true);
		});
	}

	// Handle form submit and all logic.
	onClick = (event) => {
		event.preventDefault();

		const element = event.target;

		const formData = new FormData();

		formData.append('id', element.getAttribute('data-id'));

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

		fetch(this.syncRestUrl, body)
			.then((response) => {
				return response.json();
			})
			.then((response) => {
				this.utils.setGlobalMsg(document, response.message, response.status);
				if (response.status === 'success') {
					setTimeout(() => {
						location.reload();
					}, 1000);
				}

				setTimeout(() => {
					this.utils.hideGlobalMsg(document);
				}, 6000);
			});
	};
}
