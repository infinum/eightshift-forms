export class Locations {
	constructor(options = {}) {
		/** @type {import('./../assets/utils').Utils} */
		this.utils = options.utils;
		/** @type {import('./../assets/state').State} */
		this.state = this.utils.getState();

		this.selector = options.selector;
		this.itemSelector = options.itemSelector;
		this.FORM_ID = 0;
	}

	init() {
		[...document.querySelectorAll(this.selector)].forEach((element) => {
			element.addEventListener('click', this.onClick, {once: true});
		});
	}

	// Handle form submit and all logic.
	onClick = (event) => {
		event.preventDefault();

		const item = event.target;
		const field = this.state.getFormFieldElementByChild(item);

		const formData = new FormData();

		formData.append('id', field.getAttribute(this.state.getStateAttribute('locationsId')));
		formData.append('type', field.getAttribute(this.state.getStateAttribute('locationsType')));
		this.utils.showLoader(this.FORM_ID);

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

		fetch(this.state.getRestUrl('locations'), body)
			.then((response) => {
				this.utils.formSubmitErrorContentType(response, 'location', this.FORM_ID);

				return response.text();
			})
			.then((responseData) => {
				const response = this.utils.formSubmitIsJsonString(responseData, 'location', this.FORM_ID);

				this.utils.hideLoader(this.FORM_ID);

				if (response.status === 'success') {
					item.classList.add(this.state.getStateSelector('isHidden'));
					item.closest(this.itemSelector).insertAdjacentHTML('afterend', response.data.output);
					item.remove();
				}
			});
	};
}
