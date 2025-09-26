import { isEmpty } from '@eightshift/ui-components/utilities';
import { ConditionalTags } from './conditional-tags';
import { Enrichment } from './enrichment';
import { Geolocation } from './geolocation';
import { State } from './state';
import { StateEnum, prefix, setStateWindow, setStateValues, getState } from './state-init';
import { Steps } from './step';
import globalManifest from './../../../manifest.json';

/**
 * Main Utilities class.
 */
export class Utils {
	constructor() {
		/** @type {import('./state').State} */
		this.state = new State();
		/** @type {import('./enrichment').Enrichment} */
		this.enrichment = new Enrichment(this);
		/** @type {import('./conditional-tags').ConditionalTags}*/
		this.conditionalTags = new ConditionalTags(this);
		/** @type {import('./steps').Steps}*/
		this.steps = new Steps(this);
		/** @type {import('./geolocation').Geolocation}*/
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
	 * Create custom event.
	 *
	 * @param {string} eventName Event name.
	 * @param {string} formId Form Id.
	 * @param {object} additional Additional data to add to event.
	 *
	 * @returns {Event}
	 */
	createCustomEvent(eventName, formId = null, additional = null) {
		const options = {
			bubbles: true,
			detail: {
				[prefix]: window?.[prefix],
			},
		};

		if (formId) {
			options.detail.formId = formId;
		}

		if (additional) {
			options.detail.additional = additional;
		}

		return new CustomEvent(eventName, options);
	}

	/**
	 * Dispatch custom event - window
	 *
	 * @param {string} eventName Event name.
	 * @param {object} additional Additional data to add to event.
	 *
	 * @returns {void}
	 */
	dispatchFormEventWindow(eventName, additional = null) {
		window.dispatchEvent(this.createCustomEvent(eventName, additional));
	}

	/**
	 * Dispatch custom event - form
	 *
	 * @param {string} eventName Event name.
	 * @param {string} formId Form Id.
	 * @param {object} additional Additional data to add to event.
	 *
	 * @returns {void}
	 */
	dispatchFormEventForm(eventName, formId, additional = null) {
		window.dispatchEvent(this.createCustomEvent(eventName, formId, additional));
	}

	/**
	 * Dispatch custom event - field
	 *
	 * @param {string} eventName Event name.
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {object|string|array} value Field value.
	 *
	 * @returns {void}
	 */
	dispatchFormEventField(eventName, formId, name, value) {
		window.dispatchEvent(this.createCustomEvent(eventName, formId, { name, value }));
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
		const field = this.state.getStateElementField(name, formId);
		const type = this.state.getStateElementTypeField(name, formId);

		if (field) {
			this.scrollAction(field);

			switch (type) {
				case 'file':
					this.state.getStateElementFileButton(name, formId)?.focus();
					break;
				case 'select':
				case 'country':
					this.state.getStateElementCustom(name, formId)?.showDropdown();
					break;
				case 'date':
				case 'dateTime':
					this.state.getStateElementCustom(name, formId)?.open();
					break;
				case 'file':
					this.state.getStateElementFileButton(name, formId)?.focus();
					break;
				case 'checkbox':
				case 'radio':
				case 'rating':
					const firstKey = Object.keys(this.state.getStateElementItems(name, formId))?.[0];

					this.state.getStateElementItems(name, formId)?.[firstKey]?.input?.focus();
					break;
				default:
					this.state.getStateElementInput(name, formId).focus();
			}
		}
	}

	/**
	 * Scroll to element action.
	 *
	 * @param {Element} element Element to scroll to.
	 * @param {object} options Scroll options.
	 *
	 * @returns {void}
	 */
	scrollAction(
		element,
		options = {
			behavior: 'smooth',
			block: 'start',
		}
	) {
		element?.scrollIntoView(options);
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

		const input = this.state.getStateElementInput(name, formId);

		this.state.getStateElementField(name, formId)?.classList?.remove(this.state.getStateSelector('hasError'));
		this.state.setStateElementHasError(name, false, formId);
		error.innerHTML = '';

		if (input) {
			input.setAttribute('aria-invalid', 'false');
		}
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

		const input = this.state.getStateElementInput(name, formId);

		this.state.getStateElementField(name, formId)?.classList?.add(this.state.getStateSelector('hasError'));
		this.state.setStateElementHasError(name, true, formId);
		error.innerHTML = msg;

		if (input) {
			input.setAttribute('aria-invalid', 'true');
		}
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

		messageContainer?.classList?.remove(this.state.getStateSelector('isActive'), this.state.getStateSelector('hasError'));
		messageContainer.dataset.status = '';
		messageContainer.innerHTML = '';
	}

	/**
	 * Set global msg.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} msg Message text.
	 * @param {string} status Message status.
	 * @param {object} responseData Additional responseData.
	 *
	 * @returns {void}
	 */
	setGlobalMsg(formId, msg, status, responseData = {}) {
		const messageContainer = this.state.getStateFormGlobalMsgElement(formId);

		if (!messageContainer) {
			return;
		}

		// Scroll to msg if the condition is matched.
		if (status === 'success') {
			if (responseData?.[this.state.getStateResponseOutputKey('hideGlobalMsgOnSuccess')]) {
				return;
			}

			messageContainer?.classList?.add(this.state.getStateSelector('isActive'));
			messageContainer.dataset.status = status;

			if (!this.state.getStateSettingsDisableScrollToGlobalMsgOnSuccess(formId)) {
				this.scrollAction(this.state.getStateFormGlobalMsgElement(formId));
			}

			const headingSuccess = this.state.getStateFormGlobalMsgHeadingSuccess(formId);

			if (headingSuccess) {
				messageContainer.innerHTML = `<div><div>${headingSuccess}</div><span>${msg}</span></div>`;
			} else {
				messageContainer.innerHTML = `<div><span>${msg}</span></div>`;
			}
		} else {
			messageContainer?.classList?.add(this.state.getStateSelector('isActive'), this.state.getStateSelector('hasError'));
			messageContainer.dataset.status = status;

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
			const field = this.state.getStateElementField(name, formId);

			if (!trackingName) {
				continue;
			}

			switch (this.state.getStateElementTypeField(name, formId)) {
				case 'checkbox':
					for (const [checkName, checkValue] of Object.entries(value)) {
						const trackingCheckName = trackingName?.[checkName];

						if (!trackingCheckName) {
							continue;
						}

						if (!(trackingCheckName in output)) {
							output[trackingCheckName] = '';
						}

						if (checkValue) {
							output[trackingCheckName] = checkValue;
						}
					}
					break;
				case 'file':
					const fileList = this.state.getStateElementCustom(name, formId)?.files ?? [];
					output[trackingName] = fileList?.map((file) => file?.upload?.uuid);
					break;
				case 'phone':
					output[trackingName] = this.getPhoneCombinedValue(formId, name);
					break;
				default:
					output[trackingName] = value;
					break;
			}
		}

		return output;
	}

	/**
	 * Get GTM event with data and push to dataLayer.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} status Response status.
	 * @param {object} responseData Additional responseData.
	 *
	 * @returns {void}
	 */
	gtmSubmit(formId, status, responseData = {}) {
		const eventName = responseData?.[this.state.getStateResponseOutputKey('trackingEventName')];
		const errors = responseData?.[this.state.getStateResponseOutputKey('validation')];

		if (!eventName) {
			return;
		}

		const gtmData = {
			event: eventName,
			...this.getGtmData(formId),
		};

		const additionalData = responseData?.[this.state.getStateResponseOutputKey('trackingAdditionalData')];

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
			window.dataLayer.push({ ...gtmData, ...additionalDataItems });

			this.dispatchFormEventForm(this.state.getStateEvent('afterGtmDataPush'), formId, {
				gtmData,
				additionalDataItems,
			});
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
		this.unsetActiveState(formId, name);

		const type = this.state.getStateElementTypeField(name, formId);
		const value = this.state.getStateElementValue(name, formId);

		switch (type) {
			case 'checkbox':
				this.setFieldFilledStateByName(formId, name, Object.values(value).filter((item) => item !== '').length > 0);
				break;
			case 'phone':
				this.setFieldFilledStateByName(formId, name, value?.value);
				break;
			default:
				this.setFieldFilledStateByName(formId, name, value && value.length);
				break;
		}
	}

	/**
	 * Prefill inputs active/filled.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {bool} condition Condition.
	 *
	 * @returns {void}
	 */
	setFieldFilledStateByName(formId, name, condition) {
		if (condition) {
			this.setFilledState(formId, name);
		} else {
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
	setActiveState(formId, name) {
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
	 *  Reset form values to the initial state.
	 *
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	resetForm(formId) {
		if (!this.state.getStateSettingsResetOnSuccess(formId)) {
			return;
		}

		if (this.state.getStateFormIsAdminSingleSubmit(formId)) {
			return;
		}

		if (this.state.getStateFormConfigUseSingleSubmit(formId)) {
			return;
		}

		for (const [name] of this.state.getStateElements(formId)) {
			const initial = this.state.getStateElementInitial(name, formId);

			switch (this.state.getStateElementTypeField(name, formId)) {
				case 'phone':
					this.setManualPhoneValue(formId, name, initial);
					break;
				case 'date':
				case 'dateTime':
					this.setManualDateValue(formId, name, initial);
					break;
				case 'country':
					this.setManualCountryValue(formId, name, initial);
					break;
				case 'select':
					this.setManualSelectValue(formId, name, initial);
					break;
				case 'checkbox':
					this.setManualCheckboxValue(formId, name, initial);
					break;
				case 'radio':
					this.setManualRadioValue(formId, name, initial);
					break;
				case 'rating':
					this.setManualRatingValue(formId, name, initial);
					break;
				case 'range':
					this.setManualRangeValue(formId, name, initial);
					break;
				case 'file':
					this.state.getStateElementCustom(name, formId)?.removeAllFiles();
					break;
				default:
					this.setManualInputValue(formId, name, initial);
					break;
			}
		}

		// Remove focus from last input.
		document.activeElement.blur();

		// Dispatch event.
		this.dispatchFormEventForm(this.state.getStateEvent('afterFormSubmitReset'), formId);
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
		this.dispatchFormEventForm(this.state.getStateEvent('afterFormSubmitSuccessBeforeRedirect'), formId, redirectUrl);

		// Do the actual redirect after some time.
		setTimeout(
			() => {
				window.location = redirectUrl;

				if (reload) {
					window.location.reload();
				}
			},
			parseInt(this.state.getStateSettingsRedirectionTimeout(formId), 10),
		);
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

				// Trigger event that form is fully loaded.
				this.dispatchFormEventForm(this.state.getStateEvent('formJsLoaded'), formId);
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
		
		return JSON.parse(file.xhr.response).data.fileName || '';
	}

	/**
	 * Actions to run if api response returns an error.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} actionName Action name.
	 * @param {string} errorName Error name.
	 * @param {string} errorMessage Error message.
	 *
	 * @returns {string}
	 */
	formSubmitResponseError(formId, actionName, errorName, errorMessage) {
		if (formId !== null) {
			// Clear all errors.
			this.resetErrors(formId);

			// Remove loader.
			this.hideLoader(formId);

			// Set global msg.
			this.setGlobalMsg(formId, this.state.getStateSettingsFormServerErrorMsg(), 'error');

			// Reset timeout for after each submit.
			if (typeof this.GLOBAL_MSG_TIMEOUT_ID === 'number') {
				clearTimeout(this.GLOBAL_MSG_TIMEOUT_ID);
			}

			// Hide global msg in any case after some time.
			this.GLOBAL_MSG_TIMEOUT_ID = setTimeout(
				() => {
					this.unsetGlobalMsg(formId);
				},
				parseInt(this.state.getStateSettingsHideGlobalMessageTimeout(formId), 10),
			);
		}

		return (`API response returned an error. Function used: "${actionName}" with error: "${errorName}" and a message: "${errorMessage}" for form id: "${formId}"`);
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

		this.setActiveState(formId, name);
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

		this.unsetActiveState(formId, name);
	}

	/**
	 * Set mandatory field state.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {string} value Field value.
	 * @param {bool} fullSet Full set value.
	 *
	 * @returns {void}
	 */
	setMandatoryFieldState(formId, name, value, fullSet = true) {
		this.setFieldFilledState(formId, name);

		this.enrichment.setLocalStorageFormPrefillField(formId, name);

		if (fullSet) {
			this.conditionalTags.setField(formId, name);
		}

		this.dispatchFormEventField(this.state.getStateEvent('onFieldChange'), formId, name, value);
	}

	/**
	 * Set manual field value - Phone.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {object} value Field value.
	 * @param {bool} fullSet Full set value.
	 * @param {bool} set Set value.
	 *
	 * Expected value format:
	 * {
	 *  prefix: '1',
	 *  value: '1234567890'
	 * }
	 *
	 * @returns {void}
	 */
	setManualPhoneValue(formId, name, value, fullSet = true, set = true) {
		if (typeof value !== 'object') {
			return;
		}

		if (!(name in this.state.getStateElementsObject(formId))) {
			return;
		}

		const phoneDisablePicker = this.state.getStateFormConfigPhoneDisablePicker(formId);

		let newValue = value?.value ?? this.state.getStateElementValue(name, formId)?.value ?? '';
		let newPrefix = value?.prefix ?? (this.state.getStateElementValue(name, formId)?.prefix ?? '');

		const isValueEmpty = isEmpty(value);

		// For manual setting.
			if (!phoneDisablePicker) {
				const custom = this.state.getStateElementCustom(name, formId);

				if (custom) {
					if (isValueEmpty || newPrefix === '') {
						custom.removeActiveItems();
					} else {
						if (set) {
							custom.setChoiceByValue(newPrefix);
						}
					}
				}
			}

			const input = this.state.getStateElementInput(name, formId);

			if (input) {
				if (isValueEmpty) {
					input.value = '';
					newValue = '';
					newPrefix = '';
				} else {
					if (set) {
						input.value = newValue;
					}
				}
			}

		const outputValue = {
			value: newValue,
			prefix: newPrefix,
		};

		setStateValues(name, outputValue, formId);
		this.setMandatoryFieldState(formId, name, outputValue, fullSet);
	}

	/**
	 * Set manual field value - Phone prefix by attribute value.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {string} value Field value.
	 * @param {string} attribute Attribute name.
	 * @param {bool} fullSet Full set value.
	 *
	 * Expected value format:
	 * 'hr'
	 *
	 * @returns {void}
	 */
	setManualPhonePrefixByAttributeValue(formId, name, value, attribute, fullSet = true) {
		if (typeof value !== 'string') {
			return;
		}

		if (!(name in this.state.getStateElementsObject(formId))) {
			return;
		}

		const phoneDisablePicker = this.state.getStateFormConfigPhoneDisablePicker(formId);

		if (phoneDisablePicker) {
			return;
		}

		let newPrefix = value ?? '';

		const custom = this.state.getStateElementCustom(name, formId);

		const option = [...custom?.passedElement?.element?.options].find((option) => option.getAttribute(attribute) === value);

		if (option) {
			custom.setChoiceByValue(option.value);

			newPrefix = option.value;
		}

		const outputValue = {
			value: this.state.getStateElementValue(name, formId)?.value ?? '',
			prefix: newPrefix,
		};

		setStateValues(name, outputValue, formId);
		this.setMandatoryFieldState(formId, name, outputValue, fullSet);
	}

	/**
	 * Set manual field value - Date.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {string} value Field value.
	 * @param {bool} fullSet Full set value.
	 * @param {bool} set Set value.
	 *
	 * Expected value format:
	 * '2021-01-01'
	 * '2021-01-01 12:00'
	 *
	 * @returns {void}
	 */
	setManualDateValue(formId, name, value, fullSet = true, set = true) {
		if (typeof value !== 'string') {
			return;
		}

		if (!(name in this.state.getStateElementsObject(formId))) {
			return;
		}

		// For manual setting.
		const custom = this.state.getStateElementCustom(name, formId);

		if (custom) {
			if (set) {
				custom.setDate(value, true, custom?.config?.dateFormat);
			}
		}

		setStateValues(name, value, formId);
		this.setMandatoryFieldState(formId, name, value, fullSet);
	}

	/**
	 * Set manual field value - Select.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {array} value Field value.
	 * @param {bool} fullSet Full set value.
	 * @param {bool} set Set value.
	 *
	 * Expected value format:
	 * ['option-1', 'option-2']
	 *
	 * @returns {void}
	 */
	setManualSelectValue(formId, name, value, fullSet = true, set = true) {
		if (!Array.isArray(value)) {
			return;
		}

		if (!(name in this.state.getStateElementsObject(formId))) {
			return;
		}

		const newValue = value;

		// For manual setting.
		if (set) {
			const custom = this.state.getStateElementCustom(name, formId);

			if (custom) {
				if (newValue.length) {
					newValue.forEach((item, index) => {
						const option = [...custom.passedElement?.element?.options].find((option) => option.value === item);

						if (option) {
							custom.setChoiceByValue(item);
						} else {
							newValue.splice(index, 1);
						}
					});
				} else {
					custom.removeActiveItems();
				}
			}
		}

		setStateValues(name, newValue, formId);
		this.setMandatoryFieldState(formId, name, newValue, fullSet);
	}

	/**
	 * Set manual field value - Select by attribute value.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {array} value Field value.
	 * @param {string} attribute Attribute name.
	 * @param {bool} fullSet Full set value.
	 *
	 * Expected value format:
	 * ['hr', 'de']
	 *
	 * @returns {void}
	 */
	setManualSelectByAttributeValue(formId, name, value, attribute, fullSet = true) {
		if (!Array.isArray(value)) {
			return;
		}

		if (!(name in this.state.getStateElementsObject(formId))) {
			return;
		}

		let newValue = value;

		// For manual setting.
		const custom = this.state.getStateElementCustom(name, formId);

		if (custom) {
			if (newValue.length) {
				newValue.forEach((item, index) => {
					const option = [...custom.passedElement?.element?.options].find((option) => option.getAttribute(attribute) === item);

					if (option) {
						custom.setChoiceByValue(option.value);
						newValue[index] = option.value;
					} else {
						newValue.splice(index, 1);
					}
				});
			} else {
				custom.removeActiveItems();
			}
		}

		setStateValues(name, newValue, formId);
		this.setMandatoryFieldState(formId, name, newValue, fullSet);
	}

	/**
	 * Set manual field value - Country.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {array} value Field value.
	 * @param {bool} fullSet Full set value.
	 * @param {bool} set Set value.
	 *
	 * Expected value format:
	 * ['hr', 'de']
	 *
	 * @returns {void}
	 */
	setManualCountryValue(formId, name, value, fullSet = true, set = true) {
		this.setManualSelectValue(formId, name, value, fullSet, set);
	}

	/**
	 * Set manual field value - Checkboxes.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {array} value Field value.
	 * @param {bool} fullSet Full set value.
	 *
	 * Expected value format:
	 * {
	 *  checkbox-1: checkbox-1,
	 *  checkbox-2: checkbox-2,
	 * }
	 *
	 * @returns {void}
	 */
	setManualCheckboxValue(formId, name, value, fullSet = true) {

		let initValue = value;

		if (Array.isArray(value)) {
			initValue = value.reduce((acc, item) => {
				acc[item] = item;

				return acc;
			}, {});
		}

		if (typeof initValue !== 'object') {
			return;
		}

		if (!(name in this.state.getStateElementsObject(formId))) {
			return;
		}

		const isValueEmpty = isEmpty(initValue);

		let newValue = this.state.getStateElementValue(name, formId);
		const custom = this.state.getStateElementCustom(name, formId);

		let hasCustomChanged = false;

		const inner = this.state.getStateElementItems(name, formId);

		if (inner) {
			if (isValueEmpty) {
				Object.values(inner).forEach((item) => {
					item.input.checked = false;

					newValue[item.value] = '';
				});

				if (custom) {
					custom.value = '';
					hasCustomChanged = true;
				}
			} else {
				let customValue = '';
				
				Object.values(inner).forEach((item) => {
					if (initValue?.[item.value]) {
						item.input.checked = true;

						newValue[item.value] = item.value;
					}

					if (initValue?.[item.value] === '') {
						item.input.checked = false;

						newValue[item.value] = '';
					}
				});

				Object.values(initValue).forEach((item) => {
					if (!inner[item]) {
						customValue = item;
					}
				});

				if (custom && customValue !== '') {
					custom.value = customValue;
					hasCustomChanged = true;
				}
			}
		}

		setStateValues(name, newValue, formId);
		this.setMandatoryFieldState(formId, name, newValue, fullSet);

		if (hasCustomChanged && custom) {
			setStateValues(custom.name, custom.value, formId);
			this.setMandatoryFieldState(formId, custom.name, custom.value, fullSet);
		}
	}

	/**
	 * Set manual field value - Radios.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {string} value Field value.
	 * @param {bool} fullSet Full set value.
	 *
	 * Expected value format:
	 * 'radio-1'
	 *
	 * @returns {void}
	 */
	setManualRadioValue(formId, name, value, fullSet = true) {
		if (typeof value !== 'string') {
			return;
		}

		if (!(name in this.state.getStateElementsObject(formId))) {
			return;
		}

		const custom = this.state.getStateElementCustom(name, formId);

		let newValue = this.state.getStateElementValue(name, formId);

		let hasCustomChanged = false;

		const inner = this.state.getStateElementItems(name, formId);

		if (inner) {
			if (value === '') {
				Object.values(inner).forEach((item) => {
					item.input.checked = false;

					newValue = '';
				});

				if (custom) {
					custom.value = '';
					hasCustomChanged = true;
				}
			} else {
				Object.values(inner).forEach((item) => {
					item.input.checked = false;
				});

				if (inner[value]) {
					inner[value].input.checked = true;
					newValue = value;

					if (custom) {
						custom.value = '';
						hasCustomChanged = true;
					}
				} else {
					if (custom) {
						custom.value = value;
						newValue = '';
						hasCustomChanged = true;
					}
				}
			}
		}

		setStateValues(name, newValue, formId);
		this.setMandatoryFieldState(formId, name, newValue, fullSet);

		if (hasCustomChanged && custom) {
			setStateValues(custom.name, custom.value, formId);
			this.setMandatoryFieldState(formId, custom.name, custom.value, fullSet);
		}
	}

	/**
	 * Set manual field value - Input/Textarea/Email/Text/Tel/Number/Password/Hidden.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {string} value Field value.
	 * @param {bool} fullSet Full set value.
	 * @param {bool} set Set value.
	 *
	 * Expected value format:
	 * 'value'
	 *
	 * @returns {void}
	 */
	setManualInputValue(formId, name, value, fullSet = true, set = true) {
		if (typeof value !== 'string') {
			return;
		}

		if (!(name in this.state.getStateElementsObject(formId))) {
			return;
		}

		const input = this.state.getStateElementInput(name, formId);

		if (input) {
			if (set) {
				input.value = value;
			}
		}

		setStateValues(name, value, formId);
		this.setMandatoryFieldState(formId, name, value, fullSet);

		const fieldset = this.state.getStateElementFieldset(name, formId);

		if (fieldset) {
			const customRadioInputName = fieldset.getAttribute(this.state.getStateAttribute('fieldName'));
			const customRadioInputType = fieldset.getAttribute(this.state.getStateAttribute('fieldType'));

			if (customRadioInputName && customRadioInputType === 'radio' && value !== '') {
				const inner = this.state.getStateElementItems(customRadioInputName, formId);

				Object.values(inner).forEach((item) => {
					item.input.checked = false;
				});

				setStateValues(customRadioInputName, '', formId);
				this.setMandatoryFieldState(formId, customRadioInputName, '', fullSet);
			}
		}
	}

	/**
	 * Set manual field value - Rating.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {string} value Field value.
	 * @param {bool} fullSet Full set value.
	 *
	 * Expected value format:
	 * '5'
	 *
	 * @returns {void}
	 */
	setManualRatingValue(formId, name, value, fullSet = true) {
		this.setManualRadioValue(formId, name, value, fullSet);
	}

	/**
	 * Set manual field value - Range
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {string} value Field value.
	 * @param {bool} fullSet Full set value.
	 * @param {bool} set Set value.
	 *
	 * Expected value format:
	 * '50'
	 *
	 * @returns {void}
	 */
	setManualRangeValue(formId, name, value, fullSet = true, set = true) {
		if (typeof value !== 'string') {
			return;
		}

		if (!(name in this.state.getStateElementsObject(formId))) {
			return;
		}

		this.setManualInputValue(formId, name, value, fullSet, set);
		this.setRangeCurrentValue(formId, name);
	}

	/**
	 * Set range current value.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	setRangeCurrentValue(formId, name) {
		const current = this.state.getStateElementRangeCurrent(name, formId);
		const input = this.state.getStateElementInput(name, formId);
		const custom = this.state.getStateElementCustom(name, formId);
		const value = this.state.getStateElementValue(name, formId);

		// Set range current value as css variable due to inconsistency in browsers.
		if (input) {
			const min = input.min || 0;
			const max = input.max || 100;
			const parsedProgress = Number(((value - min) * 100) / (max - min)).toFixed(2);

			input.style.setProperty('--es-form-range-progress', `${parsedProgress}%`);

			if (custom) {
				custom.value = value;
			}
		}

		if (current.length) {
			current.forEach((item) => {
				item.innerHTML = value;
			});
		}
	}

	/**
	 * Set output results.
	 *
	 * @param {string} formId Form Id.
	 * @param {object} response Api response.
	 *
	 * @returns {void}
	 */
	setResultsOutput(formId, data) {
		const formFid = this.state.getStateFormFid(formId);
		// Check if we have output element - block.
		const outputElement = document.querySelector(`${this.state.getStateSelector('resultOutput', true)}[${this.state.getStateAttribute('formId')}="${formFid}"]`);

		// If no output element, bailout.
		if (!outputElement) {
			return;
		}

		this.resetResultsOutput(formFid);

		// Check if we have output items.
		const outputItems = data?.[this.state.getStateResponseOutputKey('variation')] ?? {};

		if (Object.keys(outputItems).length) {
			for (const [key, value] of Object.entries(outputItems)) {
				const itemElements = outputElement.querySelectorAll(
					`${this.state.getStateSelector('resultOutputItem', true)}[${this.state.getStateAttribute('resultOutputItemKey')}="${key}"]`,
				);

				itemElements.forEach((item) => {
					const operator = item.getAttribute(this.state.getStateAttribute('resultOutputItemOperator')) || globalManifest.comparator.IS;
					const startValue = item.getAttribute(this.state.getStateAttribute('resultOutputItemValueStart'));
					const endValue = item.getAttribute(this.state.getStateAttribute('resultOutputItemValueEnd'));

					if (this.getComparator()[operator](String(startValue), String(value), String(endValue))) {
						item.classList.remove(this.state.getStateSelector('isHidden'));
					}
				});

				const partElement = outputElement.querySelectorAll(
					`${this.state.getStateSelector('resultOutputPart', true)}[${this.state.getStateAttribute('resultOutputPart')}="${key}"]`,
				);

				if (partElement.length && value) {
					partElement.forEach((item) => {
						item.classList.remove(this.state.getStateSelector('isHidden'));
						item.innerHTML = value;
					});
				}
			}
		}

		// Check if output block is hidden.
		const outputElementIsHidden = outputElement.classList.contains(this.state.getStateSelector('isHidden'));

		// If hidden, show it.
		if (outputElementIsHidden) {
			outputElement.classList.remove(this.state.getStateSelector('isHidden'));
		}

		// Show form elements.
		const showFormElement = outputElement.querySelectorAll(`${this.state.getStateSelector('resultOutputShowForm', true)}`);

		if (showFormElement) {
			showFormElement.forEach((item) => {
				item.addEventListener('click', this.onFormShowEvent);
			});
		}

		this.dispatchFormEventForm(this.state.getStateEvent('afterResultsOutput'), formId, data);
	}

	/**
	 * Reset output results.
	 *
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	resetResultsOutput(formId) {
		// Check if we have output element - block.
		const outputElement = document.querySelector(`${this.state.getStateSelector('resultOutput', true)}[${this.state.getStateAttribute('formId')}="${formId}"]`);

		if (!outputElement) {
			return;
		}

		// Reset items.
		const itemElements = outputElement.querySelectorAll(this.state.getStateSelector('resultOutputItem', true));

		if (itemElements.length) {
			itemElements.forEach((item) => {
				item.classList.add(this.state.getStateSelector('isHidden'));
			});
		}

		// Reset parts.
		const partElements = outputElement.querySelectorAll(this.state.getStateSelector('resultOutputPart', true));

		if (partElements.length) {
			partElements.forEach((item) => {
				if (item.hasAttribute(this.state.getStateAttribute('resultOutputPartDefault'))) {
					item.innerHTML = item.getAttribute(this.state.getStateAttribute('resultOutputPartDefault'));
				}
			});
		}
	}

	/**
	 * Get comparator object with all available operators.
	 *
	 * is  - is                               - if value is exact match.
	 * isn - is not                           - if value is not exact match.
	 * gt  - greater than                     - if value is greater than.
	 * gte - greater/equal than               - if value is greater/equal than.
	 * lt  - less than                        - if value is less than.
	 * lte - less/equal than                  - if value is less/equal than.
	 * c   - contains                         - if value contains value.
	 * cn  - contains not                     - if value doesn't contain value.
	 * sw  - starts with                      - if value starts with value.
	 * ew  - ends with                        - if value ends with value.
	 * b   - between range                    - if value is between two values.
	 * bs  - between range strict             - if value is between two values strict.
	 * bn  - not between range                - if value is not between two values.
	 * bns - not between between range strict - if value is not between two values strict.
	 *
	 * @returns {object}
	 */
	getComparator() {
		return {
			[globalManifest.comparator.IS]: (start, value) => value === start,
			[globalManifest.comparator.ISN]: (start, value) => value !== start,
			[globalManifest.comparator.GT]: (start, value) => parseFloat(String(value)) > parseFloat(String(start)),
			[globalManifest.comparator.GTE]: (start, value) => parseFloat(String(value)) >= parseFloat(String(start)),
			[globalManifest.comparator.LT]: (start, value) => parseFloat(String(value)) < parseFloat(String(start)),
			[globalManifest.comparator.LTE]: (start, value) => parseFloat(String(value)) <= parseFloat(String(start)),
			[globalManifest.comparator.C]: (start, value) => String(value).includes(String(start)),
			[globalManifest.comparator.CN]: (start, value) => !String(value).includes(String(start)),
			[globalManifest.comparator.SW]: (start, value) => String(value).startsWith(String(start)),
			[globalManifest.comparator.EW]: (start, value) => String(value).endsWith(String(start)),
			[globalManifest.comparatorExtended.B]: (start, value, end) =>
				parseFloat(String(value)) > parseFloat(String(start)) && parseFloat(String(value)) < parseFloat(String(end)),
			[globalManifest.comparatorExtended.BS]: (start, value, end) =>
				parseFloat(String(value)) >= parseFloat(String(start)) && parseFloat(String(value)) <= parseFloat(String(end)),
			[globalManifest.comparatorExtended.BN]: (start, value, end) =>
				parseFloat(String(value)) < parseFloat(String(start)) || parseFloat(String(value)) > parseFloat(String(end)),
			[globalManifest.comparatorExtended.BNS]: (start, value, end) =>
				parseFloat(String(value)) <= parseFloat(String(start)) || parseFloat(String(value)) >= parseFloat(String(end)),
		};
	}

	/**
	 * Get phone combined value.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {string}
	 */
	getPhoneCombinedValue(formId, name) {
		const data = this.state.getStateElementValue(name, formId);

		if (!data || data?.value === '') {
			return '';
		}

		if (!this.state.getStateFormConfigPhoneDisablePicker(formId)) {
			return data?.prefix === '' ? '' : `${data?.prefix}${data?.value}`;
		}

		return data?.value;
	}

	/**
	 * Build helper for form data object.
	 *
	 * @param {string} formId Form Id.
	 * @param {object} dataSet Object to build.
	 *
	 * @returns {void}
	 */
	buildFormDataItems(data, dataSet) {
		data.forEach((item) => {
			const { name, value, type = 'hidden', typeCustom = 'hidden', custom = '' } = item;

			dataSet.append(
				name,
				JSON.stringify({
					name,
					value,
					type,
					typeCustom,
					custom,
				}),
			);
		});
	}

	////////////////////////////////////////////////////////////////
	// Events callback
	////////////////////////////////////////////////////////////////

	/**
	 * Handle form show event.
	 *
	 * @param {object} event Event callback.
	 * @returns {void}
	 */
	onFormShowEvent = (e) => {
		const outputs = e?.target?.closest(this.state.getStateSelector('resultOutput', true));

		if (!outputs) {
			return;
		}

		outputs?.classList?.add(this.state.getStateSelector('isHidden'));

		for (const formId of this.state.getStateForms()) {
			const form = this.state.getStateFormElement(formId);

			if (!form) {
				continue;
			}

			form?.classList?.remove(this.state.getStateSelector('isHidden'));
		}
	};

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
			createCustomEvent: (eventName, formId = null, additional = null) => {
				return this.createCustomEvent(eventName, formId, additional);
			},
			dispatchFormEventWindow: (eventName, additional = null) => {
				this.dispatchFormEventWindow(eventName, additional);
			},
			dispatchFormEventForm: (eventName, formId, additional = null) => {
				this.dispatchFormEventForm(eventName, formId, additional);
			},
			dispatchFormEventField: (eventName, formId, name, value) => {
				this.dispatchFormEventField(eventName, formId, name, value);
			},
			scrollToElement: (formId, name) => {
				this.scrollToElement(formId, name);
			},
			scrollAction: (element) => {
				this.scrollAction(element);
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
			setGlobalMsg: (formId, msg, status, responseData = {}) => {
				this.setGlobalMsg(formId, msg, status, responseData);
			},
			getGtmData: (formId) => {
				this.getGtmData(formId);
			},
			gtmSubmit: (formId, status, responseData = {}) => {
				this.gtmSubmit(formId, status, responseData);
			},
			setFieldFilledState: (formId, name) => {
				this.setFieldFilledState(formId, name);
			},
			setFieldFilledStateByName: (formId, name, condition) => {
				this.setFieldFilledStateByName(formId, name, condition);
			},
			setActiveState: (formId, name) => {
				this.setActiveState(formId, name);
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
			formSubmitResponseError: (formId, actionName, errorName, errorMessage) => {
				this.formSubmitResponseError(formId, actionName, errorName, errorMessage);
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
			setMandatoryFieldState: (formId, name, value, fullSet = true) => {
				this.setMandatoryFieldState(formId, name, value, fullSet);
			},
			setManualPhoneValue: (formId, name, value, fullSet = true, set = true) => {
				this.setManualPhoneValue(formId, name, value, fullSet, set);
			},
			setManualPhonePrefixByAttributeValue: (formId, name, value, attribute, fullSet = true) => {
				this.setManualPhonePrefixByAttributeValue(formId, name, value, attribute, fullSet);
			},
			setManualDateValue: (formId, name, value, fullSet = true, set = true) => {
				this.setManualDateValue(formId, name, value, fullSet, set);
			},
			setManualSelectValue: (formId, name, value, fullSet = true, set = true) => {
				this.setManualSelectValue(formId, name, value, fullSet, set);
			},
			setManualSelectByAttributeValue: (formId, name, value, attribute, fullSet = true) => {
				this.setManualSelectByAttributeValue(formId, name, value, attribute, fullSet);
			},
			setManualCountryValue: (formId, name, value, fullSet = true, set = true) => {
				this.setManualCountryValue(formId, name, value, fullSet, set);
			},
			setManualCheckboxValue: (formId, name, value, fullSet = true) => {
				this.setManualCheckboxValue(formId, name, value, fullSet);
			},
			setManualRadioValue: (formId, name, value, fullSet = true) => {
				this.setManualRadioValue(formId, name, value, fullSet);
			},
			setManualInputValue: (formId, name, value, fullSet = true, set = true) => {
				this.setManualInputValue(formId, name, value, fullSet, set);
			},
			setManualRatingValue: (formId, name, value, fullSet = true) => {
				this.setManualRatingValue(formId, name, value, fullSet);
			},
			setManualRangeValue: (formId, name, value, fullSet = true, set = true) => {
				this.setManualRangeValue(formId, name, value, fullSet, set);
			},
			setRangeCurrentValue: (formId, name) => {
				this.setRangeCurrentValue(formId, name);
			},
			setResultsOutput: (formId, data) => {
				this.setResultsOutput(formId, data);
			},
			resetResultsOutput: (formId) => {
				this.resetResultsOutput(formId);
			},
			getComparator: () => {
				return this.getComparator();
			},
			getPhoneCombinedValue: (formId, name) => {
				return this.getPhoneCombinedValue(formId, name);
			},
			buildFormDataItems: (data, dataSet) => {
				this.buildFormDataItems(data, dataSet);
			},
			onFormShowEvent: (e) => {
				this.onFormShowEvent(e);
			},
		};
	}
}
