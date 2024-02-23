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

	// Handle form submit and all logic.
	onClick = (event) => {
		event.preventDefault();

		const formId = this.state.getFormIdByElement(event.target);
		const field = this.state.getFormFieldElementByChild(event.target);
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
			credentials: 'same-origin',
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		fetch(this.state.getRestUrlByType('prefixTestApi', integrationType), body)
			.then((response) => {
				this.utils.formSubmitErrorContentType(response, 'testApi', formId);
				return response.text();
			})
			.then((responseData) => {
				const response = this.utils.formSubmitIsJsonString(responseData, 'testApi', formId);

				const {
					message,
					status,
				} = response;

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
			});
	};
}
