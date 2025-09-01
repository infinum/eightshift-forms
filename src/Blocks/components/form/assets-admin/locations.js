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

	onClick = (event) => {
		event.preventDefault();

		this.submit(event.target);
	};

	async submit(target) {
		const field = this.state.getFormFieldElementByChild(target);

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

		try {
			const response = await fetch(this.state.getRestUrl('locations'), body);
			const parsedResponse = await response.json();

			const {
				status,
				data,
			} = parsedResponse;

			this.utils.hideLoader(this.FORM_ID);

			if (status === 'success') {
				target.classList.add(this.state.getStateSelector('isHidden'));
				target.closest(this.itemSelector).insertAdjacentHTML('afterend', data[this.state.getStateResponseOutputKey('adminLocations')]);
				target.remove();
			}
		} catch ({name, message}) {
			if (name === 'AbortError') {
				return;
			}

			throw new Error(this.utils.formSubmitResponseError(this.FORM_ID, 'adminLocations', name, message));
		}
	};
}
