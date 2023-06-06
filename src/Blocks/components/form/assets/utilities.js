import { State, prefix } from './state';

/**
 * Main Utilities class.
 */
export class Utils {
	constructor(options = {}) {
		this.state = new State(options);

		// Set all public methods.
		this.publicMethods();
	}


	////////////////////////////////////////////////////////////////
	// Public methods
	////////////////////////////////////////////////////////////////

	// Reset form in general.
	resetErrors(formId) {
		for (const [name] of this.state.getStateElements(formId)) {
			this.unsetFieldError(name, formId)
		};

		this.unsetGlobalMsg(formId);
	}

	// Dispatch custom event.
	dispatchFormEvent(formId, name, detail) {
		const options = {
			bubbles: true,
		};

		if (!isNaN(formId)) {
			options['detail'] = {
				formId,
			};
		}

		if (detail) {
			options['detail'] = {
				...options['detail'],
				options: detail,
			}
		}

		if (!isNaN(formId)) {
			this.state.getStateFormElement(formId).dispatchEvent(new CustomEvent(name, options));
		} else {
			formId.dispatchEvent(new CustomEvent(name, options));
		}
	}

	// Scroll to specific element.
	scrollToElement(name, formId) {
		this.state.getStateElementField(name, formId).scrollIntoView({block: 'start', behavior: 'smooth'});
	}

	scrollToGlobalMsg(formId) {
		this.state.getStateFormGlobalMsgElement(formId).scrollIntoView({block: 'start', behavior: 'smooth'});
	}

	// Show loader.
	showLoader(formId) {
		this.state.getStateFormElement(formId).classList.add(this.state.getStateSelectorsClassLoading());
		this.state.getStateFormLoader(formId).classList.add(this.state.getStateSelectorsClassActive());
	}

	// Remove one field error by name.
	unsetFieldError(name, formId) {
		const error = this.state.getStateElementError(name, formId);

		error.classList.remove(this.state.getStateSelectorsClassHasError());
		this.state.setState([this.state.ELEMENTS, name, this.state.HAS_ERROR], false, formId);
		error.innerHTML = '';
	}

	
	setFieldError(name, msg, formId) {
		const error = this.state.getStateElementError(name, formId);

		error.classList.add(this.state.getStateSelectorsClassHasError());
		this.state.setState([this.state.ELEMENTS, name, this.state.HAS_ERROR], true, formId);
		error.innerHTML = msg;
	}

	// Output all error for fields.
	outputErrors(formId, data) {
		for (const [name, msg] of Object.entries(data)) {
			this.setFieldError(name, msg, formId);
		};

		// Scroll to element if the condition is right.
		const firstItemWithErrorName = this.state.getStateFilteredBykey(this.state.ELEMENTS, this.state.HAS_ERROR, true, formId)?.[0]?.[this.state.NAME];
		if (firstItemWithErrorName && !this.state.getStateSettingsDisableScrollToFieldOnError()) {
			this.scrollToElement(firstItemWithErrorName, formId);
		}
	}

	// Hide loader.
	hideLoader(formId) {
		this.state.getStateFormElement(formId).classList.remove(this.state.getStateSelectorsClassLoading());
		this.state.getStateFormLoader(formId).classList.remove(this.state.getStateSelectorsClassActive());
	}

	// Unset global message.
	unsetGlobalMsg(formId) {
		const messageContainer = this.state.getStateFormGlobalMsgElement(formId);

		messageContainer.classList.remove(this.state.getStateSelectorsClassActive());
		messageContainer.dataset.status = '';
		messageContainer.innerHTML = '';
	}

	// Set global message.
	setGlobalMsg(formId, msg, status) {
		const messageContainer = this.state.getStateFormGlobalMsgElement(formId);

		messageContainer.classList.add(this.state.getStateSelectorsClassActive());
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

	// Build GTM data for the data layer.
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
					let telValue = value;

					if (!this.state.getStateFormConfigPhoneDisablePicker(formId) && value) {
						telValue = `${valueCountry.number}${value}`;
					}

					output[trackingName] = value ? telValue : '';
					break
				default:
					output[trackingName] = value ?? '';
					break;
			}
		}

		return Object.assign({}, { event: this.state.getStateFormTrackingEventName(formId), ...output });
	}

	// Submit GTM event.
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

	// Prefill inputs active/filled on init.
	setFieldFilledState(name, formId) {
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
			this.unsetActiveState(name, formId);
			this.setFilledState(name, formId);
		} else {
			this.unsetActiveState(name, formId);
			this.unsetFilledState(name, formId);
		}
	}

	setFieldActiveState(name, formId) {
		this.state.getStateElementField(name, formId).classList.add(this.state.getStateSelectorsClassActive());
	}

	unsetActiveState(name, formId) {
		this.state.getStateElementField(name, formId).classList.remove(this.state.getStateSelectorsClassActive());
	}

	setFilledState(name, formId) {
		this.state.getStateElementField(name, formId).classList.add(this.state.getStateSelectorsClassFilled());
	}

	unsetFilledState(name, formId) {
		this.state.getStateElementField(name, formId).classList.remove(this.state.getStateSelectorsClassFilled());
	}

	// Reset form values if the condition is right.
	resetForm(formId) {
		if (!this.state.getStateSettingsResetOnSuccess(formId)) {
			return;
		}

		for (const [name, item] of this.state.getStateElements(formId)) {
			const type = item[this.state.TYPE];
			const custom = item[this.state.CUSTOM];
			const input = item[this.state.INPUT];
			const items = item[this.state.ITEMS];
			const initial = item[this.state.INITIAL]

			switch (type) {
				case 'checkbox':
					this.state.setState([this.state.ELEMENTS, name, this.state.VALUE], {...initial}, formId);

					for(const [innerName, innerValue] of Object.entries(initial)) {
						items[innerName].input.checked = innerValue !== '';
					};
					break;
				case 'radio':
					this.state.setState([this.state.ELEMENTS, name, this.state.VALUE], initial, formId);

					if (initial === '') {
						Object.values(items).forEach((inner) => {
							inner.input.checked = false;
						});
					} else {
						items[initial].input.checked = true;
					}
					break;
				case 'file':
					this.state.setState([this.state.ELEMENTS, name, this.state.VALUE], initial, formId);
					custom.removeAllFiles();
					break;
				case 'select':
					this.state.setState([this.state.ELEMENTS, name, this.state.VALUE], initial, formId);
					custom.setChoiceByValue(initial);
					break;
				case 'date':
					this.state.setState([this.state.ELEMENTS, name, this.state.VALUE], initial, formId);
					custom.setDate(initial);
					break;
				default:
					this.state.setState([this.state.ELEMENTS, name, this.state.VALUE], initial, formId);
					input.value = initial;
					break;
			}

			this.setFieldFilledState(name, formId);
			this.unsetFieldError(name, formId);
		}

		// Remove focus from last input.
		document.activeElement.blur();

		// Dispatch event.
		this.dispatchFormEvent(formId, this.state.getStateEventsAfterFormSubmitReset());
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

		this.redirectToUrlByRefference(url.href, formId);
	}

	// Redirect to url by provided path.
	redirectToUrlByRefference(redirectUrl, formId, reload = false) {
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

	// Check if form is fully loaded.
	isFormLoaded(formId) {
		const interval = setInterval(() => {
			if (this.state.getStateFilteredBykey(this.state.ELEMENTS, this.state.LOADED, false, formId).length === 0) {
				clearInterval(interval);

				this.state.setState([this.state.FORM, this.state.ISLOADED], true, formId);

				// Triger event that form is fully loaded.
				this.dispatchFormEvent(formId, this.state.getStateEventsFormJsLoaded());
			}
		}, 100);
	}

	getSaveAsJsonFormatOutput(name, formId) {
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
	 * 
	 * @param {*} file 
	 * @returns 
	 */
	getFileNameFromFileObject(file) {
		const fileExt = file.upload.filename.split('.').slice(-1)?.[0];

		return `${file.upload.uuid}.${fileExt}`;
	}

	////////////////////////////////////////////////////////////////
	// Private methods - not shared to the public window object.
	////////////////////////////////////////////////////////////////

	/**
	 * Set all public methods.
	 *
	 * @private
	 */
	publicMethods() {
		this.state.setStateWindow();

		window[prefix].utils = {}
	}
}
