export class Migration {
	constructor(options = {}) {
		/** @type {import('./../assets/utils').Utils} */
		this.utils = options.utils;
		/** @type {import('./../assets/state').State} */
		this.state = this.utils.getState();

		this.selector = options.selector;
		this.outputSelector = options.outputSelector;
	}

	init() {
		[...document.querySelectorAll(this.selector)].forEach((element) => {
			element.addEventListener('click', this.onClick, true);
		});
	}

	onClick = (event) => {
		event.preventDefault();

		this.submit(event.target);
	};

	async submit(target) {
		const formId = this.state.getFormIdByElement(target);
		const field = this.state.getFormFieldElementByChild(target);

		const formData = new FormData();

		formData.append('type', field.getAttribute(this.state.getStateAttribute('migrationType')));
		this.utils.showLoader(formId);

		document.querySelector(this.outputSelector).value = 'Please wait, this may take a few minutes...';

		// Populate body data.
		const body = {
			method: 'POST',
			mode: 'same-origin',
			headers: {
				Accept: 'application/json',
				'X-WP-Nonce': this.state.getStateConfigNonce(),
			},
			body: formData,
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		try {
			const response = await fetch(this.state.getRestUrl('migration'), body);
			const parsedResponse = await response.json();

			const {
				message,
				status,
			} = parsedResponse;

			this.utils.hideLoader(formId);
			this.utils.setGlobalMsg(formId, message, status);

			document.querySelector(this.outputSelector).value = JSON.stringify(parsedResponse, null, 4);

			setTimeout(() => {
				this.utils.unsetGlobalMsg(formId);
			}, 6000);

		} catch ({name, message}) {
			if (name === 'AbortError') {
				return;
			}

			throw new Error(this.utils.formSubmitResponseError(formId, 'adminMigration', name, message));
		}
	};
}
