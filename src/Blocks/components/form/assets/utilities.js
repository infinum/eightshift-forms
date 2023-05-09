/* global esFormsLocalization */

import manifest from './../manifest.json';
import selectManifest from './../../select/manifest.json';

const {
	componentJsClass,
} = manifest;

/**
 * Conditional tags operators constants.
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
export const CONDITIONAL_TAGS_OPERATORS = {
	IS: 'is',
	ISN: 'isn',
	GT: 'gt',
	GTE: 'gte',
	LT: 'lt',
	LTE: 'lte',
	C: 'c',
	SW: 'sw',
	EW: 'ew',
};

/**
 * Conditional tags actions constants.
 *
 * show - show item it conditions is set, hidden by default.
 * hide - hide item it conditions is set, visible by default.
 */
export const CONDITIONAL_TAGS_ACTIONS = {
	SHOW: 'show',
	HIDE: 'hide',
};

/**
 * Conditional tags logic constants.
 *
 * all - activate condition if all conditions in rules array are met.
 * any - activate condition if at least one condition in rules array is met.
 */
export const CONDITIONAL_TAGS_LOGIC = {
	ALL: 'all',
	ANY: 'any',
};

/**
 * Main Utilities class.
 */
export class Utils {
	constructor(options = {}) {
		// Prefix.
		this.prefix = 'esForms';

		// Detect if form is used in admin for settings or on the frontend.
		this.formIsAdmin = options.formIsAdmin ?? false;

		// Form endpoint to send data.
		this.formSubmitRestApiUrl = options.formSubmitRestApiUrl ?? `${esFormsLocalization.restPrefix}/${esFormsLocalization.restRoutes.formSubmit}`;

		// Selectors.
		this.formSelectorPrefix = options.formSelectorPrefix ?? `.${componentJsClass}`;

		// Specific selectors.
		this.formSelector =  this.formSelectorPrefix;
		this.submitSingleSelector =  `${this.formSelectorPrefix}-single-submit`;
		this.stepSelector =  `${this.formSelectorPrefix}-step`;
		this.stepSubmitSelector =  `${this.formSelectorPrefix}-step-trigger`;
		this.errorSelector =  `${this.formSelectorPrefix}-error`;
		this.loaderSelector =  `${this.formSelectorPrefix}-loader`;
		this.globalMsgSelector =  `${this.formSelectorPrefix}-global-msg`;
		this.groupSelector =  `${this.formSelectorPrefix}-group`;
		this.fieldSelector =  `${this.formSelectorPrefix}-field`;
		this.dateFieldSelector =  `${this.formSelectorPrefix}-date`;
		this.countryFieldSelector =  `${this.formSelectorPrefix}-county`;
		this.inputSelector =  `${this.fieldSelector} input`;
		this.textareaSelector =  `${this.fieldSelector} textarea`;
		this.selectSelector =  `${this.fieldSelector} select`;
		this.fileSelector =  `${this.fieldSelector} input[type='file']`;

		// Class names.
		this.selectClassName = selectManifest.componentClass;

		// Custom fields params.
		this.FORM_PARAMS = options.customFormParams ?? esFormsLocalization.customFormParams ?? {};

		// Custom data attributes.
		this.DATA_ATTRIBUTES = options.customFormDataAttributes ?? esFormsLocalization.customFormDataAttributes ?? {};

		// Settings options from the backend.
		this.SETTINGS = {
			FORM_DISABLE_SCROLL_TO_FIELD_ON_ERROR: Boolean(options.formDisableScrollToFieldOnError ?? esFormsLocalization.formDisableScrollToFieldOnError),
			FORM_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS: Boolean(options.formDisableScrollToGlobalMessageOnSuccess ?? esFormsLocalization.formDisableScrollToGlobalMessageOnSuccess),
			FORM_DISABLE_NATIVE_REDIRECT_ON_SUCCESS: Boolean(options.formDisableNativeRedirectOnSuccess ?? esFormsLocalization.formDisableNativeRedirectOnSuccess),
			FORM_RESET_ON_SUCCESS: Boolean(options.formResetOnSuccess ?? esFormsLocalization.formResetOnSuccess),
			REDIRECTION_TIMEOUT: options.redirectionTimeout ?? esFormsLocalization.redirectionTimeout ?? 600,
			HIDE_GLOBAL_MESSAGE_TIMEOUT: options.hideGlobalMessageTimeout ?? esFormsLocalization.hideGlobalMessageTimeout ?? 6000,
			HIDE_LOADING_STATE_TIMEOUT: options.hideLoadingStateTimeout ?? esFormsLocalization.hideLoadingStateTimeout ?? 600,
			FILE_CUSTOM_REMOVE_LABEL: options.fileCustomRemoveLabel ?? esFormsLocalization.fileCustomRemoveLabel ?? '',
			FORM_SERVER_ERROR_MSG: options.formServerErrorMsg ?? esFormsLocalization.formServerErrorMsg ?? '',
			CAPTCHA: options.captcha ?? esFormsLocalization.captcha ?? [],
			ENRICHMENT_CONFIG: options.enrichmentConfig ?? esFormsLocalization.enrichmentConfig ?? '[]',
		};

		// All custom events.
		this.EVENTS = {
			BEFORE_FORM_SUBMIT: `${this.prefix}BeforeFormSubmit`,
			AFTER_FORM_SUBMIT: `${this.prefix}AfterFormSubmit`,
			AFTER_FORM_SUBMIT_SUCCESS_BEFORE_REDIRECT: `${this.prefix}AfterFormSubmitSuccessBeforeRedirect`,
			AFTER_FORM_SUBMIT_SUCCESS: `${this.prefix}AfterFormSubmitSuccess`,
			AFTER_FORM_SUBMIT_RESET: `${this.prefix}AfterFormSubmitReset`,
			AFTER_FORM_SUBMIT_ERROR: `${this.prefix}AfterFormSubmitError`,
			AFTER_FORM_SUBMIT_ERROR_VALIDATION: `${this.prefix}AfterFormSubmitErrorValidation`,
			AFTER_FORM_SUBMIT_END: `${this.prefix}AfterFormSubmitEnd`,
			AFTER_FORM_EVENTS_CLEAR: `${this.prefix}AfterFormEventsClear`,
			BEFORE_GTM_DATA_PUSH: `${this.prefix}BeforeGtmDataPush`,
			FORMS_JS_LOADED: `${this.prefix}JsLoaded`,
			FORM_JS_LOADED: `${this.prefix}JsFormLoaded`,
			AFTER_CAPTCHA_INIT: `${this.prefix}AfterCaptchaInit`,
		};

		// All form custom state selectors.
		this.SELECTORS = {
			CLASS_ACTIVE: 'is-active',
			CLASS_FILLED: 'is-filled',
			CLASS_LOADING: 'is-loading',
			CLASS_HIDDEN: 'is-hidden',
			CLASS_VISIBLE: 'is-visible',
			CLASS_HAS_ERROR: 'has-error',
		};

		this.DELIMITER = esFormsLocalization.delimiter;

		// Conditional tags
		this.CONDITIONAL_TAGS_OPERATORS = CONDITIONAL_TAGS_OPERATORS;
		this.CONDITIONAL_TAGS_ACTIONS = CONDITIONAL_TAGS_ACTIONS;
		this.CONDITIONAL_TAGS_LOGIC = CONDITIONAL_TAGS_LOGIC;

		// Internal State.
		this.FORMS_STATE = {}

		// Set all public methods.
		this.publicMethods();
	}

	////////////////////////////////////////////////////////////////
	// State callback
	////////////////////////////////////////////////////////////////

	// Get project js prefix.
	getPrefix() {
		return this.prefix;
	}

	// Set state initial.
	setFormStateInitial(formId = 0) {
		if (!window[this.getPrefix()]?.utils?.FORMS_STATE?.[`form_${formId}`]) {
			window[this.getPrefix()].utils.FORMS_STATE = {
				...window[this.getPrefix()].utils.FORMS_STATE,
				[`form_${formId}`]: {
					selects: [],
					textareas: [],
					dates: [],
					files: [],
					isLoaded: false,
				}
			}
		}
	}

	// Get state by key.
	getFormStateByKey(key, formId = 0) {
		return window?.[this.getPrefix()]?.utils?.FORMS_STATE?.[`form_${formId}`]?.[key];
	}

	// Set state by key.
	setFormStateByKey(key, value, formId = 0) {
		if (typeof value === 'boolean') {
			window[this.getPrefix()].utils.FORMS_STATE[`form_${formId}`][key] = value;
		} else {
			window[this.getPrefix()].utils.FORMS_STATE[`form_${formId}`][key].push(value);
		}
	}

	// Delete state item by key
	deleteFormStateByKey(key, formId = 0) {
		this.setFormStateByKey(key, [], formId);
	}

	// Get state by index.
	getFormStateByIndex(key, index, formId = 0) {
		return window?.[this.getPrefix()]?.utils?.FORMS_STATE?.[`form_${formId}`]?.[key]?.[index];
	}

	// Get state by field name.
	getFormStateByName(key, name, formId = 0) {
		return this.getFormStateByKey(key, formId).find((item) => item.esFormsName === name);
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

	// Reset form in general.
	reset(element) {
		// Reset all error classes on fields.
		element.querySelectorAll(`${this.fieldSelector}.${this.SELECTORS.CLASS_HAS_ERROR}`).forEach((item) => {
			item.classList.remove(this.SELECTORS.CLASS_HAS_ERROR);

			const value = item.querySelector(this.errorSelector);

			if (value) {
				value.innerHTML = ''
			}
		});

		this.unsetGlobalMsg(element);
	}

	// Remove one field error by name.
	removeFieldErrorByName(element, name) {
		const item = element.querySelector(`${this.fieldSelector}.${this.SELECTORS.CLASS_HAS_ERROR}[${this.DATA_ATTRIBUTES.fieldName}="${name}"]`);

		if (item) {
			item.classList.remove(this.SELECTORS.CLASS_HAS_ERROR);

			const value = item.querySelector(this.errorSelector);

			if (value) {
				value.innerHTML = ''
			}
		}
	}

	// Dispatch custom event.
	dispatchFormEvent(element, name, detail) {
		const options = {
			bubbles: true,
		};

		if (detail) {
			options['detail'] = detail;
		}

		const event = new CustomEvent(name, options);

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
		if (typeof fields === 'undefined') {
			return;
		}

		// Set error classes and error text on fields which have validation errors.
		for (const [key] of Object.entries(fields)) {
			const item = element.querySelector(`${this.errorSelector}[data-id="${key}"]`);

			item?.closest(this.fieldSelector).classList.add(this.SELECTORS.CLASS_HAS_ERROR);

			if (item !== null) {
				item.innerHTML = fields[key];
			}
		}

		// Scroll to element if the condition is right.
		if (Object.entries(fields).length > 0 && !this.SETTINGS.FORM_DISABLE_SCROLL_TO_FIELD_ON_ERROR) {
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
		const messageContainer = element.querySelector(this.globalMsgSelector);

		if (!messageContainer) {
			return;
		}

		const headingSuccess = messageContainer?.getAttribute(this.DATA_ATTRIBUTES.globalMsgHeadingSuccess);
		const headingError = messageContainer?.getAttribute(this.DATA_ATTRIBUTES.globalMsgHeadingError);

		messageContainer.classList.add(this.SELECTORS.CLASS_ACTIVE);
		messageContainer.dataset.status = status;

		// Scroll to msg if the condition is right.
		if (status === 'success') {
			if (!this.SETTINGS.FORM_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS) {
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
		const messageContainer = element.querySelector(this.globalMsgSelector);

		if (!messageContainer) {
			return;
		}

		messageContainer.classList.remove(this.SELECTORS.CLASS_ACTIVE);
	}

	// Build GTM data for the data layer.
	getGtmData(element, eventName, formData) {
		const output = {};
		const data = {};
		for (const [key, value] of formData) { // eslint-disable-line no-unused-vars
			// Skip for files.
			if (typeof value !== 'string') {
				continue;
			}

			const itemValue = JSON.parse(value);
			const item = element.querySelector(`${this.fieldSelector} [name="${itemValue.name}"]`);
			const trackingValue = item?.getAttribute(this.DATA_ATTRIBUTES.tracking);
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
	gtmSubmit(element, formData, status, errors) {
		const eventName = element.getAttribute(this.DATA_ATTRIBUTES.trackingEventName);

		if (eventName) {
			const gtmData = this.getGtmData(element, eventName, formData);

			const additionalData = JSON.parse(element.getAttribute(this.DATA_ATTRIBUTES.trackingAdditionalData));
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
				this.dispatchFormEvent(element, this.EVENTS.BEFORE_GTM_DATA_PUSH);
				window.dataLayer.push({...gtmData, ...additionalDataItems});
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
			case 'select': {
				if (input.esFormsFieldType === 'phone') {
					break;
				}

				const customSelect = input.config.choices;

				if (customSelect.some((item) => item.selected === true && item.value !== '')) {
					input.passedElement.element.closest(this.fieldSelector).classList.add(this.SELECTORS.CLASS_FILLED);
				}
				break;
			}
			case 'tel': {
				if (input.value && input.value.length) {
					input.closest(this.fieldSelector).classList.add(this.SELECTORS.CLASS_FILLED);
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
			const formId = element.getAttribute(this.DATA_ATTRIBUTES.formPostId);

			// Unset the choices in the submitted form.
			if (this.getFormStateByKey('selects', formId)) {
				this.getFormStateByKey('selects', formId).forEach((item) => {
					item.setChoiceByValue(item?.passedElement?.element.getAttribute(this.DATA_ATTRIBUTES.selectInitial));
					item.clearInput();
					item.unhighlightAll();
				});
			}

			// Unset the files in the submitted form.
			if (this.getFormStateByKey('files', formId)) {
				this.getFormStateByKey('files', formId).forEach((item, index) => {
					item.removeAllFiles();
				});
			}

			const fields = element.querySelectorAll(this.fieldSelector);

			[...fields].forEach((item) => {
				item.classList.remove(this.SELECTORS.CLASS_FILLED);
				item.classList.remove(this.SELECTORS.CLASS_ACTIVE);
				item.classList.remove(this.SELECTORS.CLASS_HAS_ERROR);
			});

			const inputs = element.querySelectorAll(`${this.inputSelector}, ${this.textareaSelector}`);
			[...inputs].forEach((item) => {
				item.value = '';
				item.checked = false;
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
		const downloads = element.getAttribute(this.DATA_ATTRIBUTES.downloads) ?? '';
		const variation = element.getAttribute(this.DATA_ATTRIBUTES.successRedirectVariation) ?? '';

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
		this.dispatchFormEvent(element, this.EVENTS.AFTER_FORM_SUBMIT_SUCCESS_BEFORE_REDIRECT, redirectUrl);

		if (!this.SETTINGS.FORM_DISABLE_NATIVE_REDIRECT_ON_SUCCESS) {
			// Do the actual redirect after some time.
			setTimeout(() => {
				window.location = redirectUrl;

				if (reload) {
					window.location.reload();
				}
			}, parseInt(this.SETTINGS.REDIRECTION_TIMEOUT, 10));
		}
	}

	// Check if captcha is used.
	isCaptchaUsed() {
		return Boolean(this.SETTINGS.CAPTCHA?.['siteKey']);
	}

	// Check if captcha init is used.
	isCaptchaInitUsed() {
		return Boolean(this.SETTINGS.CAPTCHA?.['loadOnInit']);
	}

	// Check if captcha badge is hidden.
	isCaptchaHideBadgeUsed() {
		return Boolean(this.SETTINGS.CAPTCHA?.['hideBadge']);
	}

	// Check if captcha enterprise is used.
	isCaptchaEnterprise() {
		return Boolean(this.SETTINGS.CAPTCHA?.['isEnterprise']);
	}

	// Check if form is fully loaded.
	isFormLoaded(formId, element, selectsLength, textareaLenght, filesLength) {
		const interval = setInterval(() => {
			const selects = this.getFormStateByKey('selects', formId);
			const textareas = this.getFormStateByKey('textareas', formId);
			const files = this.getFormStateByKey('files', formId);

			if (
				selects.length >= selectsLength &&
				textareas.length >= textareaLenght &&
				files.length >= filesLength
			) {
				clearInterval(interval);

				this.setFormStateByKey('isLoaded', true, formId);

				// Triger event that form is fully loaded.
				this.dispatchFormEvent(element, this.EVENTS.FORM_JS_LOADED);
			}
		}, 100);
	}

	// Check if form is loaded in admin.
	isFormAdmin() {
		return this.formIsAdmin;
	}

	// Append common form data items.
	getCommonFormDataItems(params) {
		return [
			{
				key: this.FORM_PARAMS.postId,
				value: JSON.stringify({
					name: this.FORM_PARAMS.postId,
					value: params?.formId,
					type: 'hidden',
				}),
			},
			{
				key: this.FORM_PARAMS.type,
				value: JSON.stringify({
					name: this.FORM_PARAMS.type,
					value: params?.formType,
					type: 'hidden',
				}),
			}
		];
	}

	// Return field element by name.
	getFieldByName(element, name) {
		return element.querySelector(`${this.fieldSelector}[${this.DATA_ATTRIBUTES.fieldName}="${name}"]`);
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

				condition = toCheck?.value && toCheck?.value.length;
				break;
			case 'choices':
				toCheck = element.querySelector('option');

				condition = toCheck?.value && toCheck?.value.length;
				break;
			default:
				condition = element?.value && element?.value.length;
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
		if (typeof window?.[this.getPrefix()] === 'undefined') {
			window[this.getPrefix()] = {};
		}

		if (typeof window?.[this.getPrefix()]?.utils === 'undefined') {
			window[this.getPrefix()].utils = {
				prefix: this.prefix,
				formIsAdmin: this.formIsAdmin,
				formSubmitRestApiUrl: this.formSubmitRestApiUrl,

				formSelectorPrefix: this.formSelectorPrefix,

				formSelector: this.formSelector,
				submitSingleSelector: this.submitSingleSelector,
				stepSelector: this.stepSelector,
				errorSelector: this.errorSelector,
				loaderSelector: this.loaderSelector,
				globalMsgSelector: this.globalMsgSelector,
				groupSelector: this.groupSelector,
				fieldSelector: this.fieldSelector,
				dateFieldSelector: this.dateFieldSelector,
				countryFieldSelector: this.countryFieldSelector,
				inputSelector: this.inputSelector,
				textareaSelector: this.textareaSelector,
				selectSelector: this.selectSelector,
				fileSelector: this.fileSelector,

				selectClassName: this.selectClassName,

				FORM_PARAMS: this.FORM_PARAMS,
				DATA_ATTRIBUTES: this.DATA_ATTRIBUTES,
				SETTINGS: this.SETTINGS,
				EVENTS: this.EVENTS,
				SELECTORS: this.SELECTORS,
				DELIMITER: this.DELIMITER,
				CONDITIONAL_TAGS_OPERATORS: this.CONDITIONAL_TAGS_OPERATORS,
				CONDITIONAL_TAGS_ACTIONS: this.CONDITIONAL_TAGS_ACTIONS,
				CONDITIONAL_TAGS_LOGIC: this.CONDITIONAL_TAGS_LOGIC,

				FORMS_STATE: this.FORMS_STATE,

				getPrefix: () => {
					return this.getPrefix();
				},
				setFormStateInitial: (formId = 0) => {
					return this.setFormStateInitial(formId);
				},
				getFormStateByKey: (key, formId = 0) => {
					return this.getFormStateByKey(key, formId);
				},
				setFormStateByKey: (key, value, formId = 0) => {
					return this.setFormStateByKey(key, value, formId);
				},
				deleteFormStateByKey: (key, formId = 0) => {
					return this.deleteFormStateByKey(key, formId);
				},
				getFormStateByIndex: (key, name, formId = 0) => {
					return this.getFormStateByIndex(key, name, formId);
				},
				getFormStateByName: (key, name, formId = 0) => {
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
				preFillOnInit: (input, type) => {
					this.preFillOnInit(input, type);
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
