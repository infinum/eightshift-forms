import { cookies } from '@eightshift/frontend-libs/scripts/helpers';

export class Form {
	constructor(options) {
		this.formIsAdmin = options.formIsAdmin || false;
		this.formSubmitRestApiUrl = options.formSubmitRestApiUrl;

		this.formSelector = options.formSelector;
		this.submitSingleSelector = `${this.formSelector}-single-submit`;
		this.errorSelector = `${this.formSelector}-error`;
		this.loaderSelector = `${this.formSelector}-loader`;
		this.globalMsgSelector = `${this.formSelector}-global-msg`;
		this.groupSelector = `${this.formSelector}-group`;
		this.groupInnerSelector = `${this.formSelector}-group-inner`;

		this.CLASS_ACTIVE = 'is-active';
		this.CLASS_LOADING = 'is-loading';
		this.CLASS_HAS_ERROR = 'has-error';

		this.redirectionTimeout = options.redirectionTimeout ?? 600;
		this.hideGlobalMessageTimeout = options.hideGlobalMessageTimeout ?? 6000;
	}

	// Init all actions.
	init = () => {
		const elements = document.querySelectorAll(this.formSelector);

		[...elements].forEach((element) => {
			element.addEventListener('submit', this.onFormSubmit);

			if (this.formIsAdmin) {
				const items = element.querySelectorAll(this.submitSingleSelector);
	
				[...items].forEach((item) => {
					if (item.type === 'submit') {
						item.addEventListener('click', this.onFormSubmitSingle);
					} else {
						item.addEventListener('change', this.onFormSubmitSingle);
					}
				});
			}
		});
	}

	// Handle form submit and all logic.
	onFormSubmit = (event) => {
		event.preventDefault();

		this.formSubmit(event.target);
	}

	// Handle form submit and all logic for only one field click.
	onFormSubmitSingle = (event) => {
		event.preventDefault();
		const {target} = event;

		this.formSubmit(target.closest(this.formSelector), target);
	}

	// Handle form submit and all logic.
	formSubmit = (element, singleSubmit = false) => {

		// Dispatch event.
		this.dispatchFormEvent(element, 'BeforeFormSubmit');

		// Loader show.
		this.showLoader(element);

		// Clear all errors before resubmit.
		this.reset(element);

		const formData = this.getFormData(element, singleSubmit);

		const formType = element.getAttribute('data-form-type');

		// Populate body data.
		const body = {
			method: element.getAttribute('method'),
			mode: 'same-origin',
			headers: {
				Accept: 'multipart/form-data',
			},
			body: formData,
			credentials: 'same-origin',
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		let url = this.formSubmitRestApiUrl;

		if (!this.formIsAdmin) {
			url = `${this.formSubmitRestApiUrl}-${formType}`;
		}

		fetch(url, body)
			.then((response) => {
				return response.json();
			})
			.then((response) => {
				// Dispatch event.
				this.dispatchFormEvent(element, 'AfterFormSubmit');

				// Clear all form errors.
				this.resetErrors(element);

				// Remove loader.
				this.hideLoader(element);

				// On success state.
				if (response.code === 200) {
					// Send GTM.
					this.gtmSubmit(element);

					// If success, redirect or output msg.
					let isRedirect = element?.dataset?.successRedirect ?? '';

					// Redirect on success.
					if (isRedirect !== '' || singleSubmit) {
						// Dispatch event.
						this.dispatchFormEvent(element, 'AfterFormSubmitSuccessRedirect');

						// Set global msg.
						this.setGlobalMsg(element, response.message, 'success');

						// Replace string templates used for passing data via url.
						for (var [key, val] of formData.entries()) {
							const { value } = JSON.parse(val);
							isRedirect = isRedirect.replaceAll(`{${key}}`, encodeURIComponent(value));
						}

						// Do the actual redirect after some time.
						setTimeout(() => {
							window.location.href = isRedirect;
						}, parseInt(this.redirectionTimeout, 10));
					} else {
						// Do normal success without redirect.
						// Dispatch event.
						this.dispatchFormEvent(element, 'AfterFormSubmitSuccess');

						// Set global msg.
						this.setGlobalMsg(element, response.message, 'success');

						// Clear form values.
						this.resetForm(element);
					}
				}

				// Normal errors.
				if (response.status === 'error') {
					// Dispatch event.
					this.dispatchFormEvent(element, 'AfterFormSubmitError');

					// Set global msg.
					this.setGlobalMsg(element, response.message, 'error');
				}

				// Fatal errors, trigger bugsnag.
				if (response.status === 'error_fatal') {
					// Dispatch event.
					this.dispatchFormEvent(element, 'AfterFormSubmitErrorFatal');

					// Set global msg.
					this.setGlobalMsg(element, response.message, 'error');

					// Trigger error.
					throw new Error(JSON.stringify(response));
				}

				// Validate fields error.
				if (response.status === 'error_validation') {
					// Dispatch event.
					this.dispatchFormEvent(element, 'AfterFormSubmitErrorValidation');

					// Output field errors.
					this.outputErrors(element, response.validation);
				}

				// Hide global msg in any case after some time.
				setTimeout(() => {
					this.hideGlobalMsg(element);
				}, parseInt(this.hideGlobalMessageTimeout, 10));

				// Dispatch event.
				this.dispatchFormEvent(element, 'AfterFormSubmitEnd');
			});
	}

	// Build form data object.
	getFormData = (element, singleSubmit = false) => {
		const formData = new FormData();

		const groups = element.querySelectorAll(`${this.groupSelector}`);

		// Check if we are saving group items in one key.
		if (groups.length && !singleSubmit) {
			for (const [key, group] of Object.entries(groups)) { // eslint-disable-line no-unused-vars
				const groupId = group.getAttribute('data-field-id');
				const groupInner = group.querySelectorAll(`
					${this.groupInnerSelector} input,
					${this.groupInnerSelector} select,
					${this.groupInnerSelector} textarea
				`);

				if (groupInner.length) {
					const groupInnerItems = {}
					for (const [key, groupInnerItem] of Object.entries(groupInner)) { // eslint-disable-line no-unused-vars
						const {
							id,
							value,
						} = groupInnerItem;
		
						groupInnerItems[id] = value;
					}

					formData.append(groupId, JSON.stringify({
						value: groupInnerItems,
						type: 'group',
					}));
				}
			}
		}

		let items = element.querySelectorAll(`
			input:not(${this.groupInnerSelector} input),
			select:not(${this.groupInnerSelector} select),
			textarea:not(${this.groupInnerSelector} textarea)
		`);

		const formType = element.getAttribute('data-form-type');

		// If single submit override items and pass only one item to submit.
		if (singleSubmit) {
			items = [
				singleSubmit
			];
		}

		// Iterate all form items.
		for (const [key, item] of Object.entries(items)) { // eslint-disable-line no-unused-vars
			const {
				type,
				name,
				id,
				files,
				disabled,
				checked,
			} = item;

			if (disabled) {
				continue;
			}

			let {
				value,
			} = item;

			// Build data object.
			const data = {
				name,
				value,
				type,
			};

			// If checkbox/radio on empty change to empty value.
			if ((type === 'checkbox' || type === 'radio') && !checked) {
				data.value = '';
			}

			// Append files field.
			if (type === 'file' && files.length) {
				for (const [key, file] of Object.entries(files)) {
					formData.append(`${id}[${key}]`, file);
				}
			} else {
				// Output/append all fields.
				formData.append(id, JSON.stringify(data));
			}
		}

		// Add form ID field.
		formData.append('es-form-post-id', JSON.stringify({
			value: element.getAttribute('data-form-post-id'),
			type: 'hidden',
		}));

		// Add form type field.
		formData.append('es-form-type', JSON.stringify({
			value: formType,
			type: 'hidden',
		}));

		// Add additional options for HubSpot only.
		if (formType === 'hubspot' && !this.formIsAdmin) {
			formData.append('es-form-hubspot-cookie', JSON.stringify({
				value: cookies.getCookie('hubspotutk'),
				type: 'hidden',
			}));

			formData.append('es-form-hubspot-page-name', JSON.stringify({
				value: document.title,
				type: 'hidden',
			}));

			formData.append('es-form-hubspot-page-url', JSON.stringify({
				value: window.location.href,
				type: 'hidden',
			}));
		}

		if (singleSubmit && this.formIsAdmin) {
			formData.append('es-form-single-submit', JSON.stringify({
				value: 'true',
				type: 'hidden',
			}));
		}

		return formData;
	}

	// Output all error for fields.
	outputErrors = (element, fields) => {
		// Set error classes and error text on fields which have validation errors.
		for (const [key] of Object.entries(fields)) {
			const item = element.querySelector(`${this.errorSelector}[data-id="${key}"]`);

			item?.parentElement?.classList.add(this.CLASS_HAS_ERROR);

			if (item !== null) {
				item.innerHTML = fields[key];
			}
		}

		// Scroll to element if the condition is right.
		if (typeof fields !== 'undefined' && element.getAttribute('data-disable-scroll-to-field-on-error') !== '1') {
			const firstItem = Object.keys(fields)[0];

			this.scrollToElement(element.querySelector(`${this.errorSelector}[data-id="${firstItem}"]`).parentElement);
		}
	}

	// Reset form values if the condition is right.
	resetForm = (element) => {
		if (element.getAttribute('data-reset-on-success') === '1') {
			element.reset();
		}
	}

	// Reset for in general.
	reset = (element) => {
		const items = element.querySelectorAll(this.errorSelector);
		[...items].forEach((item) => {
			item.innerHTML = '';
		});

		this.unsetGlobalMsg(element);
	}

	// Show loader.
	showLoader = (form) => {
		const loader = form.querySelector(this.loaderSelector);

		form?.classList?.add(this.CLASS_LOADING);

		if (!loader) {
			return;
		}

		loader.classList.add(this.CLASS_ACTIVE);
	}

	// Hide loader.
	hideLoader = (form) => {
		const loader = form.querySelector(this.loaderSelector);

		form?.classList?.remove(this.CLASS_LOADING);

		if (!loader) {
			return;
		}

		loader.classList.remove(this.CLASS_ACTIVE);
	}

	// Reset all error classes.
	resetErrors = (form) => {
		// Reset all error classes on fields.
		form.querySelectorAll(`.${this.CLASS_HAS_ERROR}`).forEach((element) => element.classList.remove(this.CLASS_HAS_ERROR));
	}

	// Set global message.
	setGlobalMsg = (form, msg, status) => {
		const messageContainer = form.querySelector(this.globalMsgSelector);

		if (!messageContainer) {
			return;
		}

		messageContainer.classList.add(this.CLASS_ACTIVE);
		messageContainer.dataset.status = status;
		messageContainer.innerHTML = `<span>${msg}</span>`;

		// Scroll to msg if the condition is right.
		if (status === 'success' && form.getAttribute('data-disable-scroll-to-global-message-on-success') !== '1') {
			this.scrollToElement(messageContainer);
		}
	}

	// Unset global message.
	unsetGlobalMsg(form) {
		const messageContainer = form.querySelector(this.globalMsgSelector);

		if (!messageContainer) {
			return;
		}

		messageContainer.classList.remove(this.CLASS_ACTIVE);
		messageContainer.dataset.status = '';
		messageContainer.innerHTML = '';
	}

	// Hide global message.
	hideGlobalMsg(form) {
		const messageContainer = form.querySelector(this.globalMsgSelector);

		if (!messageContainer) {
			return;
		}

		messageContainer.classList.remove(this.CLASS_ACTIVE);
	}

	// Submit GTM event.
	gtmSubmit(element) {
		const eventName = element.getAttribute('data-tracking-event-name');

		if (eventName) {
			const gtmData = this.getGtmData(element, eventName);

			if (window?.dataLayer && gtmData?.event) {
				this.dispatchFormEvent(element, 'BeforeGtmDataPush');
				window.dataLayer.push(gtmData);
			}
		}
	}

	// Build GTM data for the data layer.
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

	// Scroll to specific element.
	scrollToElement = (element) => {
		if (element !== null) {
			element.scrollIntoView({block: 'start', behavior: 'smooth'});
		}
	}

	// Dispatch custom event.
	dispatchFormEvent(element, name) {
		const event = new CustomEvent(`esForms${name}`);

		element.dispatchEvent(event);
	}
}
