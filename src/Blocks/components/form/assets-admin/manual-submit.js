export class ManualSubmit {
	constructor(options = {}) {
		/** @type {import('../assets/utils').Utils} */
		this.utils = options.utils;
		/** @type {import('../assets/state').State} */
		this.state = this.utils.getState();

		this.GLOBAL_MSG_TIMEOUT_ID = undefined;
		this.FORM_ID = 0;

		this.triggerSelector = options.triggerSelector;
		this.dataSelector = options.dataSelector;

		this.FORM_TYPE = '';
		this.importErrorMsg = options.importErrorMsg;
	}

	init () {
		[...document.querySelectorAll(this.triggerSelector)].forEach((element) => {
			element.addEventListener('click', this.onClick, true);
		});
	}

	onClick = (event) => {
		event.preventDefault();

		this.submit(event.target);
	};

	async submit(target) {
		const id = target.getAttribute(this.state.getStateAttribute('manualSubmitId'));
		const formId = target.getAttribute(this.state.getStateAttribute('formId'));
		const data = document.querySelector(`${this.dataSelector}[${this.state.getStateAttribute('manualSubmitId')}="${id}"]`)?.innerHTML;

		this.utils.showLoader(this.FORM_ID);

		if (!id || !data) {
			this.utils.hideLoader(this.FORM_ID);
			this.utils.setGlobalMsg(this.FORM_ID, this.importErrorMsg, 'error');

			return;
		}

		// Populate body data.
		const body = {
			method: 'POST',
			mode: 'same-origin',
			headers: {
				Accept: 'multipart/form-data',
				'X-WP-Nonce': this.state.getStateConfigNonce(),
			},
			body: this.getIntegrationData(data),
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		if (!this.FORM_TYPE) {
			this.utils.hideLoader(this.FORM_ID);
			this.utils.setGlobalMsg(this.FORM_ID, this.importErrorMsg, 'error');

			return;
		}

		try {
			const response = await fetch(this.state.getRestUrlByType('prefixSubmit', this.FORM_TYPE), body);
			const parsedResponse = await response.json();

			const {
				message,
				status,
			} = parsedResponse;

			this.utils.hideLoader(this.FORM_ID);
			this.utils.setGlobalMsg(this.FORM_ID, message, status);

			setTimeout(() => {
				this.utils.unsetGlobalMsg(this.FORM_ID);
			}, 6000);
		} catch ({name, message}) {
			if (name === 'AbortError') {
				return;
			}

			throw new Error(this.utils.formSubmitResponseError(formId, 'adminManualSubmit', name, message));
		}
	};

	/**
	 * Get integration data.
	 *
	 * @param {string} data Data to use.
	 *
	 * @returns {FormData}
	 */
	getIntegrationData(data) {
		const output = [];

		if (!data) {
			return output;
		}

		let params = {};

		// Check if we can parse data.
		try {
			params = JSON.parse(data)?.params;
		} catch {
			return output;
		}

		const formData = new FormData();

		for(const [name, value] of Object.entries(params)) {
			formData.append(name, JSON.stringify(value));

			if (name === this.state.getStateParam('type')) {
				this.FORM_TYPE = value?.value;
			}
		}

		return formData;
	}
}
