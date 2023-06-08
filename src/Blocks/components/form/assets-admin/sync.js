import { Utils } from "./../../form/assets/utilities";
import { State, ROUTES } from "./../../form/assets/state";

export class Sync {
	constructor(options = {}) {
		this.utils = new Utils();
		this.state = new State();

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

		const item = event.target;

		const formId = 0;

		const formData = new FormData();

		formData.append('id', item.getAttribute(this.state.getStateAttribute('syncId')));

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

		fetch(this.state.getRestUrl(ROUTES.SYNC_DIRECT), body)
			.then((response) => {
				return response.json();
			})
			.then((response) => {
				const {
					message,
					status,
				} = response;

				this.utils.setGlobalMsg(formId, message, status);

				if (response.status === 'success') {
					setTimeout(() => {
						location.reload();
					}, 1000);
				}

				setTimeout(() => {
					this.utils.unsetGlobalMsg(formId);
				}, 6000);
			});
	};
}
