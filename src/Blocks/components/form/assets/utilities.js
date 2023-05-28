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

	// Reset form in general.
	resetErrors(formId) {
		for (const [name] of this.state.getStateElements(formId)) {
			this.unsetFieldError(name, formId)
		};

		this.unsetErrorGlobal(formId);
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
	scrollToElement(name, formId) {
		this.state.getStateElementField(name, formId).scrollIntoView({block: 'start', behavior: 'smooth'});
	}

	scrollToErrorGlobal(formId) {
		this.state.getStateFormErrorGlobalElement(formId).scrollIntoView({block: 'start', behavior: 'smooth'});
	}

	// Show loader.
	showLoader(formId) {
		this.state.getStateFormElement(formId).classList.add(this.data.SELECTORS.CLASS_LOADING);
		this.state.getStateFormLoader(formId).classList.add(this.data.SELECTORS.CLASS_ACTIVE);
	}

	// Remove one field error by name.
	unsetFieldError(name, formId) {
		const error = this.state.getStateElementError(name, formId);

		error.classList.remove(this.data.SELECTORS.CLASS_HAS_ERROR);
		error.innerHTML = '';
	}

	setFieldError(name, msg, formId) {
		const error = this.state.getStateElementError(name, formId);

		error.classList.add(this.data.SELECTORS.CLASS_HAS_ERROR);
		error.innerHTML = msg;
	}

	// Output all error for fields.
	outputErrors(formId, data) {
		for (const [name, msg] of Object.entries(data)) {
			this.setFieldError(name, msg, formId);
		};

		// Scroll to element if the condition is right.
		const firstItemWithErrorName = Object.values(esForms.state.form_4069.elements).filter((item) => item.hasError === false)?.[0]?.name;
		if (firstItemWithErrorName) {
			this.scrollToElement(firstItemWithErrorName, formId);
		}
	}

	// Hide loader.
	hideLoader(formId) {
		setTimeout(() => {
			this.state.getStateFormElement(formId).classList.remove(this.data.SELECTORS.CLASS_LOADING);
			this.state.getStateFormLoader(formId).classList.remove(this.data.SELECTORS.CLASS_ACTIVE);
		}, parseInt(this.data.SETTINGS.HIDE_LOADING_STATE_TIMEOUT, 10));
	}

	// Unset global message.
	unsetErrorGlobal(formId) {
		const messageContainer = this.state.getStateFormErrorGlobalElement(formId);

		console.log(messageContainer);

		this.state.setState([this.state.FORM, this.state.ERROR_GLOBAL, this.state.STATUS], '', formId);
		this.state.setState([this.state.FORM, this.state.ERROR_GLOBAL, this.state.MSG], '', formId);

		messageContainer.classList.remove(this.data.SELECTORS.CLASS_ACTIVE);
		messageContainer.dataset.status = '';
		messageContainer.innerHTML = '';
	}

	// Set global message.
	setErrorGlobal(formId, msg, status) {
		const messageContainer = this.state.getStateFormErrorGlobalElement(formId);

		messageContainer.classList.add(this.data.SELECTORS.CLASS_ACTIVE);
		messageContainer.dataset.status = status;

		// Scroll to msg if the condition is right.
		if (status === 'success') {
			if (!this.state.getStateFormConfigDisableScrollToGlobalMsgOnSuccess(formId)) {
				this.scrollToErrorGlobal(formId);
			}

			const headingSuccess = this.state.getStateFormErrorGlobalHeadingSuccess(formId);

			if (headingSuccess) {
				messageContainer.innerHTML = `<div><div>${headingSuccess}</div><span>${msg}</span></div>`;
			} else {
				messageContainer.innerHTML = `<div><span>${msg}</span></div>`;
			}
		} else {
			const headingError = this.state.getStateFormErrorGlobalHeadingError(formId);

			if (headingError) {
				messageContainer.innerHTML = `<div><div>${headingError}</div><span>${msg}</span></div>`;
			} else {
				messageContainer.innerHTML = `<div><span>${msg}</span></div>`;
			}
		}
	}

	// Hide global message.
	setGlobalMsg(formId) {
		this.state.getStateFormErrorGlobalElement(formId).classList.remove(this.data.SELECTORS.CLASS_ACTIVE);
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
	setFieldVisualState(name, formId) {
		const type = this.state.getStateElementType(name, formId);
		const value = this.state.getStateElementValue(name, formId);
		const field = this.state.getStateElementField(name, formId);

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
	resetForm(formId) {
		if (!this.state.getStateFormConfigFormResetOnSuccess(formId)) {
			return;
		}

		for (const [name, item] of this.state.getStateElements(formId)) {
			const type = item[this.state.TYPE];
			const items = item[this.state.ITEMS];
			const custom = item[this.state.CUSTOM];

			switch (type) {
				case 'checkbox':
					[...Object.values(items)].forEach((checkboxItem) => {
						this.state.setState([this.state.ELEMENTS, name, this.state.ITEMS, checkboxItem.value, this.state.VALUE], '', formId);
						this.state.setState([this.state.ELEMENTS, name, this.state.VALUE, checkboxItem.value, this.state.VALUE], '', formId);

						this.setFieldVisualState(checkboxItem.value, formId);
					});

					this.unsetFieldError(name, formId);
					break;
				case 'file':
					this.state.setState([this.state.ELEMENTS, name, this.state.VALUE], '', formId);

					this.unsetFieldError(name, formId);
					custom.removeAllFiles();
				case 'radio':
					[...Object.values(items)].forEach((radioItem) => {
						this.state.setState([this.state.ELEMENTS, name, this.state.ITEMS, radioItem.value, this.state.VALUE], '', formId);
						this.state.setState([this.state.ELEMENTS, name, this.state.VALUE], '', formId);

						this.setFieldVisualState(radioItem.value, formId);
					});

					this.unsetFieldError(name, formId);
					break;
				case 'select':
					this.state.setState([this.state.ELEMENTS, name, this.state.VALUE], '', formId);
					this.unsetFieldError(name, formId);
					custom.clearInput();
					custom.unhighlightAll();
					break;
				default:
					this.state.setState([this.state.ELEMENTS, name, this.state.VALUE], '', formId);
					this.setFieldVisualState(name, formId);
					this.unsetFieldError(name, formId);
					break;
			}
		}

		// Remove focus from last input.
		document.activeElement.blur();

		// Dispatch event.
		this.dispatchFormEvent(formId, this.data.EVENTS.AFTER_FORM_SUBMIT_RESET);
	}

	// Redirect to url and update url params from from data.
	redirectToUrl(formId, formData) {
		let redirectUrl = this.state.getStateFormConfigSuccessRedirect(formId);

		// Replace string templates used for passing data via url.
		for (var [key, val] of formData.entries()) { // eslint-disable-line no-unused-vars
			if (typeof val === 'string') {
				const { value, name } = JSON.parse(val);
				redirectUrl = redirectUrl.replaceAll(`{${name}}`, encodeURIComponent(value));
			}
		}

		const url = new URL(redirectUrl);

		const downloads = this.state.getStateFormConfigDownloads(formId);
		if (downloads) {
			url.searchParams.append('es-downloads', downloads);
		}

		const variation = this.state.getStateFormConfigSuccessRedirectVariation(formId);
		if (variation) {
			url.searchParams.append('es-variation', variation);
		}

		this.redirectToUrlByRefference(url.href, element);
	}

	// Redirect to url by provided path.
	redirectToUrlByRefference(redirectUrl, formId, reload = false) {
		this.dispatchFormEvent(formId, this.data.EVENTS.AFTER_FORM_SUBMIT_SUCCESS_BEFORE_REDIRECT, redirectUrl);

		if (!this.state.getStateFormConfigFormDisableNativeRedirectOnSuccess(formId)) {
			// Do the actual redirect after some time.
			setTimeout(() => {
				window.location = redirectUrl;

				if (reload) {
					window.location.reload();
				}
			}, parseInt(this.state.getStateFormConfigRedirectionTimeout(formId), 10));
		}
	}

	// Check if form is fully loaded.
	isFormLoaded(formId) {
		const interval = setInterval(() => {
			if (this.state.getStateFilteredBykey(this.state.ELEMENTS, this.state.LOADED, false, formId).length === 0) {
				clearInterval(interval);

				this.state.setState([this.state.ISLOADED], true, formId);

				// Triger event that form is fully loaded.
				this.dispatchFormEvent(formId, this.data.EVENTS.FORM_JS_LOADED);
			}
		}, 100);
	}

	////////////////////////////////////////////////////////////////
	// Events callback
	////////////////////////////////////////////////////////////////

	// On Focus event for regular fields.
	onFocusEvent = (event) => {
		this.state.getStateElementField(this.state.getFormFieldElementByChild(event.target).getAttribute(this.data.DATA_ATTRIBUTES.fieldName), this.state.getFormIdByElement(event.target)).classList.add(this.data.SELECTORS.CLASS_ACTIVE);
	};

	onChangeEvent = (event) => {
		const field = this.state.getFormFieldElementByChild(event.target);
		const formId = this.state.getFormIdByElement(event.target);
		const type = field.getAttribute(this.data.DATA_ATTRIBUTES.fieldType);
		const name = field.getAttribute(this.data.DATA_ATTRIBUTES.fieldName);

		this.state.setValues(event.target, this.state.getFormIdByElement(event.target));

		if (!this.state.getStateFormConfigPhoneDisablePicker(formId) && this.state.getStateFormConfigPhoneUseSync(formId)) {
			if (type === 'country') {
				const country = this.state.getStateElementValueCountry(name, formId);
				[...this.state.getStateFilteredBykey(this.state.ELEMENTS, this.state.INTERNAL_TYPE, 'tel', formId)].forEach((tel) => {
					tel[this.state.CUSTOM].setChoiceByValue(country.number);
				});
			}

			if (type === 'phone') {
				const phone = this.state.getStateElementValueCountry(name, formId);
				[...this.state.getStateFilteredBykey(this.state.ELEMENTS, this.state.INTERNAL_TYPE, 'country', formId)].forEach((country) => {
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
		const formId = this.state.getFormIdByElement(event.target);

		this.setFieldVisualState(name, formId);
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
			}
		}
	}
}
