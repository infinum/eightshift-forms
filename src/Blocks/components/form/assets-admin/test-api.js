import { Utils } from "../assets/utilities";
import { State, ROUTES } from "../assets/state";

export class TestApi {
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

		const formId = this.state.getFormIdByElement(event.target);
		const integrationType = this.state.getStateFormTypeSettings(formId);

		const formData = new FormData();

		formData.append('type', integrationType);

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

		fetch(this.state.getRestUrlByType(ROUTES.PREFIX_TEST_API, integrationType), body)
			.then((response) => {
				return response.json();
			})
			.then((response) => {
				const {
					message,
					status,
				} = response;

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
