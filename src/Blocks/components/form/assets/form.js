export class Form {
	constructor(options) {
		this.formSubmitRestApiUrl = options.formSubmitRestApiUrl;

		this.formSelector = options.formSelector;
		this.errorSelector = `${this.formSelector}-error`;
		this.loaderSelector = `${this.formSelector}-loader`;
		this.globalMsgSelector = `${this.formSelector}-global-msg`;

		this.CLASS_ACTIVE = 'is-active';
		this.CLASS_LOADING = 'is-loading';
		this.CLASS_HAS_ERROR = 'has-error';
	}

	init = () => {
		const elements = document.querySelectorAll(this.formSelector);

		[...elements].forEach((element) => {
			element.addEventListener('submit', this.onFormSubmit);
		});
	}

	onFormSubmit = (event) => {
		event.preventDefault();

		const element = event.target;

		this.showLoader(element);

		this.reset(element);

		const body = {
			method: element.getAttribute('method'),
			mode: 'same-origin',
			headers: {
				Accept: 'multipart/form-data',
			},
			body: this.formatFormData(element),
			credentials: 'same-origin',
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		fetch(this.formSubmitRestApiUrl, body)
			.then((response) => {
				return response.json();
			})
			.then((response) => {
				this.resetErrors(element);

				this.hideLoader(element);

				if (response.code === 200) {
					// Send GTM.
					this.gtmSubmit(element);

					// If success, redirect or output msg.
					const isRedirect = element.getAttribute('data-success-redirect');
					if (isRedirect !== '') {
						this.setGlobalMsg(element, response.message, 'success');

						setTimeout(() => {
							window.location.href = isRedirect;
						}, 1000);
					} else {
						this.setGlobalMsg(element, response.message, 'success');
					}
				}

				// Normal errors.
				if (response.status === 'error') {
					this.setGlobalMsg(element, response.message, 'error');
				}

				// Fatal errors, trigger bugsnag.
				if (response.status === 'error_fatal') {
					this.setGlobalMsg(element, response.message, 'error');
					throw new Error(JSON.stringify(response));
				}

				// Validate fields.
				if (response.status === 'error_validation') {
					this.outputErrors(element, response.validation);
				}

				setTimeout(() => {
					this.hideGlobalMsg(element);
				}, 6000);
			});
	}

	formatFormData = (element) => {
		const items = element.querySelectorAll('input, select, button, textarea');

		const formData = new FormData();

		for (const [key, item] of Object.entries(items)) { // eslint-disable-line no-unused-vars
			const {
				type,
				name,
				id,
				dataset = {},
				checked,
				files,
				disabled,
			} = item;

			let {
				value,
			} = item;

			if (disabled) {
				continue;
			}

			let label = element.querySelector(`label[for="${id}"]`);

			if (label !== null) {
				label = label.innerText;
			} else {
				label = name;
			}

			if (type === 'checkbox' || type === 'radio') {
				if (!checked) {
					value = '';
				}
			}

			// Check for radio values.
			if (type === 'radio' && formData.get(name)) {
				if (!checked) {
					continue;
				} else {
					formData.delete(name);
				}
			}

			const data = {
				id,
				name,
				value,
				type,
				label,
				checked,
				data: dataset,
			};

			// Output all fields.
			if (type === 'file' && files.length) {

				for (const [key, file] of Object.entries(files)) {
					formData.append(`${name}-${key}`, file);
					data.value = "1";
					formData.append(`${name}-${key}`, JSON.stringify(data));
				}
			} else {
				formData.append(name, JSON.stringify(data));
			}
		}

		formData.append('es-form-post-id', JSON.stringify({
			id: 'es-form-post-id',
			value: element.getAttribute('data-form-post-id'),
			type: 'hidden',
			label: 'Form Id',
			data: {},
		}));

		formData.append('es-form-type', JSON.stringify({
			id: 'es-form-type',
			value: element.getAttribute('data-form-type'),
			type: 'hidden',
			label: 'Form type',
			data: {},
		}));

		return formData;
	}

	outputErrors = (element, fields) => {
		// Set error classes and error text on fields which have validation errors.
		for (const [key] of Object.entries(fields)) {
			const item = element.querySelector(`${this.errorSelector}[data-id="${key}"]`);

			item?.parentElement?.classList.add(this.CLASS_HAS_ERROR);

			if (item !== null) {
				item.innerHTML = fields[key];
			}
		}

		if (typeof fields !== 'undefined') {
			const firstItem = Object.keys(fields)[0];

			this.scrollToElement(element.querySelector(`${this.errorSelector}[data-id="${firstItem}"]`).parentElement);
		}
	}

	reset = (element) => {
		const items = element.querySelectorAll(this.errorSelector);
		[...items].forEach((item) => {
			item.innerHTML = '';
		});

		this.unsetGlobalMsg(element);
	}

	showLoader = (form) => {
		const loader = form.querySelector(this.loaderSelector);

		form?.classList?.add(this.CLASS_LOADING);

		if (!loader) {
			return;
		}

		loader.classList.add(this.CLASS_ACTIVE);
	}

	hideLoader = (form) => {
		const loader = form.querySelector(this.loaderSelector);

		form?.classList?.remove(this.CLASS_LOADING);

		if (!loader) {
			return;
		}

		loader.classList.remove(this.CLASS_ACTIVE);
	}

	resetErrors = (form) => {
		// Reset all error classes on fields.
		form.querySelectorAll(`.${this.CLASS_HAS_ERROR}`).forEach((element) => element.classList.remove(this.CLASS_HAS_ERROR));	
	}

	setGlobalMsg = (form, msg, status) => {
		const messageContainer = form.querySelector(this.globalMsgSelector);

		if (!messageContainer) {
			return;
		}

		messageContainer.classList.add(this.CLASS_ACTIVE);
		messageContainer.dataset.status = status;
		messageContainer.innerHTML = `<span>${msg}</span>`;
	}

	unsetGlobalMsg(form) {
		const messageContainer = form.querySelector(this.globalMsgSelector);

		if (!messageContainer) {
			return;
		}

		messageContainer.classList.remove(this.CLASS_ACTIVE);
		messageContainer.dataset.status = '';
		messageContainer.innerHTML = '';
	}

	hideGlobalMsg(form) {
		const messageContainer = form.querySelector(this.globalMsgSelector);

		if (!messageContainer) {
			return;
		}

		messageContainer.classList.remove(this.CLASS_ACTIVE);
	}

	gtmSubmit(element) {
		const eventName = element.getAttribute('data-tracking-event-name');

		if (eventName) {
			const gtmData = this.getGtmData(element, eventName);

			if (window?.dataLayer && gtmData?.event) {
				window?.dataLayer.push(gtmData);
			}
		}
	}

	getGtmData(element, eventName) {
		const items = element.querySelectorAll('[data-tracking]');
		const data = {};

		if (!items.length) {
			return {};
		}

		[...items].forEach((item) => {
			const tracking = item.getAttribute('data-tracking');

			if (tracking) {
				const value = item.value;
				data[tracking] = value
			}
		});

		return Object.assign({}, { event: eventName, ...data });
	}

	scrollToElement = (element) => {
		if (element !== null) {
			element.scrollIntoView(true);
		}
	}
}