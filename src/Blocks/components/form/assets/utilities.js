/* global esFormsLocalization */

import manifest from './../manifest.json';

const {
	componentJsClass,
} = manifest;

/**
 * Main Utilities class.
 */
export class Utils {
	constructor(options = {}) {
		// Prefix.
		this.prefix = options.prefix ?? 'esForms';

		// Detect if form is used in admin for settings or on the frontend.
		this.formIsAdmin = options.formIsAdmin ?? false;

		// Form endpoint to send data.
		this.formSubmitRestApiUrl = options.formSubmitRestApiUrl ?? esFormsLocalization.formSubmitRestApiUrl ?? '';

		// Selectors.
		this.formSelector = options.formSelector ?? `.${componentJsClass}`;

		// Specific selectors.
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

		// Custom fields params.
		this.FORM_PARAMS = options.customFormParams ?? esFormsLocalization.customFormParams ?? {};

		// Custom data attributes.
		this.DATA_ATTRIBUTES = options.customFormDataAttributes ?? esFormsLocalization.customFormDataAttributes ?? {};

		// Settings options from the backend.
		this.SETTINGS = {
			FORM_DISABLE_SCROLL_TO_FIELD_ON_ERROR: options.formDisableScrollToFieldOnError ?? esFormsLocalization.formDisableScrollToFieldOnError ?? true,
			FORM_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS: options.formDisableScrollToGlobalMessageOnSuccess ?? true,
			FORM_RESET_ON_SUCCESS: Boolean(options.formResetOnSuccess ?? esFormsLocalization.formResetOnSuccess ?? false),
			REDIRECTION_TIMEOUT: options.redirectionTimeout ?? esFormsLocalization.redirectionTimeout ?? 600,
			HIDE_GLOBAL_MESSAGE_TIMEOUT: options.hideGlobalMessageTimeout ?? esFormsLocalization.hideGlobalMessageTimeout ?? 6000,
			HIDE_LOADING_STATE_TIMEOUT: options.hideLoadingStateTimeout ?? esFormsLocalization.hideLoadingStateTimeout ?? 600,
			FILE_CUSTOM_REMOVE_LABEL: options.fileCustomRemoveLabel ?? esFormsLocalization.fileCustomRemoveLabel ?? '',
			FORM_SERVER_ERROR_MSG: options.formServerErrorMsg ?? esFormsLocalization.formServerErrorMsg ?? '',
			CAPTCHA: options.captcha ?? esFormsLocalization.captcha ?? '',
			ENRICHMENT_CONFIG: options.enrichmentConfig ?? esFormsLocalization.enrichmentConfig ?? '[]',
		};

		// All custom events.
		this.EVENTS = {
			BEFORE_FORM_SUBMIT: `${this.prefix}BeforeFormSubmit`,
			AFTER_FORM_SUBMIT: `${this.prefix}AfterFormSubmit`,
			AFTER_FORM_SUBMIT_SUCCESS_REDIRECT: `${this.prefix}AfterFormSubmitSuccessRedirect`,
			AFTER_FORM_SUBMIT_SUCCESS: `${this.prefix}AfterFormSubmitSuccess`,
			AFTER_FORM_SUBMIT_RESET: `${this.prefix}AfterFormSubmitReset`,
			AFTER_FORM_SUBMIT_ERROR: `${this.prefix}AfterFormSubmitError`,
			AFTER_FORM_SUBMIT_ERROR_VALIDATION: `${this.prefix}AfterFormSubmitErrorValidation`,
			AFTER_FORM_SUBMIT_END: `${this.prefix}AfterFormSubmitEnd`,
			AFTER_FORM_EVENTS_CLEAR: `${this.prefix}AfterFormEventsClear`,
			BEFORE_GTM_DATA_PUSH: `${this.prefix}BeforeGtmDataPush`,
			FORMS_JS_LOADED: `${this.prefix}JsLoaded`,
			FORM_JS_LOADED: `${this.prefix}JsFormLoaded`,
		};

		// All form custom state selectors.
		this.SELECTORS = {
			CLASS_ACTIVE: 'is-active',
			CLASS_FILLED: 'is-filled',
			CLASS_LOADING: 'is-loading',
			CLASS_HIDDEN: 'is-hidden',
			CLASS_HAS_ERROR: 'has-error',
		};

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
		this.FILES = {};
		this.CUSTOM_TEXTAREAS = [];
		this.CUSTOM_SELECTS = [];
		this.CUSTOM_FILES = [];

		// Set all public methods.
		this.publicMethods();
	}

	////////////////////////////////////////////////////////////////
	// Public methods
	////////////////////////////////////////////////////////////////

	// Unset global message.
	unsetGlobalMsg(element) {
		const messageContainer = element.querySelector(this.globalMsgSelector);
	
		if (!messageContainer) {
			return;
		}
	
		messageContainer.classList.remove(this.SELECTORS.CLASS_ACTIVE);
		messageContainer.dataset.status = '';
		messageContainer.innerHTML = '';
	}

	// Reset for in general.
	reset(element) {
		const items = element.querySelectorAll(this.errorSelector);

		Array.from(items, item => item.innerHTML = '');

		// Reset all error classes on fields.
		element.querySelectorAll(`.${this.SELECTORS.CLASS_HAS_ERROR}`).forEach((element) => element.classList.remove(this.SELECTORS.CLASS_HAS_ERROR));

		this.unsetGlobalMsg(element);
	}

	// Determine if field is custom type or normal.
	isCustom(element) {
		return element.closest(this.fieldSelector).classList.contains(this.customSelector.substring(1)) && !this.formIsAdmin;
	}

	// Dispatch custom event.
	dispatchFormEvent(element, name) {
		const event = new CustomEvent(name, {
			bubbles: true
		});

		element.dispatchEvent(event);
	}

	// Scroll to specific element.
	scrollToElement(element) {
		if (element !== null) {
			element.scrollIntoView({block: 'start', behavior: 'smooth'});
		}
	}

	// Show loader.
	showLoader(element) {
		const loader = element.querySelector(this.loaderSelector);

		element?.classList?.add(this.SELECTORS.CLASS_LOADING);

		if (!loader) {
			return;
		}

		loader.classList.add(this.SELECTORS.CLASS_ACTIVE);
	}

	// Output all error for fields.
	outputErrors(element, fields) {
		// Set error classes and error text on fields which have validation errors.
		for (const [key] of Object.entries(fields)) {
			const item = element.querySelector(`${this.errorSelector}[data-id="${key}"]`);

			item?.closest(this.fieldSelector).classList.add(this.SELECTORS.CLASS_HAS_ERROR);

			if (item !== null) {
				item.innerHTML = fields[key];
			}
		}

		// Scroll to element if the condition is right.
		if (typeof fields !== 'undefined' && this.SETTINGS.FORM_DISABLE_SCROLL_TO_FIELD_ON_ERROR !== '1') {
			const firstItem = Object.keys(fields)[0];

			this.scrollToElement(element.querySelector(`${this.errorSelector}[data-id="${firstItem}"]`).parentElement);
		}
	}

	// Hide loader.
	hideLoader(element) {
		const loader = element.querySelector(this.loaderSelector);

		setTimeout(() => {
			element?.classList?.remove(this.SELECTORS.CLASS_LOADING);

			if (!loader) {
				return;
			}

			loader.classList.remove(this.SELECTORS.CLASS_ACTIVE);
		}, parseInt(this.SETTINGS.HIDE_LOADING_STATE_TIMEOUT, 10));
	}

	// Set global message.
	setGlobalMsg(element, msg, status) {
		if(element.hasAttribute(this.DATA_ATTRIBUTES.successRedirect) && status === 'success') {
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
		if (status === 'success' && this.SETTINGS.FORM_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS !== '1') {
			this.scrollToElement(messageContainer);
		}
	}

	// Hide global message.
	hideGlobalMsg(element) {
		const messageContainer = element.querySelector(this.globalMsgSelector);

		if (!messageContainer) {
			return;
		}

		messageContainer.classList.remove(this.SELECTORS.CLASS_ACTIVE);
	}

	// Build GTM data for the data layer.
	getGtmData(element, eventName) {
		const items = element.querySelectorAll(`[${this.DATA_ATTRIBUTES.tracking}]`);
		const dataTemp = {};

		if (!items.length) {
			return {};
		}

		[...items].forEach((item) => {
			const tracking = item.getAttribute(this.DATA_ATTRIBUTES.tracking);

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
				if (item.hasAttribute(this.DATA_ATTRIBUTES.trackingSelectLabel)) {
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
	gtmSubmit(element) {
		const eventName = element.getAttribute(this.DATA_ATTRIBUTES.trackingEventName);

		if (eventName) {
			const gtmData = this.getGtmData(element, eventName);

			if (window?.dataLayer && gtmData?.event) {
				this.dispatchFormEvent(element, this.EVENTS.BEFORE_GTM_DATA_PUSH);
				window.dataLayer.push(gtmData);
			}
		}
	}

	// Prefill inputs active/filled on init.
	preFillOnInit(input, type) {
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
	}

	// Reset form values if the condition is right.
	resetForm(element) {
		if (this.SETTINGS.FORM_RESET_ON_SUCCESS) {
			element.reset();

			const formId = element.getAttribute(this.DATA_ATTRIBUTES.formPostId);

			// Unset the choices in the submitted form.
			if (this.CUSTOM_SELECTS[formId]) {
				this.CUSTOM_SELECTS[formId].forEach((item) => {
					item.setChoiceByValue('');
				});
			}

			// Unset the choices in the submitted form.
			if (this.CUSTOM_FILES[formId]) {
				this.CUSTOM_FILES[formId].forEach((item) => {
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
			this.dispatchFormEvent(element, this.EVENTS.AFTER_FORM_SUBMIT_RESET);
		}
	}

	// Redirect to url and update url params from from data.
	redirectToUrl(element, formData) {
		let redirectUrl = element.getAttribute(this.DATA_ATTRIBUTES.successRedirect) ?? '';

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
		}, parseInt(this.SETTINGS.REDIRECTION_TIMEOUT, 10));
	}

	// Check if captcha is used.
	isCaptchaUsed() {
		if (this.SETTINGS.CAPTCHA) {
			return true;
		}

		return false;
	}

	////////////////////////////////////////////////////////////////
	// Events callback
	////////////////////////////////////////////////////////////////

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

	////////////////////////////////////////////////////////////////
	// Private methods - not shared to the public window object.
	////////////////////////////////////////////////////////////////

	/**
	 * Set all public methods.
	 * 
	 * @private
	 */
	 publicMethods() {
		if (typeof window[this.prefix] === 'undefined') {
			window[this.prefix] = {};
		}

		if (typeof window[this.prefix]?.utils === 'undefined') {
			window[this.prefix].utils = {
				prefix: this.prefix,
				formIsAdmin: this.formIsAdmin,
				formSubmitRestApiUrl: this.formSubmitRestApiUrl,
				formSelector: this.formSelector,

				submitSingleSelector: this.submitSingleSelector,
				errorSelector: this.errorSelector,
				loaderSelector: this.loaderSelector,
				globalMsgSelector: this.globalMsgSelector,
				groupSelector: this.groupSelector,
				groupInnerSelector: this.groupInnerSelector,
				customSelector: this.customSelector,
				fieldSelector: this.fieldSelector,
				inputSelector: this.inputSelector,
				textareaSelector: this.textareaSelector,
				selectSelector: this.selectSelector,
				fileSelector: this.fileSelector,

				FORM_PARAMS: this.FORM_PARAMS,
				DATA_ATTRIBUTES: this.DATA_ATTRIBUTES,
				SETTINGS: this.SETTINGS,
				EVENTS: this.EVENTS,
				SELECTORS: this.SELECTORS,
				CONDITIONAL_TAGS: this.CONDITIONAL_TAGS,
				FILES: this.FILES,
				CUSTOM_TEXTAREAS: this.CUSTOM_TEXTAREAS,
				CUSTOM_SELECTS: this.CUSTOM_SELECTS,
				CUSTOM_FILES: this.CUSTOM_FILES,

				unsetGlobalMsg: (element) => {
					this.unsetGlobalMsg(element);
				},
				reset: (element) => {
					this.reset(element);
				},
				isCustom: (element) => {
					this.isCustom(element);
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
				getGtmData: (element, eventName) => {
					this.getGtmData(element, eventName);
				},
				gtmSubmit: (element) => {
					this.gtmSubmit(element);
				},
				preFillOnInit: (input, type) => {
					this.preFillOnInit(input, type);
				},
				resetForm: (element) => {
					this.resetForm(element);
				},
				redirectToUrl: (element, formData) => {
					this.redirectToUrl(element, formData);
				},
				isCaptchaUsed: () => {
					this.isCaptchaUsed();
				},
				onFocusEvent: (event) => {
					this.onFocusEvent(event);
				},
				onBlurEvent: (event) => {
					this.onBlurEvent(event);
				},
			};
		}
	 }
}
