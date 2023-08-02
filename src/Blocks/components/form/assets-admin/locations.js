import { Utils } from "../assets/utilities";
import { State, ROUTES } from "../assets/state";

export class Locations {
	constructor(options = {}) {
		this.utils = new Utils();
		this.state = new State();

		this.selector = options.selector;
		this.FORM_ID = 0;
	}

	init() {
		[...document.querySelectorAll(this.selector)].forEach((element) => {
			element.addEventListener('click', this.onClick, true);
		});
	}

	// Handle form submit and all logic.
	onClick = (event) => {
		event.preventDefault();

		const item = event.target;

		const formData = new FormData();

		formData.append('id', item.getAttribute(this.state.getStateAttribute('locationsId')));
		this.utils.showLoader(this.FORM_ID, false);

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

		fetch(this.state.getRestUrl(ROUTES.LOCATIONS), body)
			.then((response) => {
				return response.json();
			})
			.then((response) => {
				this.utils.hideLoader(this.FORM_ID);

				if (response.status === 'success') {
					item.classList.add(this.state.getStateSelectorsClassHidden());
					item.parentNode.parentNode.parentNode.parentNode.insertAdjacentHTML('afterend', response.data.output);
				}
			});
	};
}
