/* global grecaptcha */

import { cookies } from '@eightshift/frontend-libs/scripts/helpers';
import Dropzone from "dropzone";
import autosize from 'autosize';
import Choices from 'choices.js';

export const FORM_EVENTS = {
	BEFORE_FORM_SUBMIT: 'BeforeFormSubmit',
	AFTER_FORM_SUBMIT: 'AfterFormSubmit',
	AFTER_FORM_SUBMIT_SUCCESS_REDIRECT: 'AfterFormSubmitSuccessRedirect',
	AFTER_FORM_SUBMIT_SUCCESS: 'AfterFormSubmitSuccess',
	AFTER_FORM_SUBMIT_ERROR: 'AfterFormSubmitError',
	AFTER_FORM_SUBMIT_ERROR_FATAL: 'AfterFormSubmitErrorFatal',
	AFTER_FORM_SUBMIT_ERROR_VALIDATION: 'AfterFormSubmitErrorValidation',
	AFTER_FORM_SUBMIT_END: 'AfterFormSubmitEnd',
	BEFORE_GTM_DATA_PUSH: 'BeforeGtmDataPush',
};

export const FORM_SELECTORS = {
	CLASS_ACTIVE: 'is-active',
	CLASS_FILLED: 'is-filled',
	CLASS_LOADING: 'is-loading',
	CLASS_HAS_ERROR: 'has-error',
};

export const FORM_DATA_ATTRIBUTES = {
	DATA_ATTR_FORM_TYPE: 'data-form-type',
	DATA_ATTR_FIELD_ID: 'data-field-id',
	DATA_ATTR_FORM_POST_ID: 'data-form-post-id',
	DATA_ATTR_TRACKING_EVENT_NAME: 'data-tracking-event-name',
	DATA_ATTR_TRACKING: 'data-tracking',
	DATA_ATTR_TRACKING_SELECT_LABEL: 'data-tracking-select-label',
	DATA_ATTR_SUCCESS_REDIRECT: 'data-success-redirect',
};

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
		this.customSelector = `${this.formSelector}-custom`;

		this.fieldSelector = `${this.formSelector}-field`;
		this.inputSelector = `${this.fieldSelector} input`;
		this.textareaSelector = `${this.fieldSelector} textarea`;
		this.selectSelector = `${this.fieldSelector} select`;
		this.fileSelector = `${this.fieldSelector} input[type='file']`;

		this.CLASS_ACTIVE = FORM_SELECTORS.CLASS_ACTIVE;
		this.CLASS_FILLED = FORM_SELECTORS.CLASS_FILLED;
		this.CLASS_LOADING = FORM_SELECTORS.CLASS_LOADING;
		this.CLASS_HAS_ERROR = FORM_SELECTORS.CLASS_HAS_ERROR;

		this.formDisableScrollToFieldOnError = options.formDisableScrollToFieldOnError ?? true;
		this.formDisableScrollToGlobalMessageOnSuccess = options.formDisableScrollToGlobalMessageOnSuccess ?? true;
		this.formResetOnSuccess = Boolean(options.formResetOnSuccess);
		this.redirectionTimeout = options.redirectionTimeout ?? 600;
		this.hideGlobalMessageTimeout = options.hideGlobalMessageTimeout ?? 6000;
		this.hideLoadingStateTimeout = options.hideLoadingStateTimeout ?? 600;
		this.fileCustomRemoveLabel = options.fileCustomRemoveLabel ?? '';
		this.captcha = options.captcha ?? '';

		// If using custom file create global object to store files.
		this.files = {};
		this.customSelects = [];
		this.customFiles = [];

		this.DATA_ATTR_FORM_TYPE = FORM_DATA_ATTRIBUTES.DATA_ATTR_FORM_TYPE;
		this.DATA_ATTR_FIELD_ID = FORM_DATA_ATTRIBUTES.DATA_ATTR_FIELD_ID;
		this.DATA_ATTR_FORM_POST_ID = FORM_DATA_ATTRIBUTES.DATA_ATTR_FORM_POST_ID;
		this.DATA_ATTR_TRACKING_EVENT_NAME = FORM_DATA_ATTRIBUTES.DATA_ATTR_TRACKING_EVENT_NAME;
		this.DATA_ATTR_TRACKING = FORM_DATA_ATTRIBUTES.DATA_ATTR_TRACKING;
		this.DATA_ATTR_TRACKING_SELECT_LABEL = FORM_DATA_ATTRIBUTES.DATA_ATTR_TRACKING_SELECT_LABEL;
	}

	// Init all actions.
	init = () => {
		const elements = document.querySelectorAll(this.formSelector);

		[...elements].forEach((element) => {

			// Regular submit.
			element.addEventListener('submit', this.onFormSubmit);

			// Single submit.
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

			const formId = element.getAttribute(this.DATA_ATTR_FORM_POST_ID);

			const inputs = element.querySelectorAll(this.inputSelector);
			const textareas = element.querySelectorAll(this.textareaSelector);
			const selects = element.querySelectorAll(this.selectSelector);
			const files = element.querySelectorAll(this.fileSelector);

			// Setup regular inputs.
			[...inputs].forEach((input) => {
				this.setupInputField(input);
			});
	
			// Setup select inputs.
			this.customSelects[formId] = [];
			[...selects].forEach((select) => {
				this.setupSelectField(select, formId);
			});
	
			// Setup textarea inputs.
			[...textareas].forEach((textarea) => {
				this.setupTextareaField(textarea);
			});

			// Setup file single inputs.
			this.customFiles[formId] = [];
			[...files].forEach((file) => {
				this.setupFileField(file, formId);
			});
		});
	}

	// Handle form submit and all logic.
	onFormSubmit = (event) => {
		event.preventDefault();

		const element = event.target;

		if (this.captcha) {
			grecaptcha.ready(() => {
				grecaptcha.execute(this.captcha, {action: 'submit'}).then((token) => {
					this.formSubmitCaptcha(element, token);
				});
			});
		} else {
			this.formSubmit(element);
		}
	}

	// Handle form submit and all logic for only one field click.
	onFormSubmitSingle = (event) => {
		event.preventDefault();
		const {target} = event;

		this.formSubmit(target.closest(this.formSelector), target);
	}

	// Handle form submit and all logic in case we have captcha in place.
	formSubmitCaptcha = (element, token) => {
		// Loader show.
		this.showLoader(element);

		// Populate body data.
		const body = {
			method: element.getAttribute('method'),
			mode: 'same-origin',
			headers: {
				Accept: 'application/json',
			},
			body: JSON.stringify({
				token,
				formId: element.getAttribute(this.DATA_ATTR_FORM_POST_ID),
			}),
			credentials: 'same-origin',
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		let url = `${this.formSubmitRestApiUrl}-captcha`;

		fetch(url, body)
		.then((response) => {
			return response.json();
		})
		.then((response) => {
			// On success state.
			if (response.code === 200) {
				this.formSubmit(element,);
			}

			// Normal errors.
			if (response.status === 'error') {
				// Clear all errors.
				this.reset(element);

				// Remove loader.
				this.hideLoader(element);

				// Set global msg.
				this.setGlobalMsg(element, response.message, 'error');

				// Hide global msg in any case after some time.
				setTimeout(() => {
					this.hideGlobalMsg(element);
				}, parseInt(this.hideGlobalMessageTimeout, 10));
			}
		});
	}

	// Handle form submit and all logic.
	formSubmit = (element, singleSubmit = false) => {

		// Dispatch event.
		this.dispatchFormEvent(element, FORM_EVENTS.BEFORE_FORM_SUBMIT);

		// Loader show.
		if (!this.captcha) {
			this.showLoader(element);
		}

		const formData = this.getFormData(element, singleSubmit);

		const formType = element.getAttribute(this.DATA_ATTR_FORM_TYPE);

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
				this.dispatchFormEvent(element, FORM_EVENTS.AFTER_FORM_SUBMIT);

				// Clear all errors.
				this.reset(element);

				// Remove loader.
				this.hideLoader(element);

				// On success state.
				if (response.code === 200) {
					// Send GTM.
					this.gtmSubmit(element);

					// Redirect on success.
					if (element.hasAttribute(FORM_DATA_ATTRIBUTES.DATA_ATTR_SUCCESS_REDIRECT) || singleSubmit) {
						// Dispatch event.
						this.dispatchFormEvent(element, FORM_EVENTS.AFTER_FORM_SUBMIT_SUCCESS_REDIRECT);

						// Set global msg.
						this.setGlobalMsg(element, response.message, 'success');

						let redirectUrl = element.getAttribute(FORM_DATA_ATTRIBUTES.DATA_ATTR_SUCCESS_REDIRECT) ?? '';

						// Replace string templates used for passing data via url.
						for (var [key, val] of formData.entries()) { // eslint-disable-line no-unused-vars
							const { value, name } = JSON.parse(val);
							redirectUrl = redirectUrl.replaceAll(`{${name}}`, encodeURIComponent(value));
						}

						// Do the actual redirect after some time.
						setTimeout(() => {
							window.location.href = redirectUrl;
						}, parseInt(this.redirectionTimeout, 10));
					} else {
						// Do normal success without redirect.
						// Dispatch event.
						this.dispatchFormEvent(element, FORM_EVENTS.AFTER_FORM_SUBMIT_SUCCESS);

						// Set global msg.
						this.setGlobalMsg(element, response.message, 'success');

						// Clear form values.
						this.resetForm(element);
					}
				}

				// Normal errors.
				if (response.status === 'error') {
					// Dispatch event.
					this.dispatchFormEvent(element, FORM_EVENTS.AFTER_FORM_SUBMIT_ERROR);

					// Set global msg.
					this.setGlobalMsg(element, response.message, 'error');
				}

				// Fatal errors, trigger bugsnag.
				if (response.status === 'error_fatal') {
					// Dispatch event.
					this.dispatchFormEvent(element, FORM_EVENTS.AFTER_FORM_SUBMIT_ERROR_FATAL);

					// Set global msg.
					this.setGlobalMsg(element, response.message, 'error');

					// Trigger error.
					throw new Error(JSON.stringify(response));
				}

				// Validate fields error.
				if (response.status === 'error_validation') {
					// Dispatch event.
					this.dispatchFormEvent(element, FORM_EVENTS.AFTER_FORM_SUBMIT_ERROR_VALIDATION);

					// Output field errors.
					this.outputErrors(element, response.validation);
				}

				// Hide global msg in any case after some time.
				setTimeout(() => {
					this.hideGlobalMsg(element);
				}, parseInt(this.hideGlobalMessageTimeout, 10));

				// Dispatch event.
				this.dispatchFormEvent(element, FORM_EVENTS.AFTER_FORM_SUBMIT_END);
			});
	}

	// Build form data object.
	getFormData = (element, singleSubmit = false) => {
		const formData = new FormData();

		const groups = element.querySelectorAll(`${this.groupSelector}`);

		// Check if we are saving group items in one key.
		if (groups.length && !singleSubmit) {
			for (const [key, group] of Object.entries(groups)) { // eslint-disable-line no-unused-vars
				const groupId = group.getAttribute(this.DATA_ATTR_FIELD_ID);
				const groupInner = group.querySelectorAll(`
					${this.groupInnerSelector} input,
					${this.groupInnerSelector} select,
					${this.groupInnerSelector} textarea
				`);

				if (groupInner.length) {
					const groupInnerItems = {};

					for (const [key, groupInnerItem] of Object.entries(groupInner)) { // eslint-disable-line no-unused-vars
						const {
							id,
							value,
							disabled,
						} = groupInnerItem;

						if (disabled) {
							continue;
						}
		
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

		const formType = element.getAttribute(this.DATA_ATTR_FORM_TYPE);

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
			if (type === 'file') {
				// Default use normal files form input.
				let fileList = files;

				// If custom file use files got from the global object of files uploaded.
				if (this.isCustom(item)) {
					fileList = this.files[id] ?? [];
				}

				// Loop files and append.
				if (fileList.length) {
					for (const [key, file] of Object.entries(fileList)) {
						formData.append(`${id}[${key}]`, file);
					}
				} else {
					formData.append(`${id}[0]`, JSON.stringify({}));
				}
			} else {
				// Output/append all fields.
				formData.append(id, JSON.stringify(data));
			}
		}

		// Add form ID field.
		formData.append('es-form-post-id', JSON.stringify({
			value: element.getAttribute(this.DATA_ATTR_FORM_POST_ID),
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

			item?.closest(this.fieldSelector).classList.add(this.CLASS_HAS_ERROR);

			if (item !== null) {
				item.innerHTML = fields[key];
			}
		}

		// Scroll to element if the condition is right.
		if (typeof fields !== 'undefined' && this.formDisableScrollToFieldOnError !== '1') {
			const firstItem = Object.keys(fields)[0];

			this.scrollToElement(element.querySelector(`${this.errorSelector}[data-id="${firstItem}"]`).parentElement);
		}
	}

	// Reset form values if the condition is right.
	resetForm = (element) => {
		if (this.formResetOnSuccess) {
			element.reset();

			const formId = element.getAttribute(this.DATA_ATTR_FORM_POST_ID);

			// Unset the choices in the submitted form.
			if (this.customSelects[formId]) {
				this.customSelects[formId].forEach((item) => {
					item.setChoiceByValue('');
				});
			}

			// Unset the choices in the submitted form.
			if (this.customFiles[formId]) {
				this.customFiles[formId].forEach((item) => {
					item.removeAllFiles();
				});
			}
		}

		const fields = element.querySelectorAll(this.fieldSelector);

		[...fields].forEach((item) => {
			item.classList.remove(this.CLASS_FILLED);
			item.classList.remove(this.CLASS_ACTIVE);
			item.classList.remove(this.CLASS_HAS_ERROR);
		});
	}

	// Reset for in general.
	reset = (element) => {
		const items = element.querySelectorAll(this.errorSelector);
		[...items].forEach((item) => {
			item.innerHTML = '';
		});

		// Reset all error classes on fields.
		element.querySelectorAll(`.${this.CLASS_HAS_ERROR}`).forEach((element) => element.classList.remove(this.CLASS_HAS_ERROR));

		this.unsetGlobalMsg(element);
	}

	// Show loader.
	showLoader = (element) => {
		const loader = element.querySelector(this.loaderSelector);

		element?.classList?.add(this.CLASS_LOADING);

		if (!loader) {
			return;
		}

		loader.classList.add(this.CLASS_ACTIVE);
	}

	// Hide loader.
	hideLoader = (element) => {
		const loader = element.querySelector(this.loaderSelector);

		setTimeout(() => {
			element?.classList?.remove(this.CLASS_LOADING);

			if (!loader) {
				return;
			}

			loader.classList.remove(this.CLASS_ACTIVE);
		}, parseInt(this.hideLoadingStateTimeout, 10));
	}

	// Set global message.
	setGlobalMsg = (element, msg, status) => {
		if(element.hasAttribute(FORM_DATA_ATTRIBUTES.DATA_ATTR_SUCCESS_REDIRECT) && status === 'success') {
			return;
		}

		const messageContainer = element.querySelector(this.globalMsgSelector);

		if (!messageContainer) {
			return;
		}

		messageContainer.classList.add(this.CLASS_ACTIVE);
		messageContainer.dataset.status = status;
		messageContainer.innerHTML = `<span>${msg}</span>`;

		// Scroll to msg if the condition is right.
		if (status === 'success' && this.formDisableScrollToGlobalMessageOnSuccess !== '1') {
			this.scrollToElement(messageContainer);
		}
	}

	// Unset global message.
	unsetGlobalMsg(element) {
		const messageContainer = element.querySelector(this.globalMsgSelector);

		if (!messageContainer) {
			return;
		}

		messageContainer.classList.remove(this.CLASS_ACTIVE);
		messageContainer.dataset.status = '';
		messageContainer.innerHTML = '';
	}

	// Hide global message.
	hideGlobalMsg(element) {
		const messageContainer = element.querySelector(this.globalMsgSelector);

		if (!messageContainer) {
			return;
		}

		messageContainer.classList.remove(this.CLASS_ACTIVE);
	}

	// Submit GTM event.
	gtmSubmit(element) {
		const eventName = element.getAttribute(this.DATA_ATTR_TRACKING_EVENT_NAME);

		if (eventName) {
			const gtmData = this.getGtmData(element, eventName);

			if (window?.dataLayer && gtmData?.event) {
				this.dispatchFormEvent(element, FORM_EVENTS.BEFORE_GTM_DATA_PUSH);
				window.dataLayer.push(gtmData);
			}
		}
	}

	// Build GTM data for the data layer.
	getGtmData(element, eventName) {
		const items = element.querySelectorAll(`[${this.DATA_ATTR_TRACKING}]`);
		const data = {};

		if (!items.length) {
			return {};
		}

		[...items].forEach((item) => {
			const tracking = item.getAttribute(this.DATA_ATTR_TRACKING);

			if (tracking) {
				// Check if you have this data attr and if so use select label.
				if (item.hasAttribute(this.DATA_ATTR_TRACKING_SELECT_LABEL)) {
					data[tracking] = item.selectedOptions[0].label;
				} else {
					data[tracking] = item.value;
				}
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
		const event = new CustomEvent(`esForms${name}`, {
			bubbles: true
		});

		element.dispatchEvent(event);
	}

	// Setup Regular field.
	setupInputField = (input) => {
		this.preFillOnInit(input);

		input.addEventListener('focus', this.onFocusEvent);
		input.addEventListener('blur', this.onBlurEvent);
	}

	// Setup Select field.
	setupSelectField = (select, formId) => {
		const option = select.querySelector('option');

		this.preFillOnInit(option);

		if (this.isCustom(select)) {
			const choices = new Choices(select, {
				searchEnabled: false,
				shouldSort: false,
			});

			this.customSelects[formId].push(choices);

			select.closest('.choices').addEventListener('focus', this.onFocusEvent);
			select.closest('.choices').addEventListener('blur', this.onBlurEvent);
		} else {
			select.addEventListener('focus', this.onFocusEvent);
			select.addEventListener('blur', this.onBlurEvent);
		}
	}

	// Setup Textarea field.
	setupTextareaField = (textarea) => {
		this.preFillOnInit(textarea);

		textarea.addEventListener('focus', this.onFocusEvent);
		textarea.addEventListener('blur', this.onBlurEvent);

		if (this.isCustom(textarea)) {
			textarea.setAttribute('rows', '1');
			textarea.setAttribute('cols', '');

			autosize(textarea);
		}
	}

	// Setup file single field.
	setupFileField = (file, formId) => {
		if (this.isCustom(file)) {
			// Init dropzone.
			const myDropzone = new Dropzone(
				file.closest(this.fieldSelector),
				{
					url: "/",
					addRemoveLinks: true,
					autoProcessQueue: false,
					autoDiscover: false,
					maxFiles: !file.multiple ? 1 : null,
					dictRemoveFile: this.fileCustomRemoveLabel,
				}
			);

			this.customFiles[formId].push(myDropzone);

			// On add files.
			myDropzone.on("addedfiles", () => {
				this.files[file.id] = myDropzone.files;
			});

			// On add one file.
			myDropzone.on("addedfile", (file) => {
				setTimeout(() => {
					file.previewTemplate.classList.add(this.CLASS_ACTIVE);
				}, 200);

				setTimeout(() => {
					file.previewTemplate.classList.add(this.CLASS_FILLED);
				}, 1200);
			});

			// On remove files.
			myDropzone.on("removedfile", () => {
				this.files[file.id] = myDropzone.files;
			});

			// Trigger on wrap click.
			file.nextElementSibling.addEventListener('click', (event) => {
				event.preventDefault();
				event.stopPropagation();
				myDropzone.hiddenFileInput.click();
			});

			const button = file.parentNode.querySelector('a');

			button.addEventListener('focus', this.onFocusEvent);
			button.addEventListener('blur', this.onBlurEvent);
		}
	}

	// // Prefill inputs active/filled on init.
	preFillOnInit = (input) => {
		if (input.type === 'checkbox' || input.type === 'radio') {
			if (input.checked) {
				input.closest(this.fieldSelector).classList.add(this.CLASS_FILLED);
			}
		} else {
			if (input.value && input.value.length) {
				input.closest(this.fieldSelector).classList.add(this.CLASS_FILLED);
			}
		}
	}

	// On Focus event for regular fields.
	onFocusEvent = (event) => {
		event.target.closest(this.fieldSelector).classList.add(this.CLASS_ACTIVE);
	}

	// On Blur generic method. Check for length of value.
	onBlurEvent = (event) => {
		const element = event.target;
		const field = element.closest(this.fieldSelector);

		let toCheck = element;
		let condition = false;
		let type = element.type;

		if (element.classList.contains('choices')) {
			type = 'choices';
		}

		switch (type) {
			case 'radio':
				condition = element.checked;
				break;
			case 'checkbox':
				condition = field.querySelectorAll('input:checked').length;
				break;
			case 'select':
				toCheck = element.options[element.options.selectedIndex];

				condition = toCheck.value && toCheck.value.length;
				break;
			case 'choices':
				toCheck = element.querySelector('option');

				condition = toCheck.value && toCheck.value.length;
				break;
			default:
				condition = element.value && element.value.length;
				break;
		}

		if (condition) {
			field.classList.remove(this.CLASS_ACTIVE);
			field.classList.add(this.CLASS_FILLED);
		} else {
			field.classList.remove(this.CLASS_ACTIVE, this.CLASS_FILLED);
		}
	}

	// Determine if field is custom type or normal.
	isCustom(item) {
		return item.closest(this.fieldSelector).classList.contains(this.customSelector.substring(1)) && !this.formIsAdmin;
	}
}
