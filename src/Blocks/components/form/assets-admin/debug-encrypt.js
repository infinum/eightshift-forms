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

		this.submit(event.target);
	};

	async submit(target) {
		const formId = this.state.getFormIdByElement(target);

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
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		try {
			const response = await fetch(this.state.getRestUrl('debugEncrypt'), body);
			const parsedResponse = await response.json();

			const {
				message,
				data,
				status,
			} = parsedResponse;

			this.utils.hideLoader(formId);
			this.utils.setGlobalMsg(formId, message, status);

			const encryptValue = data?.[this.state.getStateResponseOutputKey('adminEncrypt')];

			if (encryptValue) {
				document.querySelector(this.outputSelector).value = encryptValue;
			}

			setTimeout(() => {
				this.utils.unsetGlobalMsg(formId);
			}, 6000);

		} catch ({name, message}) {
			if (name === 'AbortError') {
				return;
			}

			throw new Error(this.utils.formSubmitResponseError(formId, 'adminDebugEncrypt', name, message));
		}
	};
}
