import { State } from './state';
import { StateEnum, prefix, setStateWindow } from './state/init';

/**
 * Main Utilities class.
 */
export class Utils {
	constructor() {
		this.state = new State();

		// Set all public methods.
		this.publicMethods();
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
	 * @param {boolean} disableOverlay Disable overlay.
	 *
	 * @returns {void}
	 */
	showLoader(formId, disableOverlay = true) {
		this.state.getStateFormElement(formId)?.classList?.add(this.state.getStateSelectorsClassLoading());
		this.state.getStateFormLoader(formId)?.classList?.add(this.state.getStateSelectorsClassActive());

		if (!disableOverlay) {
			this.state.getStateFormLoader(formId)?.classList?.add(this.state.getStateSelectorsClassLoaderDisableOverlay());
		}
	}

	/**
	 * Hide loader.
	 *
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	hideLoader(formId) {
		this.state.getStateFormElement(formId)?.classList?.remove(this.state.getStateSelectorsClassLoading());
		this.state.getStateFormLoader(formId)?.classList?.remove(this.state.getStateSelectorsClassActive());
		this.state.getStateFormLoader(formId)?.classList?.remove(this.state.getStateSelectorsClassLoaderDisableOverlay());
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

		error?.classList?.remove(this.state.getStateSelectorsClassHasError());
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

		error?.classList?.add(this.state.getStateSelectorsClassHasError());
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

		messageContainer?.classList?.remove(this.state.getStateSelectorsClassActive());
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

		messageContainer?.classList?.add(this.state.getStateSelectorsClassActive());
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

			switch (this.state.getStateElementTypeInternal(name, formId)) {
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
				case 'tel':
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
					this.state.getStateEventsAfterGtmDataPush(), {
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
		const type = this.state.getStateElementType(name, formId);
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
		this.state.getStateElementField(name, formId)?.classList?.add(this.state.getStateSelectorsClassActive());
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
		this.state.getStateElementField(name, formId)?.classList?.remove(this.state.getStateSelectorsClassActive());
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
		this.state.getStateElementField(name, formId)?.classList?.add(this.state.getStateSelectorsClassFilled());
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
		this.state.getStateElementField(name, formId)?.classList?.remove(this.state.getStateSelectorsClassFilled());
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
			const type = this.state.getStateElementType(name, formId);
			const custom = this.state.getStateElementCustom(name, formId);
			const input = this.state.getStateElementInput(name, formId);
			const items = this.state.getStateElementItems(name, formId);
			const initial = this.state.getStateElementInitial(name, formId);

			// Skip select search field.
			if (name === 'search_terms') {
				continue;
			}

			switch (type) {
				case 'checkbox':
					this.state.setStateElementValue(name, {...initial}, formId);

					for(const [innerName, innerValue] of Object.entries(initial)) {
						items[innerName].input.checked = innerValue !== '';
					}
					break;
				case 'radio':
					this.state.setStateElementValue(name, initial, formId);

					if (initial === '') {
						Object.values(items).forEach((inner) => {
							inner.input.checked = false;
						});
					} else {
						items[initial].input.checked = true;
					}
					break;
				case 'file':
					this.state.setStateElementValue(name, initial, formId);
					custom.removeAllFiles();
					break;
				case 'select':
					this.state.setStateElementValue(name, initial, formId);
					custom.setChoiceByValue(initial);
					break;
				case 'date':
					this.state.setStateElementValue(name, initial, formId);
					custom.setDate(initial);
					break;
				default:
					this.state.setStateElementValue(name, initial, formId);
					input.value = initial;
					break;
			}

			this.setFieldFilledState(formId, name);
			this.unsetFieldError(formId, name);
		}

		// Remove focus from last input.
		document.activeElement.blur();

		// Dispatch event.
		this.dispatchFormEvent(formId, this.state.getStateEventsAfterFormSubmitReset());
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
			const type = this.state.getStateElementTypeCustom(name, formId);

			// If checkbox split multiple.
			if (type === 'checkbox') {
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

		this.redirectToUrlByRefference(formId, url.href);
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
	redirectToUrlByRefference(formId, redirectUrl, reload = false) {
		this.dispatchFormEvent(formId, this.state.getStateEventsAfterFormSubmitSuccessBeforeRedirect(), redirectUrl);

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
				this.dispatchFormEvent(formId, this.state.getStateEventsFormJsLoaded());
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
			redirectToUrlByRefference: (formId, redirectUrl, reload = false) => {
				this.redirectToUrlByRefference(formId, redirectUrl, reload);
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
		};
	}
}
