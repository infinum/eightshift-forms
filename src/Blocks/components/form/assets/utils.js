import { ConditionalTags } from './conditional-tags';
import { Enrichment } from './enrichment';
import { Geolocation } from './geolocation';
import { State } from './state';
import { StateEnum,
	prefix,
	setStateWindow,
	setStateValuesSelect,
	setStateValuesInput,
	setStateValuesPhoneInput,
	setStateValuesRadio,
	setStateValuesCheckbox,
	setStateValuesPhoneSelect,
	setStateValuesCountry,
} from './state/init';
import { Steps } from './step';

/**
 * Main Utilities class.
 */
export class Utils {
	constructor() {
		this.state = new State();
		this.enrichment = new Enrichment(this);
		this.conditionalTags = new ConditionalTags(this);
		this.steps = new Steps(this);
		this.geolocation = new Geolocation(this);

		this.GLOBAL_MSG_TIMEOUT_ID = undefined;

		// Set all public methods.
		this.publicMethods();
	}

	////////////////////////////////////////////////////////////////
	// Getters methods
	////////////////////////////////////////////////////////////////

	/**
	 * Get state class.
	 *
	 * @returns {State}
	 */
	getState() {
		return this.state;
	}

	/**
	 * Get enrichment class.
	 *
	 * @returns {Enrichment}
	 */
	getEnrichment() {
		return this.enrichment;
	}

	/**
	 * Get conditional tags class.
	 *
	 * @returns {ConditionalTags}
	 */
	getConditionalTags() {
		return this.conditionalTags;
	}

	/**
	 * Get steps class.
	 *
	 * @returns {Steps}
	 */
	getSteps() {
		return this.steps;
	}

	/**
	 * Get geolocation class.
	 *
	 * @returns {Geolocation}
	 */
	getGeolocation() {
		return this.geolocation;
	}

	////////////////////////////////////////////////////////////////
	// Public methods
	////////////////////////////////////////////////////////////////

	/**
	 * Reset form in general.
	 *
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	resetErrors(formId) {
		for (const [name] of this.state.getStateElements(formId)) {
			this.unsetFieldError(formId, name);
		}

		this.unsetGlobalMsg(formId);
	}

	/**
	 * Dispatch custom event.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Event name.
	 * @param {object} additional Additional data to add to event.
	 *
	 * @returns {void}
	 */
	dispatchFormEvent(formId, name, additional) {
		const options = {
			bubbles: true,
			detail: {
				[prefix]: window?.[prefix],
			}
		};

		if (!isNaN(formId)) {
			options.detail.formId = formId;
		}

		if (additional) {
			options.detail.additional = additional;
		}

		if (!isNaN(formId)) {
			this.state.getStateFormElement(formId).dispatchEvent(new CustomEvent(name, options));
		} else {
			formId.dispatchEvent(new CustomEvent(name, options));
		}
	}

	/**
	 * Scroll to specific element.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	scrollToElement(formId, name) {
		this.state.getStateElementField(name, formId).scrollIntoView({block: 'start', behavior: 'smooth'});
	}

	/**
	 * Scroll to global msg.
	 *
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	scrollToGlobalMsg(formId) {
		this.state.getStateFormGlobalMsgElement(formId).scrollIntoView({block: 'start', behavior: 'smooth'});
	}

	/**
	 * Show loader.
	 *
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	showLoader(formId) {
		this.state.getStateFormElement(formId)?.classList?.add(this.state.getStateSelector('isLoading'));
		this.state.getStateFormLoader(formId)?.classList?.add(this.state.getStateSelector('isActive'));
	}

	/**
	 * Hide loader.
	 *
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	hideLoader(formId) {
		this.state.getStateFormElement(formId)?.classList?.remove(this.state.getStateSelector('isLoading'));
		this.state.getStateFormLoader(formId)?.classList?.remove(this.state.getStateSelector('isActive'));
	}

	/**
	 * Unset all error for fields by name.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	unsetFieldError(formId, name) {
		const error = this.state.getStateElementError(name, formId);

		if (!error) {
			return;
		}

		error?.classList?.remove(this.state.getStateSelector('hasError'));
		this.state.setStateElementHasError(name, false, formId);
		error.innerHTML = '';
	}

	/**
	 * Set all error for fields.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {string} msg Error msg.
	 *
	 * @returns {void}
	 */
	setFieldError(formId, name, msg) {
		const error = this.state.getStateElementError(name, formId);
		if (!error) {
			return;
		}

		error?.classList?.add(this.state.getStateSelector('hasError'));
		this.state.setStateElementHasError(name, true, formId);
		error.innerHTML = msg;
	}

	/**
	 * Output all error for fields.
	 *
	 * @param {string} formId Form Id.
	 * @param {object} data Form response data.
	 *
	 * @returns {void}
	 */
	outputErrors(formId, data) {
		for (const [name, msg] of Object.entries(data)) {
			this.setFieldError(formId, name, msg);
		}

		// Scroll to element if the condition is right.
		const firstItemWithErrorName = this.state.getStateElementByHasError(true, formId)?.[0]?.[StateEnum.NAME];
		if (firstItemWithErrorName && !this.state.getStateSettingsDisableScrollToFieldOnError()) {
			this.scrollToElement(formId, firstItemWithErrorName);
		}
	}

	/**
	 * Unset global msg.
	 *
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	unsetGlobalMsg(formId) {
		const messageContainer = this.state.getStateFormGlobalMsgElement(formId);

		if (!messageContainer) {
			return;
		}

		messageContainer?.classList?.remove(this.state.getStateSelector('isActive'));
		messageContainer.dataset.status = '';
		messageContainer.innerHTML = '';
	}

	/**
	 * Set global msg.
	 *
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	setGlobalMsg(formId, msg, status) {
		const messageContainer = this.state.getStateFormGlobalMsgElement(formId);

		if (!messageContainer) {
			return;
		}

		messageContainer?.classList?.add(this.state.getStateSelector('isActive'));
		messageContainer.dataset.status = status;

		// Scroll to msg if the condition is right.
		if (status === 'success') {
			if (!this.state.getStateSettingsDisableScrollToGlobalMsgOnSuccess(formId)) {
				this.scrollToGlobalMsg(formId);
			}

			const headingSuccess = this.state.getStateFormGlobalMsgHeadingSuccess(formId);

			if (headingSuccess) {
				messageContainer.innerHTML = `<div><div>${headingSuccess}</div><span>${msg}</span></div>`;
			} else {
				messageContainer.innerHTML = `<div><span>${msg}</span></div>`;
			}
		} else {
			const headingError = this.state.getStateFormGlobalMsgHeadingError(formId);

			if (headingError) {
				messageContainer.innerHTML = `<div><div>${headingError}</div><span>${msg}</span></div>`;
			} else {
				messageContainer.innerHTML = `<div><span>${msg}</span></div>`;
			}
		}
	}

	/**
	 *  Build GTM data for the data layer.
	 *
	 * @param {string} formId Form Id.
	 *
	 * @returns {object}
	 */
	getGtmData(formId) {
		const output = {};
		for (const [name] of this.state.getStateElements(formId)) {
			const value = this.state.getStateElementValue(name, formId);
			const trackingName = this.state.getStateElementTracking(name, formId);
			const valueCountry = this.state.getStateElementValueCountry(name, formId);
			if (!trackingName) {
				continue;
			}

			switch (this.state.getStateElementTypeField(name, formId)) {
				case 'checkbox':
					for(const [checkName, checkValue] of Object.entries(value)) {
						const trackingCheckName = trackingName?.[checkName];

						if(!(trackingCheckName in output)) {
							output[trackingCheckName] = [];
						}

						if (checkValue) {
							output[trackingCheckName].push(checkValue);
						}
					}
					break;
				case 'phone':
					let telValue = value; // eslint-disable-line no-case-declarations

					if (!this.state.getStateFormConfigPhoneDisablePicker(formId) && value) {
						telValue = `${valueCountry.number}${value}`;
					}

					output[trackingName] = value ? telValue : '';
					break;
				default:
					output[trackingName] = value ?? '';
					break;
			}
		}

		return Object.assign({}, { event: this.state.getStateFormTrackingEventName(formId), ...output });
	}

	/**
	 * Get GTM event with data and push to dataLayer.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} status Response status.
	 * @param {object} errors Errors object.
	 *
	 * @returns {void}
	 */
	gtmSubmit(formId, status, errors) {
		const eventName = this.state.getStateFormTrackingEventName(formId);

		if (eventName) {
			const gtmData = this.getGtmData(formId);

			const additionalData = this.state.getStateFormTrackingEventAdditionalData(formId);
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
				window.dataLayer.push({...gtmData, ...additionalDataItems});

				this.dispatchFormEvent(
					formId,
					this.state.getStateEvent('afterGtmDataPush'), {
						gtmData,
						additionalDataItems,
					}
				);
			}
		}
	}

	/**
	 * Prefill inputs active/filled.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	setFieldFilledState(formId, name) {
		const type = this.state.getStateElementTypeField(name, formId);
		const value = this.state.getStateElementValue(name, formId);

		let condition = false;

		switch (type) {
			case 'checkbox':
				condition = Object.values(value).filter((item) => item !== '').length > 0;
				break;
			default:
				condition = value && value.length;
				break;
		}

		if (condition) {
			this.unsetActiveState(formId, name);
			this.setFilledState(formId, name);
		} else {
			this.unsetActiveState(formId, name);
			this.unsetFilledState(formId, name);
		}
	}

	/**
	 * Set active state.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	setFieldActiveState(formId, name) {
		this.state.getStateElementField(name, formId)?.classList?.add(this.state.getStateSelector('isActive'));
	}

	/**
	 * Unset active state.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	unsetActiveState(formId, name) {
		this.state.getStateElementField(name, formId)?.classList?.remove(this.state.getStateSelector('isActive'));
	}

	/**
	 * Set filled state.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	setFilledState(formId, name) {
		this.state.getStateElementField(name, formId)?.classList?.add(this.state.getStateSelector('isFilled'));
	}

	/**
	 * Unset filled state.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	unsetFilledState(formId, name) {
		this.state.getStateElementField(name, formId)?.classList?.remove(this.state.getStateSelector('isFilled'));
	}

	/**
	 *  Reset form values to the initi state.
	 *
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	resetForm(formId) {
		if (!this.state.getStateSettingsResetOnSuccess(formId)) {
			return;
		}

		for (const [name] of this.state.getStateElements(formId)) {
			const initial = this.state.getStateElementInitial(name, formId);

			switch (this.state.getStateElementTypeField(name, formId)) {
				case 'phone':
					this.setManualPhoneValue(
						formId,
						name,
						{
							value: initial,
						}
					);
					break;
				case 'date':
				case 'dateTime':
					this.setManualDateValue(formId, name, initial);
					break;
				case 'country':
				case 'select':
					this.setManualSelectValue(formId, name, initial);
					break;
				case 'checkbox':
					this.setManualCheckboxValue(formId, name, initial);
					break;
				case 'radio':
					this.setManualRadioValue(formId, name, initial);
					break;
				case 'file':
					this.setManualFileValue(formId, name, initial);
					break;
				default:
					this.setManualInputValue(formId, name, initial);
					break;
			}
		}

		// Remove focus from last input.
		document.activeElement.blur();

		// Dispatch event.
		this.dispatchFormEvent(formId, this.state.getStateEvent('afterFormSubmitReset'));
	}

	/**
	 * Redirect to url and update url params from from data.
	 *
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	redirectToUrl(formId) {
		let redirectUrl = this.state.getStateFormConfigSuccessRedirect(formId);

		if (!redirectUrl) {
			return;
		}

		// Replace string templates used for passing data via url.
		for(const [name] of this.state.getStateElements(formId)) {
			let value = this.state.getStateElementValue(name, formId);

			// If checkbox split multiple.
			if (this.state.getStateElementTypeField(name, formId) === 'checkbox') {
				value = Object.values(value)?.filter((n) => n);
			}

			if (!value) {
				value = '';
			}

			redirectUrl = redirectUrl.replaceAll(`{${name}}`, encodeURIComponent(value));
		}

		const url = new URL(redirectUrl);

		const downloads = this.state.getStateFormConfigDownloads(formId);
		if (downloads) {
			let downloadsName = 'all';

			for(const key of Object.keys(downloads)) {
				if (key === 'all') {
					continue;
				}

				const keyFull = key.split("=");

				if (keyFull <= 1) {
					continue;
				}

				const value = this.state.getStateElementValue(keyFull[0], formId);

				if (value === keyFull[1]) {
					downloadsName = key;
					continue;
				}
			}

			if (downloads?.[downloadsName]) {
				url.searchParams.append('es-downloads', downloads[downloadsName]);
			}
		}

		const variation = this.state.getStateFormConfigSuccessRedirectVariation(formId);
		if (variation) {
			url.searchParams.append('es-variation', variation);
		}

		this.redirectToUrlByReference(formId, url.href);
	}

	/**
	 * Redirect to url by provided path.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} redirectUrl Redirect url.
	 * @param {bool} reload Do we reload or not.
	 *
	 * @returns {void}
	 */
	redirectToUrlByReference(formId, redirectUrl, reload = false) {
		this.dispatchFormEvent(formId, this.state.getStateEvent('afterFormSubmitSuccessBeforeRedirect'), redirectUrl);

		if (!this.state.getStateSettingsDisableNativeRedirectOnSuccess(formId)) {
			// Do the actual redirect after some time.
			setTimeout(() => {
				window.location = redirectUrl;

				if (reload) {
					window.location.reload();
				}
			}, parseInt(this.state.getStateSettingsRedirectionTimeout(formId), 10));
		}
	}

	/**
	 * Check if form is fully loaded.
	 *
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	isFormLoaded(formId) {
		var iterations = 0;
		const interval = setInterval(() => {
			if (iterations >= 20) {
				clearInterval(interval);
			}

			if (this.state.getStateElementByLoaded(false, formId)?.length === 0) {
				clearInterval(interval);

				this.state.setStateFormIsLoaded(true, formId);

				// Triger event that form is fully loaded.
				this.dispatchFormEvent(formId, this.state.getStateEvent('formJsLoaded'));
			}
			iterations++;
		}, 100);
	}

	/**
	 * Get textarea json object to save.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {object}
	 */
	getSaveAsJsonFormatOutput(formId, name) {
		const output = [];
		const items = this.state.getStateElementValue(name, formId).split(/\r\n|\r|\n/);

		if (items.length) {
			items.forEach((item) => {
				if (!item) {
					return;
				}

				const innerItem = item.split(':');
				const innerOutput = [];

				if (innerItem) {
					innerItem.forEach((inner) => {
						const innerItem = inner.trim();

						if (!innerItem) {
							return;
						}

						innerOutput.push(innerItem.trim());
					});
				}

				output.push(innerOutput);
			});
		}

		return output;
	}

	/**
	 * Check if form is fully loaded.
	 *
	 * @param {object} file File object.
	 *
	 * @returns {string}
	 */
	getFileNameFromFileObject(file) {
		if (!file) {
			return '';
		}
		const fileExt = file.upload.filename.split('.').slice(-1)?.[0];

		return `${file.upload.uuid}.${fileExt}`;
	}

	/**
	 * Actions to run if api response returns wrong content type.
	 *
	 * This can happen if the API returns HTML or something else that we don't expect.
	 * Cloudflare security can return HTML.
	 *
	 * @param {mixed} response Api response.
	 * @param {string} type Function used.
	 * @param {string} formId Form Id.
	 *
	 * @throws Error.
	 *
	 * @returns {void}
	 */
	formSubmitErrorContentType(response, type, formId) {
		const contentType = response?.headers?.get('content-type');
		const status = response?.status;

		// This can happen if the API returns HTML or something else that we don't expect.
		if ((contentType && contentType.indexOf('application/json') === -1) || (status >= 500 && status <= 599)) {
			if (formId !== null) {
				// Clear all errors.
				this.resetErrors(formId);
	
				// Remove loader.
				this.hideLoader(formId);
	
				// Set global msg.
				this.setGlobalMsg(
					formId,
					this.state.getStateSettingsFormServerErrorMsg(),
					'error'
				);
	
				// Reset timeout for after each submit.
				if (typeof this.GLOBAL_MSG_TIMEOUT_ID === "number") {
					clearTimeout(this.GLOBAL_MSG_TIMEOUT_ID);
				}
	
				// Hide global msg in any case after some time.
				this.GLOBAL_MSG_TIMEOUT_ID = setTimeout(() => {
					this.unsetGlobalMsg(formId);
				}, parseInt(this.state.getStateSettingsHideGlobalMessageTimeout(formId), 10));
			}

			// Throw error.
			if (status >= 500 && status <= 599) {
				throw new Error(`API response returned the server error for this request. Function used: "${type}"`);
			} else {
				throw new Error(`API response returned the wrong content type for this request. Function used: "${type}"`);
			}
		}
	}

	/**
	 * Get selected value by custom data of select for country and phone.
	 *
	 * @param {string} type Type for field.
	 * @param {string} value Value to check.
	 * @param {object} choices Choices object.
	 *
	 * @returns {string}
	 */
	getSelectSelectedValueByCustomData(type, value, choices) {
		if (type == 'country' || type === 'phone') {
			return choices?.config?.choices?.find((item) => item?.customProperties?.[this.state.getStateAttribute('selectCountryCode')] === value)?.value;
		}

		return '';
	}

	/**
	 * Remove forms that don't have forms block.
	 *
	 * @returns {void}
	 */
	removeFormsWithMissingFormsBlock() {
		[...document.querySelectorAll(this.state.getStateSelector('form', true))].forEach((form) => {
			if (!form?.closest(this.state.getStateSelector('forms', true))) {
				form.innerHTML = this.state.getStateSettingsFormMisconfiguredMsg();
			}
		});
	}

	/**
	 * Set on focus change event.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	setOnFocus(target) {
		const formId = this.state.getFormIdByElement(target);
		const field = this.state.getFormFieldElementByChild(target);
		const name = field.getAttribute(this.state.getStateAttribute('fieldName'));

		this.setFieldActiveState(formId, name);
	}

	/**
	 * Set on blur change event.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	setOnBlur(target) {
		const formId = this.state.getFormIdByElement(target);
		const field = this.state.getFormFieldElementByChild(target);
		const name = field.getAttribute(this.state.getStateAttribute('fieldName'));

		this.setFieldFilledState(formId, name);
	}

	/**
	 * Set on user change - input.
	 *
	 * @param {object} target Field element.
	 *
	 * @returns {void}
	 */
	setOnUserChangeInput(target) {
		const formId = this.state.getFormIdByElement(target);
		const field = this.state.getFormFieldElementByChild(target);
		const name = field.getAttribute(this.state.getStateAttribute('fieldName'));

		switch (this.state.getStateElementTypeField(name, formId)) {
			case 'phone':
				setStateValuesPhoneInput(target, formId);
				break;
			case 'radio':
				setStateValuesRadio(target, formId);
				break;
			case 'checkbox':
				setStateValuesCheckbox(target, formId);
				break;
			default:
				setStateValuesInput(target, formId);
				break;
		}

		if (this.state.getStateElementHasChanged(name, formId)) {
			this.unsetFieldError(formId, name);
		}

		this.enrichment.setLocalStorageFormPrefillItem(formId, name);

		this.conditionalTags.setField(formId, name);
	}

	/**
	 * Set on user change - date.
	 *
	 * @param {object} target Field element.
	 *
	 * @returns {void}
	 */
	setOnUserChangeDate(target) {
		const formId = this.state.getFormIdByElement(target);
		const field = this.state.getFormFieldElementByChild(target);
		const name = field.getAttribute(this.state.getStateAttribute('fieldName'));

		setStateValuesInput(target, formId);

		if (this.state.getStateElementHasChanged(name, formId)) {
			this.unsetFieldError(formId, name);
		}

		this.enrichment.setLocalStorageFormPrefillItem(formId, name);
		this.setFieldFilledState(formId, name);
		this.conditionalTags.setField(formId, name);
	}

	/**
	 * Set on user change - select.
	 *
	 * @param {object} target Field element.
	 * @param {bool} disableSync Disable sync.
	 *
	 * @returns {void}
	 */
	setOnUserChangeSelect(target, disableSync = false) {
		const formId = this.state.getFormIdByElement(target);
		const field = this.state.getFormFieldElementByChild(target);
		const name = field.getAttribute(this.state.getStateAttribute('fieldName'));
		const type = this.state.getStateElementTypeField(name, formId);

		switch (type) {
			case 'phone':
				setStateValuesPhoneSelect(target, formId);
				break;
			case 'country':
				setStateValuesCountry(target, formId);
				break;
			default:
				setStateValuesSelect(target, formId);
				break;
		}

		if (this.state.getStateElementHasChanged(name, formId)) {
			this.unsetFieldError(formId, name);
		}

		if (disableSync) {
			return;
		}

		if (!this.state.getStateFormConfigPhoneDisablePicker(formId) && this.state.getStateFormConfigPhoneUseSync(formId)) {
			if (type === 'country') {
				const country = this.state.getStateElementValueCountry(name, formId);
				[...this.state.getStateElementByTypeField('phone', formId)].forEach((tel) => {
					const name = tel[StateEnum.NAME];

					this.setManualPhoneValue(formId, name, {
						prefix: country.number,
						value: this.state.getStateElementValue(name, formId),
					});
				});
			}

			if (type === 'phone') {
				const phone = this.state.getStateElementValueCountry(name, formId);
				[...this.state.getStateElementByTypeField('country', formId)].forEach((country) => {
					const name = country[StateEnum.NAME];

					this.setManualSelectValue(formId, name, phone?.label);
				});
			}
		}

		this.enrichment.setLocalStorageFormPrefillItem(formId, name);

		this.conditionalTags.setField(formId, name);

	}

	/**
	 * Set manual field value - Phone.
	 * 
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {object} value Field value.
	 * 
	 * @returns {void}
	 */
	setManualPhoneValue(formId, name, value) {
		let newValue = value?.value;

		if (typeof newValue === 'undefined') {
			newValue = '';
		}
		const input = this.state.getStateElementInput(name, formId);
		const custom = this.state.getStateElementCustom(name, formId);
		const inputSelect = this.state.getStateElementInputSelect(name, formId);

		if (input) {
			input.value = newValue;
			if (!this.state.getStateFormConfigPhoneDisablePicker(formId)) {
				custom.setChoiceByValue(value?.prefix);
				this.setOnUserChangeSelect(inputSelect, true);
			}
			this.setOnUserChangeInput(input);
			this.setOnBlur(input);
		}

		this.enrichment.setLocalStorageFormPrefillItem(formId, name);

		this.conditionalTags.setField(formId, name);
	}

	/**
	 * Set manual field value - Date.
	 * 
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {object} value Field value.
	 * 
	 * @returns {void}
	 */
	setManualDateValue(formId, name, value) {
		const custom = this.state.getStateElementCustom(name, formId);
		const input = this.state.getStateElementInput(name, formId);

		if (input) {
			custom.setDate(value, true, custom?.config?.dateFormat);
		}

		this.enrichment.setLocalStorageFormPrefillItem(formId, name);

		this.conditionalTags.setField(formId, name);
	}

	/**
	 * Set manual field value - Select/Country.
	 * 
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {object} value Field value.
	 * 
	 * @returns {void}
	 */
	setManualSelectValue(formId, name, value) {
		const custom = this.state.getStateElementCustom(name, formId);
		const input = this.state.getStateElementInput(name, formId);

		if (input) {
			if (typeof value !== 'string') {
				custom.removeActiveItems();
			}
			custom.setChoiceByValue(value);
			this.setOnUserChangeSelect(input, true);
			this.setOnBlur(input);
		}

		this.enrichment.setLocalStorageFormPrefillItem(formId, name);

		this.conditionalTags.setField(formId, name);
	}

	/**
	 * Set manual field value - Checkboxes.
	 * 
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {object} value Field value.
	 * 
	 * @returns {void}
	 */
	setManualCheckboxValue(formId, name, value) {
		Object.entries(value).forEach(([innerName, innerValue]) => {
			const innerInput = this.state.getStateElementItemsInput(name, innerName, formId);
			if (innerInput) {
				innerInput.checked = innerValue;
				this.setOnUserChangeInput(innerInput);
				this.setOnBlur(innerInput);
			}
		});

		this.enrichment.setLocalStorageFormPrefillItem(formId, name);

		this.conditionalTags.setField(formId, name);
	}

	/**
	 * Set manual field value - Radios.
	 * 
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {object} value Field value.
	 * 
	 * @returns {void}
	 */
	setManualRadioValue(formId, name, value) {
		const items = this.state.getStateElementItems(name, formId);

		const innerInput = items?.[value]?.input;

		if (value === '') {
			Object.values(items).forEach((inner) => {
				inner.input.checked = false;
			});
		}

		if (innerInput) {
			innerInput.checked = value;
			this.setOnUserChangeInput(innerInput);
			this.setOnBlur(innerInput);
		}

		this.enrichment.setLocalStorageFormPrefillItem(formId, name);

		this.conditionalTags.setField(formId, name);
	}

	/**
	 * Set manual field value - Input/Textarea/Email/Text/Tel/Number/Password/Hidden.
	 * 
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {object} value Field value.
	 * 
	 * @returns {void}
	 */
	setManualInputValue(formId, name, value) {
		const input = this.state.getStateElementInput(name, formId);

		if (input) {
			input.value = value;
			this.setOnUserChangeInput(input);
			this.setOnBlur(input);
		}

		this.enrichment.setLocalStorageFormPrefillItem(formId, name);

		this.conditionalTags.setField(formId, name);
	}

	/**
	 * Set manual field value - Rating.
	 * 
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {object} value Field value.
	 * 
	 * @returns {void}
	 */
	setManualRatingValue(formId, name, value) {
		this.state.getStateElementCustom(name, formId).setAttribute(this.state.getStateAttribute('ratingValue'), value);
		this.setManualInputValue(formId, name, value);
	}

	/**
	 * Set manual field value - File
	 * 
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {object} value Field value.
	 * 
	 * @returns {void}
	 */
	setManualFileValue(formId, name, value) {
		const input = this.state.getStateElementInput(name, formId);
		const custom = this.state.getStateElementCustom(name, formId);

		if (input) {
			input.value = value;
			custom.removeAllFiles();
			this.setOnUserChangeInput(input);
			this.setOnBlur(input);
		}

		this.enrichment.setLocalStorageFormPrefillItem(formId, name);

		this.conditionalTags.setField(formId, name);
	}

	////////////////////////////////////////////////////////////////
	// Private methods - not shared to the public window object.
	////////////////////////////////////////////////////////////////

	/**
	 * Set all public methods.
	 *
	 * @returns {string}
	 */
	publicMethods() {
		setStateWindow();

		if (window[prefix].utils) {
			return;
		}

		window[prefix].utils = {
			resetErrors: (formId) => {
				this.resetErrors(formId);
			},
			dispatchFormEvent: (formId, name, additional) => {
				this.dispatchFormEvent(formId, name, additional);
			},
			scrollToElement: (formId, name) => {
				this.scrollToElement(formId, name);
			},
			scrollToGlobalMsg: (formId) => {
				this.scrollToGlobalMsg(formId);
			},
			showLoader: (formId) => {
				this.showLoader(formId);
			},
			hideLoader: (formId) => {
				this.hideLoader(formId);
			},
			unsetFieldError: (formId, name) => {
				this.unsetFieldError(formId, name);
			},
			setFieldError: (formId, name, msg) => {
				this.setFieldError(formId, name, msg);
			},
			outputErrors: (formId, data) => {
				this.outputErrors(formId, data);
			},
			unsetGlobalMsg: (formId) => {
				this.unsetGlobalMsg(formId);
			},
			setGlobalMsg: (formId, msg, status) => {
				this.setGlobalMsg(formId, msg, status);
			},
			getGtmData: (formId) => {
				this.getGtmData(formId);
			},
			gtmSubmit: (formId, status, errors) => {
				this.gtmSubmit(formId, status, errors);
			},
			setFieldFilledState: (formId, name) => {
				this.setFieldFilledState(formId, name);
			},
			setFieldActiveState: (formId, name) => {
				this.setFieldActiveState(formId, name);
			},
			unsetActiveState: (formId, name) => {
				this.unsetActiveState(formId, name);
			},
			setFilledState: (formId, name) => {
				this.setFilledState(formId, name);
			},
			unsetFilledState: (formId, name) => {
				this.unsetFilledState(formId, name);
			},
			resetForm: (formId) => {
				this.resetForm(formId);
			},
			redirectToUrl: (formId) => {
				this.redirectToUrl(formId);
			},
			redirectToUrlByReference: (formId, redirectUrl, reload = false) => {
				this.redirectToUrlByReference(formId, redirectUrl, reload);
			},
			isFormLoaded: (formId) => {
				this.isFormLoaded(formId);
			},
			getSaveAsJsonFormatOutput: (formId, name) => {
				this.getSaveAsJsonFormatOutput(formId, name);
			},
			getFileNameFromFileObject: (file) => {
				this.getFileNameFromFileObject(file);
			},
			formSubmitErrorContentType: (response, type, formId) => {
				this.formSubmitErrorContentType(response, type, formId);
			},
			getSelectSelectedValueByCustomData: (type, value, choices) => {
				return this.getSelectSelectedValueByCustomData(type, value, choices);
			},
			removeFormsWithMissingFormsBlock: () => {
				this.removeFormsWithMissingFormsBlock();
			},
			setOnFocus: (target) => {
				this.setOnFocus(target);
			},
			setOnBlur: (target) => {
				this.setOnBlur(target);
			},
			setOnUserChangeInput: (target) => {
				this.setOnUserChangeInput(target);
			},
			setOnUserChangeDate: (target) => {
				this.setOnUserChangeDate(target);
			},
			setOnUserChangeSelect: (target) => {
				this.setOnUserChangeSelect(target);
			},
			setManualPhoneValue: (formId, name, value) => {
				this.setManualPhoneValue(formId, name, value);
			},
			setManualDateValue: (formId, name, value) => {
				this.setManualDateValue(formId, name, value);
			},
			setManualSelectValue: (formId, name, value) => {
				this.setManualSelectValue(formId, name, value);
			},
			setManualCheckboxValue: (formId, name, value) => {
				this.setManualCheckboxValue(formId, name, value);
			},
			setManualRadioValue: (formId, name, value) => {
				this.setManualRadioValue(formId, name, value);
			},
			setManualInputValue: (formId, name, value) => {
				this.setManualInputValue(formId, name, value);
			},
		};
	}
}
