export class Increment {
	constructor(options = {}) {
		/** @type {import('../assets/utils').Utils} */
		this.utils = options.utils;
		/** @type {import('../assets/state').State} */
		this.state = this.utils.getState();

		this.selector = options.selector;
		this.confirmMsg = options.confirmMsg;
	}

	init() {
		document.querySelectorAll(this.selector).forEach((element) => element.addEventListener('click', this.onClick, true));
	}

	// Handle form submit and all logic.
	onClick = (event) => {
		event.preventDefault();

		if (!confirm(this.confirmMsg)) {
			return;
		}

		const formId = this.state.getFormIdByElement(event.target);
		const field = this.state.getFormFieldElementByChild(event.target);

		const formData = new FormData();

		formData.append('formId', field.getAttribute(this.state.getStateAttribute('formId')));
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

		fetch(this.state.getRestUrl('increment'), body)
			.then((response) => {
				this.utils.formSubmitErrorContentType(response, 'increment', formId);

				return response.text();
			})
			.then((responseData) => {
				const response = this.utils.formSubmitIsJsonString(responseData, 'increment', formId);

				const { message, status } = response;

				this.utils.hideLoader(formId);
				this.utils.setGlobalMsg(formId, message, status);

				setTimeout(() => {
					location.reload();
				}, 1000);
			});
	};
}
