import { Utils } from "../assets/utilities";
import { State, ROUTES } from "../assets/state";

export class Migration {
	constructor(options = {}) {
		this.utils = new Utils();
		this.state = new State();

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

		const formData = new FormData();

		const formId = this.state.getFormIdByElement(event.target);

		formData.append('type', event.target.getAttribute(this.state.getStateAttribute('migrationType')));

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

		fetch(this.state.getRestUrl(ROUTES.MIGRATION), body)
			.then((response) => {
				return response.json();
			})
			.then((response) => {
				const {
					message,
					status,
				} = response;

				this.utils.setGlobalMsg(formId, message, status);

				document.querySelector(this.outputSelector).value = JSON.stringify(response, null, 4);

				setTimeout(() => {
					this.utils.unsetGlobalMsg(formId);
				}, 6000);
			});
	};
}
