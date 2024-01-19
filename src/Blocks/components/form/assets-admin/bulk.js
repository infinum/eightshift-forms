export class Bulk {
	constructor(options = {}) {
		/** @type {import('./../assets/utils').Utils} */
		this.utils = options.utils;
		/** @type {import('./../assets/state').State} */
		this.state = this.utils.getState();

		this.GLOBAL_MSG_TIMEOUT_ID = undefined;
		this.FORM_ID = 0;

		this.selector = options.selector;
		this.itemsSelector = options.itemsSelector;
		this.itemSelector = options.itemSelector;
		this.selectAllSelector = options.selectAllSelector;
	}

	init() {
		document.querySelector(`${this.selectAllSelector} input`)?.addEventListener('click', this.onClickSelectAll, true);

		[...document.querySelectorAll(this.selector)].forEach((element) => {
			element.addEventListener('click', this.onClick, true);
		});

		[...document.querySelectorAll(`${this.itemSelector} input`)].forEach((element) => {
			element.addEventListener('click', this.onClickItem, true);
		});

		this.state.getStateFormGlobalMsgElement(this.FORM_ID)?.addEventListener('mouseenter', this.onGlobalMsgFocus);
		this.state.getStateFormGlobalMsgElement(this.FORM_ID)?.addEventListener('mouseleave', this.onGlobalMsgBlur);
	}

	onClickSelectAll = (event) => {
		[...document.querySelectorAll(`${this.itemSelector} input`)].forEach((element) => {
			const item = element?.closest(this.itemSelector);

			if (!item?.classList?.contains(this.state.getStateSelector('isHidden'))) {
				element.checked = event.target.checked;
				this.selectItem(parseInt(element.name), element.checked);
			}
		});

		this.toggleAllOtherButtons();
	};

	onClickItem = (event) => {
		this.selectItem(parseInt(event.target.name), event.target.checked);
	};

	// Handle form submit and all logic.
	onClick = (event) => {
		event.preventDefault();

		if (this.isDisableButton(event.target)) {
			return;
		}

		const formData = new FormData();

		const field = this.state.getFormFieldElementByChild(event.target);
		const type = field?.getAttribute(this.state.getStateAttribute('bulkType'));

		// Can be fake to prevent submit and use button toggles for other things like export.
		if (type === 'fake') {
			return;
		}

		formData.append('type', field?.getAttribute(this.state.getStateAttribute('bulkType')));
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

		fetch(this.state.getRestUrl('bulk'), body)
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

	toggleAllOtherButtons() {
		const items = document.querySelector(this.itemsSelector)?.getAttribute(this.state.getStateAttribute('bulkItems'));
		const button = document.querySelector(`${this.selectAllSelector} input`);

		const output = items ? JSON.parse(items) : [];

		if (!output.length) {
			button.classList.add(this.state.getStateSelector('isActive'));
		} else {
			button.classList.remove(this.state.getStateSelector('isActive'));
		}
	}

	selectItem(formId, status=false) {
		const itemsElement = document.querySelector(this.itemsSelector);
		const items = itemsElement?.getAttribute(this.state.getStateAttribute('bulkItems'));

		let output = items ? JSON.parse(items) : [];

		if (status) {
			output.push(formId);
		} else {
			output = output.filter((item) => item !== formId);
		}

		itemsElement.setAttribute(this.state.getStateAttribute('bulkItems'), JSON.stringify(output));
		this.toggleDisableButton();
	}

	toggleDisableButton() {
		const items = document.querySelector(this.itemsSelector)?.getAttribute(this.state.getStateAttribute('bulkItems'));

		const ids = items ? JSON.parse(items) : [];

		[...document.querySelectorAll(this.selector)].forEach((element) => {
			if (!ids.length) {
				element.disabled = true;
			} else {
				element.disabled = false;
			}
		});
	}

	isDisableButton(element) {
		return element.classList.contains(this.state.getStateSelector('isDisabled'));
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
