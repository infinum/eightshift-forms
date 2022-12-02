/* global esFormsLocalization */

import manifest from './../manifest.json';

if (typeof esFormsLocalization === 'undefined') {
	throw 'Your project is missing global variable esFormsLocalization called from the enqueue script in the forms.';
}

const {
	componentJsClass,
} = manifest;

/**
 * Main Utilities class.
 */
export class Utils {
	constructor(options = {}) {
		// Prefix.
		this.ePrefix = options.ePrefix ?? 'esForms';

		// Detect if form is used in admin for settings or on the frontend.
		this.formIsAdmin = options.formIsAdmin ?? false;

		// Form endpoint to send data.
		this.formSubmitRestApiUrl = options.formSubmitRestApiUrl ?? esFormsLocalization.formSubmitRestApiUrl ?? '';

		// Selectors.
		this.formSelector = options.formSelector ?? `.${componentJsClass}`;

		this.submitSingleSelector =  `${this.formSelector}-single-submit`;
		this.errorSelector =  `${this.formSelector}-error`;
		this.loaderSelector =  `${this.formSelector}-loader`;
		this.globalMsgSelector =  `${this.formSelector}-global-msg`;
		this.groupSelector =  `${this.formSelector}-group`;
		this.groupInnerSelector =  `${this.formSelector}-group-inner`;
		this.customSelector =  `${this.formSelector}-custom`;
		this.fieldSelector =  `${this.formSelector}-field`;
		this.inputSelector =  `${this.fieldSelector} input`;
		this.textareaSelector =  `${this.fieldSelector} textarea`;
		this.selectSelector =  `${this.fieldSelector} select`;
		this.fileSelector =  `${this.fieldSelector} input[type='file']`;
		this.conditionalTagsSelector =  `${this.fieldSelector} input[id='conditional-tags']`;

		// LocalStorage.
		this.STORAGE_NAME = options.STORAGE_NAME ?? 'es-storage';
		this.storageName = this.STORAGE_NAME;

		// Custom fields params.
		this.FORM_CUSTOM_FORM_PARAMS = options.customFormParams ?? esFormsLocalization.customFormParams ?? {};
		this.customFormParams = this.FORM_CUSTOM_FORM_PARAMS;

		// Custom data attributes.
		this.FORM_CUSTOM_DATA_ATTRIBUTES = options.customFormDataAttributes ?? esFormsLocalization.customFormDataAttributes ?? {};
		this.customFormDataAttributes = this.FORM_CUSTOM_DATA_ATTRIBUTES;

		// Settings options.
		this.formDisableScrollToFieldOnError = options.formDisableScrollToFieldOnError ?? esFormsLocalization.formDisableScrollToFieldOnError ?? true;
		this.formDisableScrollToGlobalMessageOnSuccess = options.formDisableScrollToGlobalMessageOnSuccess ?? true;
		this.formResetOnSuccess = Boolean(options.formResetOnSuccess ?? esFormsLocalization.formResetOnSuccess ?? false);
		this.redirectionTimeout = options.redirectionTimeout ?? esFormsLocalization.redirectionTimeout ?? 600;
		this.hideGlobalMessageTimeout = options.hideGlobalMessageTimeout ?? esFormsLocalization.hideGlobalMessageTimeout ?? 6000;
		this.hideLoadingStateTimeout = options.hideLoadingStateTimeout ?? esFormsLocalization.hideLoadingStateTimeout ?? 600;
		this.fileCustomRemoveLabel = options.fileCustomRemoveLabel ?? esFormsLocalization.fileCustomRemoveLabel ?? '';
		this.formServerErrorMsg = options.formServerErrorMsg ?? esFormsLocalization.formServerErrorMsg ?? '';
		this.captcha = options.captcha ?? esFormsLocalization.captcha ?? '';
		this.storageConfig = options.storageConfig ?? esFormsLocalization.storageConfig ?? '';

		/**
		 * All custom events.
		 */
		this.EVENTS = {
			BEFORE_FORM_SUBMIT: `${this.ePrefix}BeforeFormSubmit`,
			AFTER_FORM_SUBMIT: `${this.ePrefix}AfterFormSubmit`,
			AFTER_FORM_SUBMIT_SUCCESS_REDIRECT: `${this.ePrefix}AfterFormSubmitSuccessRedirect`,
			AFTER_FORM_SUBMIT_SUCCESS: `${this.ePrefix}AfterFormSubmitSuccess`,
			AFTER_FORM_SUBMIT_RESET: `${this.ePrefix}AfterFormSubmitReset`,
			AFTER_FORM_SUBMIT_ERROR: `${this.ePrefix}AfterFormSubmitError`,
			AFTER_FORM_SUBMIT_ERROR_FATAL: `${this.ePrefix}AfterFormSubmitErrorFatal`,
			AFTER_FORM_SUBMIT_ERROR_VALIDATION: `${this.ePrefix}AfterFormSubmitErrorValidation`,
			AFTER_FORM_SUBMIT_END: `${this.ePrefix}AfterFormSubmitEnd`,
			AFTER_EVENTS_CLEAR: `${this.ePrefix}AfterFormEventsClear`,
			BEFORE_GTM_DATA_PUSH: `${this.ePrefix}BeforeGtmDataPush`,
			FORMS_JS_LOADED: `${this.ePrefix}JsLoaded`,
		};

		/**
		* All form custom state selectors.
		*/
		this.SELECTORS = {
		CLASS_ACTIVE: 'is-active',
		CLASS_FILLED: 'is-filled',
		CLASS_LOADING: 'is-loading',
		CLASS_HIDDEN: 'is-hidden',
		CLASS_HAS_ERROR: 'has-error',
		};
		this.selectors = this.SELECTORS;

		/**
		 * Data constants.
		 *
		 * show - show item it conditions is set, hidden by default.
		 * hide - hide item it conditions is set, visible by default.
		 *
		 * all - activate condition if all conditions in rules array are met.
		 * any - activate condition if at least one condition in rules array is met.
		 *
		 * is  - is                  - if value is exact match.
		 * isn - is not              - if value is not exact match.
		 * gt  - greater than        - if value is greater than.
		 * gte  - greater/equal than - if value is greater/equal than.
		 * lt  - less than           - if value is less than.
		 * lte  - less/equal than    - if value is less/equal than.
		 * c   - contains            - if value contains value.
		 * sw  - starts with         - if value starts with value.
		 * ew  - ends with           - if value starts with value.
		 */
		this.CONDITIONAL_TAGS = {
			IS: 'is',
			ISN: 'isn',
			GT: 'gt',
			GTE: 'gte',
			LT: 'lt',
			LTE: 'lte',
			C: 'c',
			SW: 'sw',
			EW: 'ew',
			SHOW: 'show',
			HIDE: 'hide',
			ALL: 'all',
			ANY: 'any',
		};

		// Internal state.
		this.files = {};
		this.customTextareas = [];
		this.customSelects = [];
		this.customFiles = [];
	}

	// Unset global message.
	unsetGlobalMsg = (element) => {
		const messageContainer = element.querySelector(this.globalMsgSelector);
	
		if (!messageContainer) {
			return;
		}
	
		messageContainer.classList.remove(this.SELECTORS.CLASS_ACTIVE);
		messageContainer.dataset.status = '';
		messageContainer.innerHTML = '';
	};

	// Reset for in general.
	reset = (element) => {
		const items = element.querySelectorAll(this.errorSelector);
		[...items].forEach((item) => {
			item.innerHTML = '';
		});
		// Reset all error classes on fields.
		element.querySelectorAll(`.${this.SELECTORS.CLASS_HAS_ERROR}`).forEach((element) => element.classList.remove(this.SELECTORS.CLASS_HAS_ERROR));

		this.unsetGlobalMsg(element);
	};

	// Determine if field is custom type or normal.
	isCustom = (element) => {
		return element.closest(this.fieldSelector).classList.contains(this.customSelector.substring(1)) && !this.formIsAdmin;
	};

	// Dispatch custom event.
	dispatchFormEvent = (element, name) => {
		const event = new CustomEvent(name, {
			bubbles: true
		});

		element.dispatchEvent(event);
	}
	// Scroll to specific element.
	scrollToElement = (element) => {
		if (element !== null) {
			element.scrollIntoView({block: 'start', behavior: 'smooth'});
		}
	};

	// Show loader.
	showLoader = (element) => {
		const loader = element.querySelector(this.loaderSelector);

		element?.classList?.add(this.SELECTORS.CLASS_LOADING);

		if (!loader) {
			return;
		}

		loader.classList.add(this.SELECTORS.CLASS_ACTIVE);
	};

	// Output all error for fields.
	outputErrors = (element, fields) => {
		// Set error classes and error text on fields which have validation errors.
		for (const [key] of Object.entries(fields)) {
			const item = element.querySelector(`${this.errorSelector}[data-id="${key}"]`);

			item?.closest(this.fieldSelector).classList.add(this.SELECTORS.CLASS_HAS_ERROR);

			if (item !== null) {
				item.innerHTML = fields[key];
			}
		}

		// Scroll to element if the condition is right.
		if (typeof fields !== 'undefined' && this.formDisableScrollToFieldOnError !== '1') {
			const firstItem = Object.keys(fields)[0];

			this.scrollToElement(element.querySelector(`${this.errorSelector}[data-id="${firstItem}"]`).parentElement);
		}
	};

	// Hide loader.
	hideLoader = (element) => {
		const loader = element.querySelector(this.loaderSelector);

		setTimeout(() => {
			element?.classList?.remove(this.SELECTORS.CLASS_LOADING);

			if (!loader) {
				return;
			}

			loader.classList.remove(this.SELECTORS.CLASS_ACTIVE);
		}, parseInt(this.hideLoadingStateTimeout, 10));
	};

	// Set global message.
	setGlobalMsg = (element, msg, status) => {
		console.log(element);
		if(element.hasAttribute(this.FORM_CUSTOM_DATA_ATTRIBUTES.successRedirect) && status === 'success') {
			return;
		}

		const messageContainer = element.querySelector(this.globalMsgSelector);

		if (!messageContainer) {
			return;
		}

		messageContainer.classList.add(this.SELECTORS.CLASS_ACTIVE);
		messageContainer.dataset.status = status;
		messageContainer.innerHTML = `<span>${msg}</span>`;

		// Scroll to msg if the condition is right.
		if (status === 'success' && this.formDisableScrollToGlobalMessageOnSuccess !== '1') {
			this.scrollToElement(messageContainer);
		}
	};

	// Hide global message.
	hideGlobalMsg = (element) => {
		const messageContainer = element.querySelector(this.globalMsgSelector);

		if (!messageContainer) {
			return;
		}

		messageContainer.classList.remove(this.SELECTORS.CLASS_ACTIVE);
	}

	// Build GTM data for the data layer.
	getGtmData = (element, eventName) => {
		const items = element.querySelectorAll(`[${this.FORM_CUSTOM_DATA_ATTRIBUTES.tracking}]`);
		const dataTemp = {};

		if (!items.length) {
			return {};
		}

		[...items].forEach((item) => {
			const tracking = item.getAttribute(this.FORM_CUSTOM_DATA_ATTRIBUTES.tracking);

			if (tracking) {
				const {type, checked} = item;

				if (typeof dataTemp[tracking] === 'undefined') {
					if (type === 'checkbox') {
						dataTemp[tracking] = [];
					} else {
						dataTemp[tracking] = '';
					}
				}

				if ((type === 'checkbox' || type === 'radio') && !checked) {
					return;
				}

				// Check if you have this data attr and if so use select label.
				if (item.hasAttribute(this.FORM_CUSTOM_DATA_ATTRIBUTES.trackingSelectLabel)) {
					dataTemp[tracking] = item.selectedOptions[0].label;
					return;
				}

				if (type === 'checkbox') {
					dataTemp[tracking].push(item.value);
					return;
				}

				dataTemp[tracking] = item.value;
			}
		});

		const data = {};

		for (const [key, value] of Object.entries(dataTemp)) {
			if (Array.isArray(value)) {
				switch (value.length) {
					case 0:
						data[key] = false;
						break;
					case 1:
						if (value[0] === 'on') {
							data[key] = true;
						} else {
							data[key] = value;
						}
						break;
					default:
						data[key] = value;
						break;
				}
			} else {
				data[key] = value;
			}
		}

		return Object.assign({}, { event: eventName, ...data });
	}

	// Submit GTM event.
	gtmSubmit = (element) => {
		const eventName = element.getAttribute(this.FORM_CUSTOM_DATA_ATTRIBUTES.trackingEventName);

		if (eventName) {
			const gtmData = this.getGtmData(element, eventName);

			if (window?.dataLayer && gtmData?.event) {
				this.dispatchFormEvent(element, this.EVENTS.BEFORE_GTM_DATA_PUSH);
				window.dataLayer.push(gtmData);
			}
		}
	}

	// Prefill inputs active/filled on init.
	preFillOnInit = (input, type) => {
		switch (type) {
			case 'checkbox':
			case 'radio':
				if (input.checked) {
					input.closest(this.fieldSelector).classList.add(this.SELECTORS.CLASS_FILLED);
				}
				break;
			case 'select-custom': {
				const customSelect = input.config.choices;

				if (customSelect.some((item) => item.selected === true && item.value !== '')) {
					input.passedElement.element.closest(this.fieldSelector).classList.add(this.SELECTORS.CLASS_FILLED);
				}
				break;
			}
			default:
				if (input.value && input.value.length) {
					input.closest(this.fieldSelector).classList.add(this.SELECTORS.CLASS_FILLED);
				}
				break;
		}
	};

	// On Focus event for regular fields.
	onFocusEvent = (event) => {
		event.target.closest(this.fieldSelector).classList.add(this.SELECTORS.CLASS_ACTIVE);
	};

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
			field.classList.remove(this.SELECTORS.CLASS_ACTIVE);
			field.classList.add(this.SELECTORS.CLASS_FILLED);
		} else {
			field.classList.remove(this.SELECTORS.CLASS_ACTIVE, this.SELECTORS.CLASS_FILLED);
		}
	};

	// Set local storage value.
	setLocalStorage = () => {
		// If storage is not set in the backend bailout.
		// Backend provides the ability to limit what tags are allowed to store in local storage.
		if (this.storageConfig === '') {
			return;
		}

		const storageConfig = JSON.parse(this.storageConfig);

		const allowedTags = storageConfig?.allowed;
		const expiration = storageConfig?.expiration ?? '30';

		// Missing data from backend, bailout.
		if (!allowedTags) {
			return;
		}

		// Bailout if nothing is set in the url.
		if (!window.location.search) {
			return;
		}

		// Find url params.
		const searchParams = new URLSearchParams(window.location.search);

		console.log(searchParams.entries());

		// Get storage from backend this is considered new by the page request.
		const newStorage = {};

		// Loop entries and get new storage values.
		for (const [key, value] of searchParams.entries()) {
			// Bailout if not allowed or empty
			if (!allowedTags.includes(key) || value === '') {
				continue;
			}

			// Add valid tag.
			newStorage[key] = value;
		}

		// Bailout if nothing is set from allowed tags or everything is empty.
		if (Object.keys(newStorage).length === 0) {
			return;
		}

		// Add current timestamp to new storage.
		newStorage.timestamp = Date.now();

		// Store in a new variable for later usage.
		const newStorageFinal = {...newStorage};
		delete newStorageFinal.timestamp;

		// current storage is got from local storage.
		const currentStorage = JSON.parse(getLocalStorage());

		// Store in a new variable for later usage.
		const currentStorageFinal = {...currentStorage};
		delete currentStorageFinal.timestamp;

		// If storage exists check if it is expired.
		if (getLocalStorage() !== null) {
			// Update expiration date by number of days from the current
			let expirationDate = new Date(currentStorage.timestamp);
			expirationDate.setDate(expirationDate.getDate() + parseInt(expiration, 10));

			// Remove expired storage if it exists.
			if (expirationDate.getTime() < currentStorage.timestamp) {
				localStorage.removeItem(this.STORAGE_NAME);
			}
		}

		// Create new storage if this is the first visit or it was expired.
		if (getLocalStorage() === null) {
			localStorage.setItem(
				this.STORAGE_NAME,
				JSON.stringify(newStorage)
			);
			return;
		}

		// Prepare new output.
		const output = {
			...currentStorageFinal,
			...newStorageFinal,
		};

		// If output is empty something was wrong here and just bailout.
		if (Object.keys(output).length === 0) {
			return;
		}

		// If nothing has changed bailout.
		if (JSON.stringify(currentStorageFinal) === JSON.stringify(output)) {
			return;
		}

		// Add timestamp to the new output.
		const finalOutput = {
			...output,
			timestamp: newStorage.timestamp,
		};

		// Update localStorage with the new item.
		localStorage.setItem(this.STORAGE_NAME, JSON.stringify(finalOutput));
	}

	// Get local storage value.
	getLocalStorage = () => {
		return localStorage.getItem(this.STORAGE_NAME);
	}

	// Reset form values if the condition is right.
	resetForm = (element) => {
		if (this.formResetOnSuccess) {
			element.reset();

			const formId = element.getAttribute(this.FORM_CUSTOM_DATA_ATTRIBUTES.formPostId);

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

			const fields = element.querySelectorAll(this.fieldSelector);

			[...fields].forEach((item) => {
				item.classList.remove(this.SELECTORS.CLASS_FILLED);
				item.classList.remove(this.SELECTORS.CLASS_ACTIVE);
				item.classList.remove(this.SELECTORS.CLASS_HAS_ERROR);
			});

			// Remove focus from last input.
			document.activeElement.blur();

			// Dispatch event.
			dispatchFormEvent(element, this.EVENTS.AFTER_FORM_SUBMIT_RESET);
		}
	};

	// Redirect to url and update url params from from data.
	redirectToUrl = (element, formData) => {
		let redirectUrl = element.getAttribute(this.FORM_CUSTOM_DATA_ATTRIBUTES.successRedirect) ?? '';

		// Replace string templates used for passing data via url.
		for (var [key, val] of formData.entries()) { // eslint-disable-line no-unused-vars
			if (typeof val === 'string') {
				const { value, name } = JSON.parse(val);
				redirectUrl = redirectUrl.replaceAll(`{${name}}`, encodeURIComponent(value));
			}
		}

		// Do the actual redirect after some time.
		setTimeout(() => {
			window.location.href = redirectUrl;
		}, parseInt(this.redirectionTimeout, 10));
	}
}
