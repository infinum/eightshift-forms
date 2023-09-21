import { Utils } from "../assets/utilities";
import { State, ROUTES } from "../assets/state";

export class Bulk {
	constructor(options = {}) {
		this.utils = new Utils();
		this.state = new State();

		this.GLOBAL_MSG_TIMEOUT_ID = undefined;
		this.FORM_ID = 0;

		this.selector = options.selector;
		this.itemsSelector = options.itemsSelector;
		this.itemSelector = options.itemSelector;
	}

	init() {
		this.toggleDisableButton();

		[...document.querySelectorAll(this.selector)].forEach((element) => {
			element.addEventListener('click', this.onClick, true);
		});

		[...document.querySelectorAll(`${this.itemSelector} input`)].forEach((element) => {
			element.addEventListener('click', this.onClickItem, true);
		});

		this.state.getStateFormGlobalMsgElement(this.FORM_ID)?.addEventListener('mouseenter', this.onGlobalMsgFocus);
		this.state.getStateFormGlobalMsgElement(this.FORM_ID)?.addEventListener('mouseleave', this.onGlobalMsgBlur);
	}

	onClickItem = (event) => {
		const formId = parseInt(event.target.name);
		const itemsElement = document.querySelector(this.itemsSelector);
		const items = itemsElement?.getAttribute(this.state.getStateAttribute('bulkItems'));

		let output = items ? JSON.parse(items) : [];

		if (event.target.checked) {
			output.push(formId);
		} else {
			output = output.filter((item) => item !== formId);
		}

		itemsElement.setAttribute(this.state.getStateAttribute('bulkItems'), JSON.stringify(output));
		this.toggleDisableButton();
	};

	// Handle form submit and all logic.
	onClick = (event) => {
		event.preventDefault();

		if (this.isDisableButton(event.target)) {
			return;
		}

		const formData = new FormData();

		formData.append('type', event.target?.getAttribute(this.state.getStateAttribute('bulkType')));
		formData.append('ids', document.querySelector(this.itemsSelector)?.getAttribute(this.state.getStateAttribute('bulkItems')));

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

		fetch(this.state.getRestUrl(ROUTES.BULK), body)
			.then((response) => {
				this.utils.formSubmitErrorContentType(response, 'bulk', null);
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

	toggleDisableButton() {
		const items = document.querySelector(this.itemsSelector)?.getAttribute(this.state.getStateAttribute('bulkItems'));

		const ids = items ? JSON.parse(items) : [];

		[...document.querySelectorAll(this.selector)].forEach((element) => {
			if (!ids.length) {
				element.classList.add(this.state.getStateSelectorsClassDisabled());
			} else {
				element.classList.remove(this.state.getStateSelectorsClassDisabled());
			}
		});
	}

	isDisableButton(element) {
		return element.classList.contains(this.state.getStateSelectorsClassDisabled());
	}

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
