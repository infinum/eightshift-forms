export class ManualImportApi {
	constructor(options = {}) {
		/** @type {import('./../assets/utils').Utils} */
		this.utils = options.utils;
		/** @type {import('./../assets/state').State} */
		this.state = this.utils.getState();

		this.selector = options.selector;
		this.outputSelector = options.outputSelector;
		this.dataSelector = options.dataSelector;
		this.importErrorMsg = options.importErrorMsg;
	}

	init () {
		[...document.querySelectorAll(this.selector)].forEach((element) => {
			element.addEventListener('click', this.onClick, true);
		});
	}

	// Handle form submit and all logic.
	onClick = (event) => {
		event.preventDefault();

		const formId = this.state.getFormIdByElement(event.target);
		const data = document.querySelector(this.dataSelector);
		const dataValue = data?.value;

		const formData = new FormData();

		const items = this.getIntegrationData(dataValue);

		this.utils.showLoader(formId);

		// Clear output everytime.
		document.querySelector(this.outputSelector).value = '';

		// If no items, show error and return.
		if (!items.length) {
			this.utils.hideLoader(formId);
			this.utils.setGlobalMsg(
				formId,
				this.importErrorMsg,
				'error'
			);

			return;
		}

		[...items].forEach((item, index) => {
			setTimeout(() => {
				if (item.formId) {
					formData.append(this.state.getStateParam('formId'), JSON.stringify({
						name: this.state.getStateParam('formId'),
						value: item.formId,
						type: 'text',
						typeCustom: 'text',
						custom: '',
					}));
				}

				if (item.postId) {
					formData.append(this.state.getStateParam('postId'), JSON.stringify({
						name: this.state.getStateParam('postId'),
						value: item.postId,
						type: 'text',
						typeCustom: 'text',
						custom: '',
					}));
				}

				if (item.type) {
					formData.append(this.state.getStateParam('type'), JSON.stringify({
						name: this.state.getStateParam('type'),
						value: item.type,
						type: 'text',
						typeCustom: 'text',
						custom: '',
					}));
				}

				formData.append(this.state.getStateParam('direct'), JSON.stringify({
					name: this.state.getStateParam('direct'),
					value: 'true',
					type: 'text',
					typeCustom: 'text',
					custom: '',
				}));

				if (item.itemId) {
					formData.append(this.state.getStateParam('itemId'), JSON.stringify({
						name: this.state.getStateParam('itemId'),
						value: item.itemId,
						type: 'text',
						typeCustom: 'text',
						custom: '',
					}));
				}

				if (item.innerId) {
					formData.append(this.state.getStateParam('innerId'), JSON.stringify({
						name: this.state.getStateParam('innerId'),
						value: item.innerId,
						type: 'text',
						typeCustom: 'text',
						custom: '',
					}));
				}

				for(const [name, value] of Object.entries(item.params)) {
					formData.append(name, JSON.stringify({
						name: name,
						value: value,
						type: 'text',
						typeCustom: 'text',
						custom: '',
					}));
				}

				// Populate body data.
				const body = {
					method: 'POST',
					mode: 'same-origin',
					headers: {
						Accept: 'multipart/form-data',
						'X-WP-Nonce': this.state.getStateConfigNonce(),
					},
					body: formData,
					redirect: 'follow',
					referrer: 'no-referrer',
				};

				fetch(this.state.getRestUrlByType('prefixSubmit', item.type), body)
					.then((response) => {
						this.utils.formSubmitErrorContentType(response, 'manualImport', formId);

						return response.text();
					})
					.then((responseData) => {
						const response = this.utils.formSubmitIsJsonString(responseData, 'manualImport', formId);

						const {
							message,
							status,
						} = response;

						this.utils.hideLoader(formId);
						this.utils.setGlobalMsg(formId, message, status);

						data.value = '';
						document.querySelector(this.outputSelector).value += `${JSON.stringify(response, null, 4)} \n`;
					});

					if (items.length - 1 === index) {
						setTimeout(() => {
							this.utils.unsetGlobalMsg(formId);
						}, 6000);
					}
			}, 2000 * index);
		});
	};

	getIntegrationData(data) {
		const output = [];

		if (!data) {
			return output;
		}

		let items = {};

		// Check if we can parse data.
		try {
			items = JSON.parse(data);
		} catch {
			return output;
		}

		if (!items) {
			return output;
		}

		let itemsOutput = items;

		// Check if items is iterable. One or multiple items.
		if (typeof items[Symbol.iterator] !== 'function') {
			itemsOutput = [items];
		}

		[...itemsOutput].forEach((item) => {
			output.push({
				postId: item.postId,
				formId: item.formId,
				type: item.type,
				params: item.params,
				itemId: item.itemId,
				innerId: item.innerId,
			});
		});

		return output;
	}
}
