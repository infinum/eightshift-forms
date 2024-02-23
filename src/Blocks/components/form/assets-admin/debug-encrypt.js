export class DebugEncrypt {
	constructor(options = {}) {
		/** @type {import('../assets/utils').Utils} */
		this.utils = options.utils;
		/** @type {import('../assets/state').State} */
		this.state = this.utils.getState();

		this.selector = options.selector;
		this.outputSelector = options.outputSelector;
		this.typeSelector = options.typeSelector;
		this.dataSelector = options.dataSelector;
	}

	init() {
		document.querySelector(this.selector).addEventListener('click', this.onClick, true);
	}

	// Handle form submit and all logic.
	onClick = (event) => {
		event.preventDefault();

		const formId = this.state.getFormIdByElement(event.target);

		const formData = new FormData();

		formData.append('type', this.state.getStateElementValue('debug-encrypt-type', formId));
		formData.append('data', this.state.getStateElementValue('debug-encrypt-data', formId));

		this.utils.showLoader(formId);

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

		fetch(this.state.getRestUrl('debugEncrypt'), body)
			.then((response) => {
				this.utils.formSubmitErrorContentType(response, 'debugEncrypt', formId);
				return response.text();
			})
			.then((responseData) => {
				const response = this.utils.formSubmitIsJsonString(responseData, 'debugEncrypt', formId);

				const {
					message,
					data,
					status,
				} = response;

				this.utils.hideLoader(formId);
				this.utils.setGlobalMsg(formId, message, status);

				if (data?.output) {
					document.querySelector(this.outputSelector).value = data?.output;
				}

				setTimeout(() => {
					this.utils.unsetGlobalMsg(formId);
				}, 6000);
		});
	};
}
