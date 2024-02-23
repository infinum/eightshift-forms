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

	// Handle form submit and all logic.
	onClick = (event) => {
		event.preventDefault();

		const formId = this.state.getFormIdByElement(event.target);
		const field = this.state.getFormFieldElementByChild(event.target);

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
			credentials: 'same-origin',
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		fetch(this.state.getRestUrl('migration'), body)
			.then((response) => {
				this.utils.formSubmitErrorContentType(response, 'migration', formId);
				return response.text();
			})
			.then((responseData) => {
				const response = this.utils.formSubmitIsJsonString(responseData, 'migration', formId);

				const {
					message,
					status,
				} = response;

				this.utils.hideLoader(formId);
				this.utils.setGlobalMsg(formId, message, status);

				document.querySelector(this.outputSelector).value = JSON.stringify(response, null, 4);

				setTimeout(() => {
					this.utils.unsetGlobalMsg(formId);
				}, 6000);
			});
	};
}
