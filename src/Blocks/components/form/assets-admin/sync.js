import { Utils } from "./../../form/assets/utilities";
import { State, ROUTES } from "./../../form/assets/state";

export class Sync {
	constructor(options = {}) {
		this.utils = new Utils();
		this.state = new State();

		this.GLOBAL_MSG_TIMEOUT_ID = undefined;
		this.FORM_ID = 0;

		this.selector = options.selector;
	}

	init() {
		[...document.querySelectorAll(this.selector)].forEach((element) => {
			element.addEventListener('click', this.onClick, true);
		});

		this.state.getStateFormGlobalMsgElement(this.FORM_ID).addEventListener('mouseenter', this.onGlobalMsgFocus);
		this.state.getStateFormGlobalMsgElement(this.FORM_ID).addEventListener('mouseleave', this.onGlobalMsgBlur);
	}

	// Handle form submit and all logic.
	onClick = (event) => {
		event.preventDefault();

		const item = event.target;

		const formData = new FormData();

		formData.append('id', item.getAttribute(this.state.getStateAttribute('syncId')));

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

				this.utils.hideLoader(this.FORM_ID);
				this.utils.setGlobalMsg(this.FORM_ID, message, status);

				if (response.status === 'success') {
					setTimeout(() => {
						location.reload();
					}, 1000);
				}

				this.hideGlobalMsg();
			});
	};

	onGlobalMsgFocus = () => {
		if (typeof this.GLOBAL_MSG_TIMEOUT_ID === "number") {
			clearTimeout(this.GLOBAL_MSG_TIMEOUT_ID);
		}
	};

	onGlobalMsgBlur = () => {
		this.hideGlobalMsg(this.FORM_ID);
	};

	hideGlobalMsg() {
		this.GLOBAL_MSG_TIMEOUT_ID = setTimeout(() => {
			this.utils.unsetGlobalMsg(this.FORM_ID);
		}, 6000);
	}
}
