export class TestApi {
	constructor(options = {}) {
		/** @type {import('./../assets/utils').Utils} */
		this.utils = options.utils;
		/** @type {import('./../assets/state').State} */
		this.state = this.utils.getState();

		this.selector = options.selector;
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
		const integrationType = field.getAttribute(this.state.getStateAttribute('testApiType'));

		const formData = new FormData();

		formData.append('type', integrationType);

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
			const response = await fetch(this.state.getRestUrlByType('prefixTestApi', integrationType), body);
			const parsedResponse = await response.json();

			const {
				message,
				status,
			} = parsedResponse;

			this.utils.hideLoader(formId);
			this.utils.setGlobalMsg(formId, message, status);

			if (this.state.getStateFormElement(formId).getAttribute(this.state.getStateAttribute('reload')) === 'true') {
				setTimeout(() => {
					location.reload();
				}, 1000);
			} else {
				setTimeout(() => {
					this.utils.unsetGlobalMsg(formId);
				}, 6000);
			}
		} catch ({name, message}) {
			if (name === 'AbortError') {
				return;
			}

			throw new Error(this.utils.formSubmitResponseError(formId, 'adminTestApi', name, message));
		}
	};
}
