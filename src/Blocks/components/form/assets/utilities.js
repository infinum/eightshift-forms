/* global esFormsLocalization */

import { State } from './state';
import { Data } from './data';

/**
 * Main Utilities class.
 */
export class Utils {
	constructor(options = {}) {
		this.data = new Data(options);
		this.state = new State(options);

		// Set all public methods.
		this.publicMethods();
	}

	////////////////////////////////////////////////////////////////
	// State callback
	////////////////////////////////////////////////////////////////



	////////////////////////////////////////////////////////////////
	// Public methods
	////////////////////////////////////////////////////////////////

	// Unset global message.
	unsetGlobalMsg(element) {
		const messageContainer = element.querySelector(this.data.globalMsgSelector);

		if (!messageContainer) {
			return;
		}

		messageContainer.classList.remove(this.data.SELECTORS.CLASS_ACTIVE);
		messageContainer.dataset.status = '';
		messageContainer.innerHTML = '';
	}

	// Reset form in general.
	reset(formId) {
		const form = this.getStateFormElement(formId);

		// Reset all error classes on fields.
		form.querySelectorAll(`${this.data.fieldSelector}.${this.data.SELECTORS.CLASS_HAS_ERROR}`).forEach((item) => {
			item.classList.remove(this.data.SELECTORS.CLASS_HAS_ERROR);

			const value = item.querySelector(this.data.errorSelector);

			if (value) {
				value.innerHTML = ''
			}
		});

		this.unsetGlobalMsg(form);
	}

	// Remove one field error by name.
	removeFieldErrorByName(name, formId) {
		const data = this.state.getStateElement(name, formId);

		const {
			field,
		} = data;

		if (field.classList.contains(this.data.SELECTORS.CLASS_HAS_ERROR)) {
			field.classList.remove(this.data.SELECTORS.CLASS_HAS_ERROR);

			const value = field.querySelector(this.data.errorSelector);

			if (value) {
				value.innerHTML = ''
			}
		}
	}

	// Dispatch custom event.
	dispatchFormEvent(formId, name, detail) {
		const options = {
			bubbles: true,
		};

		if (detail) {
			options['detail'] = detail;
		}

		this.state.getStateFormElement(formId).dispatchEvent(new CustomEvent(name, options));
	}

	// Scroll to specific element.
	scrollToElement(element) {
		if (element !== null) {
			element.scrollIntoView({block: 'start', behavior: 'smooth'});
		}
	}

	// Show loader.
	showLoader(formId) {
		const loader = this.state.getStateFormLoader(formId);
		const form = this.state.getStateFormElement(formId);

		form.classList.add(this.data.SELECTORS.CLASS_LOADING);

		if (!loader) {
			return;
		}

		loader.classList.add(this.data.SELECTORS.CLASS_ACTIVE);
	}

	// Output all error for fields.
	outputErrors(element, fields) {
		if (typeof fields === 'undefined') {
			return;
		}

		// Set error classes and error text on fields which have validation errors.
		for (const [key] of Object.entries(fields)) {
			const item = element.querySelector(`${this.data.errorSelector}[data-id="${key}"]`);

			item?.closest(this.data.fieldSelector).classList.add(this.data.SELECTORS.CLASS_HAS_ERROR);

			if (item !== null) {
				item.innerHTML = fields[key];
			}
		}

		// Scroll to element if the condition is right.
		if (Object.entries(fields).length > 0 && !this.data.SETTINGS.FORM_DISABLE_SCROLL_TO_FIELD_ON_ERROR) {
			const firstItem = Object.keys(fields)[0];

			this.scrollToElement(element.querySelector(`${this.data.errorSelector}[data-id="${firstItem}"]`).parentElement);
		}
	}

	// Hide loader.
	hideLoader(formId) {
		const loader = this.state.getStateFormLoader(formId);
		const form = this.state.getStateFormElement(formId);

		setTimeout(() => {
			form?.classList?.remove(this.data.SELECTORS.CLASS_LOADING);

			if (!loader) {
				return;
			}

			loader.classList.remove(this.data.SELECTORS.CLASS_ACTIVE);
		}, parseInt(this.data.SETTINGS.HIDE_LOADING_STATE_TIMEOUT, 10));
	}

	// Set global message.
	setGlobalMsg(formId, msg, status) {
		const messageContainer = this.state.getStateFormGlobalMsg(formId);

		if (!messageContainer) {
			return;
		}

		const headingSuccess = messageContainer?.getAttribute(this.data.DATA_ATTRIBUTES.globalMsgHeadingSuccess);
		const headingError = messageContainer?.getAttribute(this.data.DATA_ATTRIBUTES.globalMsgHeadingError);

		messageContainer.classList.add(this.data.SELECTORS.CLASS_ACTIVE);
		messageContainer.dataset.status = status;

		// Scroll to msg if the condition is right.
		if (status === 'success') {
			if (!this.data.SETTINGS.FORM_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS) {
				this.scrollToElement(messageContainer);
			}

			if (headingSuccess) {
				messageContainer.innerHTML = `<div><div>${headingSuccess}</div><span>${msg}</span></div>`;
			} else {
				messageContainer.innerHTML = `<div><span>${msg}</span></div>`;
			}
		} else {
			if (headingError) {
				messageContainer.innerHTML = `<div><div>${headingError}</div><span>${msg}</span></div>`;
			} else {
				messageContainer.innerHTML = `<div><span>${msg}</span></div>`;
			}
		}
	}

	// Hide global message.
	hideGlobalMsg(element) {
		const messageContainer = element.querySelector(this.data.globalMsgSelector);

		if (!messageContainer) {
			return;
		}

		messageContainer.classList.remove(this.data.SELECTORS.CLASS_ACTIVE);
	}

	// Build GTM data for the data layer.
	getGtmData(formId, eventName, formData) {
		const form = this.state.getStateFormElement(formId);

		const output = {};
		const data = {};
		for (const [key, value] of formData) { // eslint-disable-line no-unused-vars
			// Skip for files.
			if (typeof value !== 'string') {
				continue;
			}

			const itemValue = JSON.parse(value);
			const item = form.querySelector(`${this.data.fieldSelector} [name="${itemValue.name}"]`);
			const trackingValue = item?.getAttribute(this.data.DATA_ATTRIBUTES.tracking);
			if (!trackingValue) {
				continue;
			}

			if (trackingValue in data) {
				if (itemValue.value) {
					data[trackingValue].push(itemValue.value);
				}
			} else {
				switch (itemValue.type) {
					case 'checkbox':
					case 'radio':
						data[trackingValue] = itemValue.value ? [itemValue.value] : [];
						break;
					case 'select-one':
						data[trackingValue] = item.selectedOptions[0]?.label;
						break;
					default:
						data[trackingValue]= itemValue.value;
						break;
				}
			}
		}

		for (const [key, value] of Object.entries(data)) {
			if (Array.isArray(value)) {
				switch (value.length) {
					case 0:
						output[key] = false;
						break;
					case 1:
						if (value[0] === 'on') {
							output[key] = true;
						} else {
							output[key] = value;
						}
						break;
					default:
						output[key] = value;
						break;
				}
			} else {
				output[key] = value;
			}
		}

		return Object.assign({}, { event: eventName, ...output });
	}

	// Submit GTM event.
	gtmSubmit(formId, formData, status, errors) {
		const form = this.state.getStateFormElement(formId);
		const eventName = form.getAttribute(this.data.DATA_ATTRIBUTES.trackingEventName);

		if (eventName) {
			const gtmData = this.getGtmData(formId, eventName, formData);

			const additionalData = JSON.parse(form.getAttribute(this.data.DATA_ATTRIBUTES.trackingAdditionalData));
			let additionalDataItems = additionalData?.general;

			if (status === 'success') {
				additionalDataItems = {
					...additionalDataItems,
					...additionalData?.success,
				};
			}

			if (status === 'error') {
				additionalDataItems = {
					...additionalDataItems,
					...additionalData?.error,
				};
			}

			if (errors) {
				for (const [key, value] of Object.entries(additionalDataItems)) {
					if (value === '{invalidFieldsString}') {
						additionalDataItems[key] = Object.keys(errors).join(',');
					}
	
					if (value === '{invalidFieldsArray}') {
						additionalDataItems[key] = Object.keys(errors);
					}
				}
			}

			if (window?.dataLayer && gtmData?.event) {
				this.dispatchFormEvent(formId, this.data.EVENTS.BEFORE_GTM_DATA_PUSH);
				window.dataLayer.push({...gtmData, ...additionalDataItems});
			}
		}
	}

	// Prefill inputs active/filled on init.
	setFieldVisualState(data) {
		const {
			type,
			value,
			field,
		} = data;

		let condition = false;

		switch (type) {
			case 'checkbox':
				condition = Object.values(value).filter((item) => item !== '').length > 0;
				break
			default:
				condition = value && value.length;
				break;
		}

		if (condition) {
			field.classList.remove(this.data.SELECTORS.CLASS_ACTIVE);
			field.classList.add(this.data.SELECTORS.CLASS_FILLED);
		} else {
			field.classList.remove(this.data.SELECTORS.CLASS_ACTIVE, this.data.SELECTORS.CLASS_FILLED);
		}
	}

	// Reset form values if the condition is right.
	resetForm(element) {
		if (this.data.SETTINGS.FORM_RESET_ON_SUCCESS) {
			const formId = element.getAttribute(this.data.DATA_ATTRIBUTES.formPostId);

			// Unset the choices in the submitted form.
			if (this.getState([this.state.SELECTS], formId)) {
				this.getState([this.state.SELECTS], formId).forEach((item) => {
					item.setChoiceByValue(item?.passedElement?.element.getAttribute(this.data.DATA_ATTRIBUTES.selectInitial));
					item.clearInput();
					item.unhighlightAll();
				});
			}

			// Unset the files in the submitted form.
			if (this.getState([this.state.FILES], formId)) {
				this.getState([this.state.FILES], formId).forEach((item, index) => {
					item.removeAllFiles();
				});
			}

			const fields = element.querySelectorAll(this.data.fieldSelector);

			[...fields].forEach((item) => {
				item.classList.remove(this.data.SELECTORS.CLASS_FILLED);
				item.classList.remove(this.data.SELECTORS.CLASS_ACTIVE);
				item.classList.remove(this.data.SELECTORS.CLASS_HAS_ERROR);
			});

			const inputs = element.querySelectorAll(`${this.data.inputSelector}, ${this.data.textareaSelector}`);
			[...inputs].forEach((item) => {
				item.value = '';
				item.checked = false;
			});

			// Remove focus from last input.
			document.activeElement.blur();

			// Dispatch event.
			this.dispatchFormEvent(element, this.data.EVENTS.AFTER_FORM_SUBMIT_RESET);
		}
	}

	// Redirect to url and update url params from from data.
	redirectToUrl(element, formData) {
		let redirectUrl = element.getAttribute(this.data.DATA_ATTRIBUTES.successRedirect) ?? '';
		const downloads = element.getAttribute(this.data.DATA_ATTRIBUTES.downloads) ?? '';
		const variation = element.getAttribute(this.data.DATA_ATTRIBUTES.successRedirectVariation) ?? '';

		// Replace string templates used for passing data via url.
		for (var [key, val] of formData.entries()) { // eslint-disable-line no-unused-vars
			if (typeof val === 'string') {
				const { value, name } = JSON.parse(val);
				redirectUrl = redirectUrl.replaceAll(`{${name}}`, encodeURIComponent(value));
			}
		}

		const url = new URL(redirectUrl);

		if (downloads) {
			url.searchParams.append('es-downloads', downloads);
		}

		if (variation) {
			url.searchParams.append('es-variation', variation);
		}

		this.redirectToUrlByRefference(url.href, element);
	}

	// Redirect to url by provided path.
	redirectToUrlByRefference(redirectUrl, element, reload = false) {
		this.dispatchFormEvent(element, this.data.EVENTS.AFTER_FORM_SUBMIT_SUCCESS_BEFORE_REDIRECT, redirectUrl);

		if (!this.data.SETTINGS.FORM_DISABLE_NATIVE_REDIRECT_ON_SUCCESS) {
			// Do the actual redirect after some time.
			setTimeout(() => {
				window.location = redirectUrl;

				if (reload) {
					window.location.reload();
				}
			}, parseInt(this.data.SETTINGS.REDIRECTION_TIMEOUT, 10));
		}
	}

	// Check if captcha is used.
	isCaptchaUsed() {
		return Boolean(this.data.SETTINGS.CAPTCHA?.['siteKey']);
	}

	// Check if captcha init is used.
	isCaptchaInitUsed() {
		return Boolean(this.data.SETTINGS.CAPTCHA?.['loadOnInit']);
	}

	// Check if captcha badge is hidden.
	isCaptchaHideBadgeUsed() {
		return Boolean(this.data.SETTINGS.CAPTCHA?.['hideBadge']);
	}

	// Check if captcha enterprise is used.
	isCaptchaEnterprise() {
		return Boolean(this.data.SETTINGS.CAPTCHA?.['isEnterprise']);
	}

	// Check if form is fully loaded.
	isFormLoaded(formId) {
		const interval = setInterval(() => {
			if (this.state.getStateFilteredBykey(this.state.ELEMENTS, this.state.LOADED, false, formId).length === 0) {
				clearInterval(interval);

				this.state.setState([this.state.ISLOADED], true, formId);

				// Triger event that form is fully loaded.
				this.dispatchFormEvent(this.state.getStateFormElement(formId), this.data.EVENTS.FORM_JS_LOADED);
			}
		}, 100);
	}

	// Check if form is loaded in admin.
	isFormAdmin() {
		return this.data.formIsAdmin;
	}

	// Append common form data items.
	getCommonFormDataItems(params) {
		return [
			{
				key: this.data.FORM_PARAMS.postId,
				value: JSON.stringify({
					name: this.data.FORM_PARAMS.postId,
					value: params?.formId,
					type: 'hidden',
				}),
			},
			{
				key: this.data.FORM_PARAMS.type,
				value: JSON.stringify({
					name: this.data.FORM_PARAMS.type,
					value: params?.formType,
					type: 'hidden',
				}),
			}
		];
	}

	// Return field element by name.
	getFieldByName(element, name) {
		return element.querySelector(`${this.data.fieldSelector}[${this.data.DATA_ATTRIBUTES.fieldName}="${name}"]`);
	}

	// Return field element by value.
	getCheckboxByValue(element, value) {
		return element.querySelector(`${this.data.fieldSelector} input[type="checkbox"][value="${value}"]`);
	}

	////////////////////////////////////////////////////////////////
	// Events callback
	////////////////////////////////////////////////////////////////

	// On Focus event for regular fields.
	onFocusEvent = (event) => {
		const field = this.state.getFormFieldElementByChild(event.target);
		const name = field.getAttribute(this.data.DATA_ATTRIBUTES.fieldName);

		this.state.getStateElementField(name, this.state.getFormIdByElement(event.target)).classList.add(this.data.SELECTORS.CLASS_ACTIVE);
	};

	onChangeEvent = (event) => {
		const field = this.state.getFormFieldElementByChild(event.target);
		const formId = this.state.getFormIdByElement(event.target);
		const type = field.getAttribute(this.data.DATA_ATTRIBUTES.fieldType);
		const name = field.getAttribute(this.data.DATA_ATTRIBUTES.fieldName);

		this.state.setValues(event.target, this.state.getFormIdByElement(event.target));

		if (!this.state.getStateFormPhoneDisablePicker(formId) && this.state.getStateFormPhoneUseSync(formId)) {
			if (type === 'country') {
				const country = this.state.getStateElementValueCountry(name, formId);
				[...this.state.getStateFilteredBykey(this.state.ELEMENTS, this.state.INTERNALTYPE, 'tel', formId)].forEach((tel) => {
					tel[this.state.CUSTOM].setChoiceByValue(country.number);
				});
			}

			if (type === 'phone') {
				const phone = this.state.getStateElementValueCountry(name, formId);
				[...this.state.getStateFilteredBykey(this.state.ELEMENTS, this.state.INTERNALTYPE, 'country', formId)].forEach((country) => {
					country[this.state.CUSTOM].setChoiceByValue(phone.label);
				});
			}
		}
	}

	onInputEvent = (event) => {
		this.state.setValues(event.target, this.state.getFormIdByElement(event.target));
	}

	// On Blur generic method. Check for length of value.
	onBlurEvent = (event) => {
		const field = this.state.getFormFieldElementByChild(event.target);
		const name = field.getAttribute(this.data.DATA_ATTRIBUTES.fieldName);

		this.setFieldVisualState(this.state.getStateElement(name, this.state.getFormIdByElement(event.target)))
	};

	////////////////////////////////////////////////////////////////
	// Private methods - not shared to the public window object.
	////////////////////////////////////////////////////////////////

	/**
	 * Set all public methods.
	 *
	 * @private
	 */
	 publicMethods() {
		if (typeof window?.[this.data.prefix] === 'undefined') {
			window[this.data.prefix] = {};
		}

		if (typeof window?.[this.data.prefix]?.utils === 'undefined') {
			window[this.data.prefix].utils = {
				formIsAdmin: this.formIsAdmin,
				formSubmitRestApiUrl: this.formSubmitRestApiUrl,

				formSelectorPrefix: this.formSelectorPrefix,

				formSelector: this.formSelector,
				submitSingleSelector: this.submitSingleSelector,
				stepSelector: this.stepSelector,
				errorSelector: this.data.errorSelector,
				loaderSelector: this.data.loaderSelector,
				globalMsgSelector: this.data.globalMsgSelector,
				groupSelector: this.groupSelector,
				fieldSelector: this.data.fieldSelector,
				dateFieldSelector: this.dateFieldSelector,
				countryFieldSelector: this.countryFieldSelector,
				inputSelector: this.data.inputSelector,
				textareaSelector: this.data.textareaSelector,
				selectSelector: this.selectSelector,
				fileSelector: this.fileSelector,

				selectClassName: this.selectClassName,

				FORM_PARAMS: this.data.FORM_PARAMS,
				DATA_ATTRIBUTES: this.data.DATA_ATTRIBUTES,
				SETTINGS: this.data.SETTINGS,
				EVENTS: this.data.EVENTS,
				SELECTORS: this.SELECTORS,
				DELIMITER: this.DELIMITER,
				CONDITIONAL_TAGS_OPERATORS: this.CONDITIONAL_TAGS_OPERATORS,
				CONDITIONAL_TAGS_ACTIONS: this.CONDITIONAL_TAGS_ACTIONS,
				CONDITIONAL_TAGS_LOGIC: this.CONDITIONAL_TAGS_LOGIC,

				FORMS_STATE: this.FORMS_STATE,

				setFormStateInitial: (formId) => {
					return this.setFormStateInitial(formId);
				},
				getFormStateByName: (key, name, formId) => {
					return this.getFormStateByName(key, name, formId);
				},
				unsetGlobalMsg: (element) => {
					this.unsetGlobalMsg(element);
				},
				reset: (element) => {
					this.reset(element);
				},
				removeFieldErrorByName: (element, name) => {
					this.removeFieldErrorByName(element, name);
				},
				dispatchFormEvent: (element, name) => {
					this.dispatchFormEvent(element, name);
				},
				scrollToElement: (element) => {
					this.scrollToElement(element);
				},
				showLoader: (element) => {
					this.showLoader(element);
				},
				outputErrors: (element, fields) => {
					this.outputErrors(element, fields);
				},
				hideLoader: (element) => {
					this.hideLoader(element);
				},
				setGlobalMsg: (element, msg, status) => {
					this.setGlobalMsg(element, msg, status);
				},
				hideGlobalMsg: (element) => {
					this.hideGlobalMsg(element);
				},
				getGtmData: (element, eventName, formData) => {
					return this.getGtmData(element, eventName, formData);
				},
				gtmSubmit: (element, formData, status, errors) => {
					this.gtmSubmit(element, formData, status, errors);
				},
				setFieldVisualState: (input, type) => {
					this.setFieldVisualState(input, type);
				},
				resetForm: (element) => {
					this.resetForm(element);
				},
				redirectToUrl: (element, formData) => {
					this.redirectToUrl(element, formData);
				},
				redirectToUrlByRefference: (redirectUrl, element, reload = false) => {
					this.redirectToUrlByRefference(redirectUrl, element, reload);
				},
				isCaptchaUsed: () => {
					this.isCaptchaUsed();
				},
				isCaptchaInitUsed: () => {
					return this.isCaptchaInitUsed();
				},
				isCaptchaHideBadgeUsed: () => {
					return this.isCaptchaHideBadgeUsed();
				},
				isCaptchaEnterprise: () => {
					return this.isCaptchaEnterprise();
				},
				isFormLoaded: (formId, element, selectsLength, textareaLenght, filesLength) => {
					this.isFormLoaded(formId, element, selectsLength, textareaLenght, filesLength);
				},
				isFormAdmin: () => {
					return this.isFormAdmin();
				},
				getCommonFormDataItems: (params) => {
					return this.getCommonFormDataItems(params);
				},
				getFieldByName: (element, name) => {
					return this.getFieldByName(element, name);
				},
				onFocusEvent: (event) => {
					this.onFocusEvent(event);
				},
				onBlurEvent: (event) => {
					this.onBlurEvent(event);
				},
			}
		}
	}
}
