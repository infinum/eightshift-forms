/* global esFormsLocalization */

import manifest from './../manifest.json';
import selectManifest from './../../select/manifest.json';

export const prefix = 'esForms';

export class State {
	constructor(options = {}) {
		// Detect if form is used in admin for settings or on the frontend.
		this.formIsAdmin = options.formIsAdmin ?? false;

		// State names.
		this.ISLOADED = 'isloaded';
		this.ELEMENTS = 'elements';
		this.FORM = 'form';

		this.METHOD = 'method';
		this.ACTION = 'action';
		this.ACTION_EXTERNAL = 'actionExternal';
		this.FIELD = 'field';
		this.VALUE = 'value';
		this.INITIAL = 'initial';
		this.VALUES = 'values';
		this.VALUE_COUNTRY = 'valueCountry';
		this.INPUT = 'input';
		this.INPUT_SELECT = 'inputSelect';
		this.ITEMS = 'items';
		this.CUSTOM = 'custom';
		this.TYPE = 'type';
		this.TYPE_SETTINGS = 'typeSettings';
		this.TYPE_INTERNAL = 'typeInternal';
		this.TYPE_CUSTOM = 'typeCustom';
		this.NAME = 'name';
		this.ERROR = 'error';
		this.GLOBAL_MSG = 'globalMsg';
		this.HAS_ERROR = 'hasError';
		this.LOADED = 'loaded';
		this.LOADER = 'loader';
		this.ELEMENT = 'element';
		this.HEADING_SUCCESS = 'headingSuccess';
		this.HEADING_ERROR = 'headingError';
		this.IS_SINGLE_SUBMIT = 'isSingleSubmit';
		this.SAVE_AS_JSON = 'saveAsJson';
		this.IS_ADMIN = 'isAdmin';
		this.IS_USED = 'isUsed';
		this.NONCE = 'nonce';

		// Conditional tags
		this.CONDITIONAL_TAGS = 'conditionalTags';
		this.CONDITIONAL_TAGS_INNER = 'conditionalTagsInner';
		this.TAGS = 'tags';
		this.TAGS_REF = 'reference';
		this.TAGS_DEFAULTS = 'defaults';
		this.TAGS_EVENTS = 'events';
		this.CONDITIONAL_TAGS_FORM = 'conditionalTagsForm';
		this.CONDITIONAL_TAGS_EVENTS = 'conditionalTagsEvents';
		this.CONDITIONAL_TAGS_INNER_EVENTS = 'conditionalTagsInnerEvents';

		this.CONFIG = 'config';
		this.CONFIG_SELECT_USE_PLACEHOLDER = 'usePlaceholder';
		this.CONFIG_SELECT_USE_SEARCH = 'useSearch';
		this.CONFIG_PHONE_DISABLE_PICKER = 'disablePicker';
		this.CONFIG_PHONE_USE_PHONE_SYNC = 'usePhoneSync';
		this.CONFIG_SUCCESS_REDIRECT= 'successRedirect';
		this.CONFIG_SUCCESS_REDIRECT_VARIATION= 'successRedirectVariation';
		this.CONFIG_DOWNLOADS= 'downloads';

		this.SETTINGS = 'settings';
		this.SETTINGS_DISABLE_SCROLL_TO_GLOBAL_MSG_ON_SUCCESS = 'disableScrollToGlobalMsgOnSuccess';
		this.SETTINGS_DISABLE_SCROLL_TO_FIELD_ON_ERROR = 'disableScrollToFieldOnError';
		this.SETTINGS_FORM_RESET_ON_SUCCESS= 'formResetOnSuccess';
		this.SETTINGS_FORM_DISABLE_NATIVE_REDIRECT_ON_SUCCESS = 'formDisableNativeRedirectOnSuccess';
		this.SETTINGS_REDIRECTION_TIMEOUT = 'redirectionTimeout';
		this.SETTINGS_HIDE_GLOBAL_MESSAGE_TIMEOUT = 'hideGlobalMessageTimeout';
		this.SETTINGS_FILE_REMOVE_LABEL = 'fileRemoveLabel';

		this.CAPTCHA = 'captcha';
		this.CAPTCHA_SITE_KEY = 'site_key';
		this.CAPTCHA_IS_ENTERPRISE = 'isEnterprise';
		this.CAPTCHA_SUBMIT_ACTION = 'submitAction';
		this.CAPTCHA_INIT_ACTION = 'initAction';
		this.CAPTCHA_LOAD_ON_INIT = 'loadOnInit';
		this.CAPTCHA_HIDE_BADGE = 'hideBadge';

		this.ENRICHMENT = 'enrichment';
		this.ENRICHMENT_EXPIRATION = 'expiration';
		this.ENRICHMENT_ALLOWED = 'allowed';

		this.EVENTS = 'events';
		this.EVENTS_BEFORE_FORM_SUBMIT = 'beforeFormSubmit';
		this.EVENTS_AFTER_FORM_SUBMIT = 'afterFormSubmit';
		this.EVENTS_AFTER_FORM_SUBMIT_SUCCESS_BEFORE_REDIRECT = 'afterFormSubmitSuccessBeforeRedirect';
		this.EVENTS_AFTER_FORM_SUBMIT_SUCCESS = 'afterFormSubmitSuccess';
		this.EVENTS_AFTER_FORM_SUBMIT_RESET = 'afterFormSubmitReset';
		this.EVENTS_AFTER_FORM_SUBMIT_ERROR = 'afterFormSubmitError';
		this.EVENTS_AFTER_FORM_SUBMIT_ERROR_VALIDATION = 'afterFormSubmitErrorValidation';
		this.EVENTS_AFTER_FORM_SUBMIT_END = 'afterFormSubmitEnd';
		this.EVENTS_BEFORE_GTM_DATA_PUSH = 'beforeGtmDataPush';
		this.EVENTS_FORM_JS_LOADED = 'jsFormLoaded';
		this.EVENTS_AFTER_CAPTCHA_INIT = 'afterCaptchaInit';

		this.SELECTORS = 'selectors';
		this.SELECTORS_CLASS_ACTIVE = 'isActive';
		this.SELECTORS_CLASS_FILLED = 'isFilled';
		this.SELECTORS_CLASS_LOADING = 'isLoading';
		this.SELECTORS_CLASS_HIDDEN = 'isHidden';
		this.SELECTORS_CLASS_VISIBLE = 'isVisible';
		this.SELECTORS_CLASS_HAS_ERROR = 'hasError';

		this.TRACKING = 'tracking';
		this.TRACKING_EVENT_NAME = 'event_name';
		this.TRACKING_EVENT_ADDITIONAL_DATA = 'event_additional_data';

		this.STEPS = 'steps';
		this.STEPS_FLOW = 'flow';
		this.STEPS_CURRENT = 'current';
		this.STEPS_ITEMS = 'items';
		this.STEPS_ELEMENTS = 'elements';

		// Selectors.
		this.formSelectorPrefix = `.${manifest.componentJsClass}`;
		// Class names.
		this.selectClassName = selectManifest.componentClass;

		// Specific selectors.
		this.SELECTORS_PREFIX = 'prefix';
		this.SELECTORS_FORM = 'form';
		this.SELECTORS_SUBMIT_SINGLE = 'singleSubmit';
		this.SELECTORS_STEP = `step`;
		this.SELECTORS_STEP_SUBMIT = `stepSubmit`;
		this.SELECTORS_ERROR = `error`;
		this.SELECTORS_LOADER = `loader`;
		this.SELECTORS_GLOBAL_MSG = `globalMsg`;
		this.SELECTORS_GROUP = `group`;
		this.SELECTORS_FIELD = `field`;

		this.ATTRIBUTES = 'attributes';
		this.PARAMS = 'params';
	}

	setStateWindow() {
		if (!window[prefix]) {
			window[prefix] = {}
		}
	}

	setStateInitial() {
		this.setStateWindow();

		window[prefix].state = {};
		window[prefix].state = {
			[this.CAPTCHA]: {},
			[this.ENRICHMENT]: {},
			[this.SETTINGS]: {},
			[this.EVENTS]: {},
			[this.SELECTORS]: {},
			[this.ATTRIBUTES]: {},
			[this.PARAMS]: {},
			[this.CONFIG]: {},
		}

		// Attributes.
		for (const [key, item] of Object.entries(esFormsLocalization.customFormDataAttributes ?? {})) {
			this.setState([key], item, this.ATTRIBUTES);
		}

		// Params.
		for (const [key, item] of Object.entries(esFormsLocalization.customFormParams ?? {})) {
			this.setState([key], item, this.PARAMS);
		}

		this.setState([this.IS_ADMIN], this.formIsAdmin, this.CONFIG);
		this.setState([this.NONCE], esFormsLocalization.nonce, this.CONFIG);

		// Global settings.
		this.setState([this.SETTINGS_DISABLE_SCROLL_TO_GLOBAL_MSG_ON_SUCCESS], Boolean(esFormsLocalization.formDisableScrollToGlobalMessageOnSuccess), this.SETTINGS);
		this.setState([this.SETTINGS_DISABLE_SCROLL_TO_FIELD_ON_ERROR], Boolean(esFormsLocalization.formDisableScrollToFieldOnError), this.SETTINGS);
		this.setState([this.SETTINGS_FORM_RESET_ON_SUCCESS], Boolean(esFormsLocalization.formResetOnSuccess), this.SETTINGS);
		this.setState([this.SETTINGS_FORM_DISABLE_NATIVE_REDIRECT_ON_SUCCESS], Boolean(esFormsLocalization.formDisableNativeRedirectOnSuccess), this.SETTINGS);
		this.setState([this.SETTINGS_REDIRECTION_TIMEOUT], esFormsLocalization.redirectionTimeout ?? 600, this.SETTINGS);
		this.setState([this.SETTINGS_HIDE_GLOBAL_MESSAGE_TIMEOUT], esFormsLocalization.hideGlobalMessageTimeout ?? 6000, this.SETTINGS);
		this.setState([this.SETTINGS_FILE_REMOVE_LABEL], esFormsLocalization.fileRemoveLabel ?? '', this.SETTINGS);

		// Captcha.
		const captcha = esFormsLocalization.captcha ?? {};
		this.setState([this.IS_USED], Boolean(captcha.isUsed), this.CAPTCHA);

		if (captcha.isUsed) {
			this.setState([this.CAPTCHA_SITE_KEY], captcha.siteKey, this.CAPTCHA);
			this.setState([this.CAPTCHA_IS_ENTERPRISE], Boolean(captcha.isEnterprise), this.CAPTCHA);
			this.setState([this.CAPTCHA_SUBMIT_ACTION], captcha.submitAction, this.CAPTCHA);
			this.setState([this.CAPTCHA_INIT_ACTION], captcha.initAction, this.CAPTCHA);
			this.setState([this.CAPTCHA_LOAD_ON_INIT], Boolean(captcha.loadOnInit), this.CAPTCHA);
			this.setState([this.CAPTCHA_HIDE_BADGE], Boolean(captcha.hideBadge), this.CAPTCHA);
		}

		// Enrichment.
		const enrichment = esFormsLocalization.enrichment ?? {};
		this.setState([this.IS_USED], Boolean(enrichment.isUsed), this.ENRICHMENT);

		if (enrichment.isUsed) {
			this.setState([this.ENRICHMENT_EXPIRATION], enrichment.expiration, this.ENRICHMENT);
			this.setState([this.ENRICHMENT_ALLOWED], enrichment.allowed, this.ENRICHMENT);
			this.setState([this.NAME], 'es-storage', this.ENRICHMENT);
		}

		// Events.
		this.setState([this.EVENTS_BEFORE_FORM_SUBMIT], this.getEventName(this.EVENTS_BEFORE_FORM_SUBMIT), this.EVENTS);
		this.setState([this.EVENTS_AFTER_FORM_SUBMIT], this.getEventName(this.EVENTS_AFTER_FORM_SUBMIT), this.EVENTS);
		this.setState([this.EVENTS_AFTER_FORM_SUBMIT_SUCCESS_BEFORE_REDIRECT], this.getEventName(this.EVENTS_AFTER_FORM_SUBMIT_SUCCESS_BEFORE_REDIRECT), this.EVENTS);
		this.setState([this.EVENTS_AFTER_FORM_SUBMIT_SUCCESS], this.getEventName(this.EVENTS_AFTER_FORM_SUBMIT_SUCCESS), this.EVENTS);
		this.setState([this.EVENTS_AFTER_FORM_SUBMIT_RESET], this.getEventName(this.EVENTS_AFTER_FORM_SUBMIT_RESET), this.EVENTS);
		this.setState([this.EVENTS_AFTER_FORM_SUBMIT_ERROR], this.getEventName(this.EVENTS_AFTER_FORM_SUBMIT_ERROR), this.EVENTS);
		this.setState([this.EVENTS_AFTER_FORM_SUBMIT_ERROR_VALIDATION], this.getEventName(this.EVENTS_AFTER_FORM_SUBMIT_ERROR_VALIDATION), this.EVENTS);
		this.setState([this.EVENTS_AFTER_FORM_SUBMIT_END], this.getEventName(this.EVENTS_AFTER_FORM_SUBMIT_END), this.EVENTS);
		this.setState([this.EVENTS_BEFORE_GTM_DATA_PUSH], this.getEventName(this.EVENTS_BEFORE_GTM_DATA_PUSH), this.EVENTS);
		this.setState([this.EVENTS_FORM_JS_LOADED], this.getEventName(this.EVENTS_FORM_JS_LOADED), this.EVENTS);
		this.setState([this.EVENTS_AFTER_CAPTCHA_INIT], this.getEventName(this.EVENTS_AFTER_CAPTCHA_INIT), this.EVENTS);

		// Selectors.
		this.setState([this.SELECTORS_CLASS_ACTIVE], 'is-active', this.SELECTORS);
		this.setState([this.SELECTORS_CLASS_FILLED], 'is-filled', this.SELECTORS);
		this.setState([this.SELECTORS_CLASS_LOADING], 'is-loading', this.SELECTORS);
		this.setState([this.SELECTORS_CLASS_HIDDEN], 'is-hidden', this.SELECTORS);
		this.setState([this.SELECTORS_CLASS_VISIBLE], 'is-visible', this.SELECTORS);
		this.setState([this.SELECTORS_CLASS_HAS_ERROR], 'has-error', this.SELECTORS);

		this.setState([this.SELECTORS_PREFIX], this.formSelectorPrefix, this.SELECTORS);
		this.setState([this.SELECTORS_FORM], this.formSelectorPrefix, this.SELECTORS);
		this.setState([this.SELECTORS_SUBMIT_SINGLE], `${this.formSelectorPrefix}-single-submit`, this.SELECTORS);
		this.setState([this.SELECTORS_STEP], `${this.formSelectorPrefix}-step`, this.SELECTORS);
		this.setState([this.SELECTORS_STEP_SUBMIT], `${this.formSelectorPrefix}-step-trigger`, this.SELECTORS);
		this.setState([this.SELECTORS_ERROR], `${this.formSelectorPrefix}-error`, this.SELECTORS);
		this.setState([this.SELECTORS_LOADER], `${this.formSelectorPrefix}-loader`, this.SELECTORS);
		this.setState([this.SELECTORS_GLOBAL_MSG], `${this.formSelectorPrefix}-global-msg`, this.SELECTORS);
		this.setState([this.SELECTORS_GROUP], `${this.formSelectorPrefix}-group`, this.SELECTORS);
		this.setState([this.SELECTORS_FIELD], `${this.formSelectorPrefix}-field`, this.SELECTORS);
	}

	// Set state initial.
	setFormStateInitial(formId) {
		this.setStateWindow();
		window[prefix].state[`form_${formId}`] = {}
		window[prefix].state[`form_${formId}`] = {
			[this.ELEMENTS]: {},
			[this.FORM]: {},
		}

		let formElement = '';

		if (formId === 0) {
			formElement = document.querySelector(this.getStateSelectorsForm());
		} else {
			formElement = document.querySelector(`${this.getStateSelectorsForm()}[${this.getStateAttribute('formPostId')}="${formId}"]`);
		}

		this.setState([this.FORM, this.ISLOADED], false, formId);
		this.setState([this.FORM, this.IS_SINGLE_SUBMIT], false, formId);
		this.setState([this.FORM, this.ELEMENT], formElement, formId);
		this.setState([this.FORM, this.TYPE], formElement.getAttribute(this.getStateAttribute('formType')), formId);
		this.setState([this.FORM, this.METHOD], formElement.getAttribute('method'), formId);
		this.setState([this.FORM, this.ACTION], formElement.getAttribute('action'), formId);
		this.setState([this.FORM, this.ACTION_EXTERNAL], formElement.getAttribute(this.getStateAttribute('actionExternal')), formId);
		this.setState([this.FORM, this.TYPE_SETTINGS], formElement.getAttribute(this.getStateAttribute('settingsType')), formId);
		this.setState([this.FORM, this.LOADER], formElement.querySelector(this.getStateSelectorsLoader()), formId);
		this.setState([this.FORM, this.TRACKING, this.TRACKING_EVENT_NAME], formElement.getAttribute(this.getStateAttribute('trackingEventName')), formId);
		this.setState([this.FORM, this.TRACKING, this.TRACKING_EVENT_ADDITIONAL_DATA], JSON.parse(formElement.getAttribute(this.getStateAttribute('trackingAdditionalData'))), formId);

		// Form settings
		this.setState([this.FORM, this.CONFIG, this.CONFIG_PHONE_DISABLE_PICKER], Boolean(formElement.getAttribute(this.getStateAttribute('phoneDisablePicker'))), formId);
		this.setState([this.FORM, this.CONFIG, this.CONFIG_PHONE_USE_PHONE_SYNC], Boolean(formElement.getAttribute(this.getStateAttribute('phoneSync'))), formId);
		this.setState([this.FORM, this.CONFIG, this.CONFIG_SUCCESS_REDIRECT], formElement.getAttribute(this.getStateAttribute('successRedirect')), formId);
		this.setState([this.FORM, this.CONFIG, this.CONFIG_SUCCESS_REDIRECT_VARIATION], formElement.getAttribute(this.getStateAttribute('successRedirectVariation')), formId);
		this.setState([this.FORM, this.CONFIG, this.CONFIG_DOWNLOADS], formElement.getAttribute(this.getStateAttribute('downloads')), formId);

		const globalMsg = formElement.querySelector(this.getStateSelectorsGlobalMsg());
		this.setState([this.FORM, this.GLOBAL_MSG, this.ELEMENT], globalMsg, formId);
		this.setState([this.FORM, this.GLOBAL_MSG, this.HEADING_SUCCESS], globalMsg.getAttribute(this.getStateAttribute('globalMsgHeadingSuccess')), formId);
		this.setState([this.FORM, this.GLOBAL_MSG, this.HEADING_ERROR], globalMsg.getAttribute(this.getStateAttribute('globalMsgHeadingError')), formId);

		// Conditional tags
		this.setState([this.FORM, this.CONDITIONAL_TAGS_EVENTS], {}, formId);
		this.setState([this.FORM, this.CONDITIONAL_TAGS_INNER_EVENTS], {}, formId);
		this.setState([this.FORM, this.CONDITIONAL_TAGS_FORM], JSON.parse(formElement.getAttribute(this.getStateAttribute('conditionalTags'))), formId);

		// Steps.
		const steps = formElement.querySelectorAll(this.getStateSelectorsStep(formId));
		this.setState([this.FORM, this.STEPS, this.IS_USED], false, formId);

		if (steps.length) {
			this.setState([this.FORM, this.STEPS, this.IS_USED], true, formId);
			this.setState([this.FORM, this.STEPS, this.STEPS_FLOW], [], formId);
			this.setState([this.FORM, this.STEPS, this.STEPS_CURRENT], '', formId);
			this.setState([this.FORM, this.STEPS, this.STEPS_ITEMS], {}, formId);
			this.setState([this.FORM, this.STEPS, this.STEPS_ELEMENTS], {}, formId);

			Object.values(steps).forEach((item, index) => {
				const stepFields = item.querySelectorAll(this.getStateSelectorsField());
				const stepId = item.getAttribute(this.getStateAttribute('stepId'));
				const stepOutput = [];

				stepFields.forEach((stepField) => {
					const stepFieldName = stepField.getAttribute(this.getStateAttribute('fieldName'));

					if (stepFieldName) {
						stepOutput.push(stepFieldName);
					}
				})

				this.setState([this.FORM, this.STEPS, this.STEPS_ELEMENTS, stepId], item, formId);
				this.setState([this.FORM, this.STEPS, this.STEPS_ITEMS, stepId], stepOutput, formId);

				if (index === 0) {
					this.setState([this.FORM, this.STEPS, this.STEPS_CURRENT], stepId, formId);
				}
			});
		}

		// Loop all fields.
		for (const [key, item] of Object.entries(formElement.querySelectorAll('input, select, textarea'))) {
			const {
				value,
				name,
				type,
			} = item;

			if (name === 'search_terms') {
				continue;
			}

			const field = formElement.querySelector(`${this.getStateSelectorsField()}[${this.getStateAttribute('fieldName')}="${name}"]`);

			// Make changes depending on the field type.
			switch (type) {
				case 'radio':
				case 'checkbox':
					this.setState([this.ELEMENTS, name, this.TYPE], type, formId);
					this.setState([this.ELEMENTS, name, this.TYPE_INTERNAL], type, formId);
					this.setState([this.ELEMENTS, name, this.INPUT], '', formId);
					this.setState([this.ELEMENTS, name, this.ITEMS, value, this.VALUE], value, formId);
					this.setState([this.ELEMENTS, name, this.ITEMS, value, this.FIELD], item.parentNode.parentNode, formId);
					this.setState([this.ELEMENTS, name, this.ITEMS, value, this.INPUT], item, formId);
					this.setState([this.ELEMENTS, name, this.ITEMS, value, this.NAME], name, formId);

					if (type === 'radio') {
						if (!this.getStateElementInitial(name, formId)) {
							this.setState([this.ELEMENTS, name, this.INITIAL], item.checked ? value : '', formId);
						}

						if (!this.getStateElementValue(name, formId)) {
							this.setState([this.ELEMENTS, name, this.VALUE], item.checked ? value : '', formId);
						}

						this.setState([this.ELEMENTS, name, this.TRACKING], field.getAttribute(this.getStateAttribute('tracking')), formId);
					}

					if (type === 'checkbox') {
						this.setState([this.ELEMENTS, name, this.VALUE, value], item.checked ? value : '', formId);
						this.setState([this.ELEMENTS, name, this.INITIAL, value], item.checked ? value : '', formId);
						this.setState([this.ELEMENTS, name, this.TRACKING, value], item.parentNode.parentNode.getAttribute(this.getStateAttribute('tracking')), formId);
					}

					this.setStateConditionalTagsItems(item.parentNode.parentNode.getAttribute(this.getStateAttribute('conditionalTags')), name, value, formId);

					break;
				case 'select-one':
					// Combined fields like phone can have field null.
					const customField = this.getFormFieldElementByChild(item);
					const typeTemp = customField.getAttribute(this.getStateAttribute('fieldType'));

					if (item.options.length) {
						const customData = JSON.parse(item.options[item.options.selectedIndex].getAttribute(this.getStateAttribute('selectCustomProperties')));

						switch (typeTemp) {
							case 'phone':
							case 'country':
								this.setState([this.ELEMENTS, name, this.VALUE_COUNTRY, 'code'], customData[this.getStateAttribute('selectCountryCode')], formId);
								this.setState([this.ELEMENTS, name, this.VALUE_COUNTRY, 'label'], customData[this.getStateAttribute('selectCountryLabel')], formId);
								this.setState([this.ELEMENTS, name, this.VALUE_COUNTRY, 'number'], customData[this.getStateAttribute('selectCountryNumber')], formId);
								break;
							}
					}

					if (typeTemp !== 'phone') {
						this.setState([this.ELEMENTS, name, this.VALUE], value, formId);
						this.setState([this.ELEMENTS, name, this.INITIAL], value, formId);
					}

					this.setState([this.ELEMENTS, name, this.TYPE_INTERNAL], typeTemp, formId);
					this.setState([this.ELEMENTS, name, this.TYPE], 'select', formId);
					this.setState([this.ELEMENTS, name, this.INPUT], item, formId);
					this.setState([this.ELEMENTS, name, this.CONFIG, this.CONFIG_SELECT_USE_PLACEHOLDER], Boolean(item.getAttribute(this.getStateAttribute('selectPlaceholder'))), formId);
					this.setState([this.ELEMENTS, name, this.CONFIG, this.CONFIG_SELECT_USE_SEARCH], Boolean(item.getAttribute(this.getStateAttribute('selectAllowSearch'))), formId);
					this.setState([this.ELEMENTS, name, this.TRACKING], field.getAttribute(this.getStateAttribute('tracking')), formId);
					break;
				case 'tel':
					this.setState([this.ELEMENTS, name, this.INITIAL], value, formId);
					this.setState([this.ELEMENTS, name, this.VALUE], value, formId);
					this.setState([this.ELEMENTS, name, this.TYPE], type, formId);
					this.setState([this.ELEMENTS, name, this.TYPE_INTERNAL], 'tel', formId);
					this.setState([this.ELEMENTS, name, this.INPUT], item, formId);
					this.setState([this.ELEMENTS, name, this.INPUT_SELECT], field.querySelector('select'), formId);
					this.setState([this.ELEMENTS, name, this.TRACKING], field.getAttribute(this.getStateAttribute('tracking')), formId);
					break;
				case 'date':
				case 'datetime-local':
					this.setState([this.ELEMENTS, name, this.INITIAL], value, formId);
					this.setState([this.ELEMENTS, name, this.VALUE], value, formId);
					this.setState([this.ELEMENTS, name, this.TYPE], type, formId);
					this.setState([this.ELEMENTS, name, this.TYPE_INTERNAL], type, formId);
					this.setState([this.ELEMENTS, name, this.INPUT], item, formId);
					this.setState([this.ELEMENTS, name, this.TRACKING], field.getAttribute(this.getStateAttribute('tracking')), formId);

					if (type === 'datetime-local') {
						this.setState([this.ELEMENTS, name, this.TYPE], 'date', formId);
						this.setState([this.ELEMENTS, name, this.TYPE_INTERNAL], 'datetime', formId);
					}
					break;
				default:
					this.setState([this.ELEMENTS, name, this.INITIAL], value, formId);
					this.setState([this.ELEMENTS, name, this.VALUE], value, formId);
					this.setState([this.ELEMENTS, name, this.TYPE], type, formId);
					this.setState([this.ELEMENTS, name, this.TYPE_INTERNAL], type, formId);
					this.setState([this.ELEMENTS, name, this.INPUT], item, formId);
					this.setState([this.ELEMENTS, name, this.TRACKING], field.getAttribute(this.getStateAttribute('tracking')), formId);
					break;
			}

			this.setState([this.ELEMENTS, name, this.HAS_ERROR], false, formId);
			this.setState([this.ELEMENTS, name, this.LOADED], false, formId);
			this.setState([this.ELEMENTS, name, this.NAME], name, formId);
			this.setState([this.ELEMENTS, name, this.FIELD], field, formId);
			this.setState([this.ELEMENTS, name, this.ERROR], field?.querySelector(this.getStateSelectorsError()), formId);
			this.setState([this.ELEMENTS, name, this.IS_SINGLE_SUBMIT], item.classList.contains(this.getStateSelectorsSubmitSingle().substring(1)), formId);
			this.setState([this.ELEMENTS, name, this.TYPE_CUSTOM], field?.getAttribute(this.getStateAttribute('fieldTypeCustom')), formId);
			this.setState([this.ELEMENTS, name, this.SAVE_AS_JSON], Boolean(item.getAttribute(this.getStateAttribute('saveAsJson'))), formId);

			// Conditional tags.
			if (field) {
				this.setStateConditionalTags(field, name, formId);
			}
		}
	}

	/**
	 * Init fields initial values data.
	 *
	 * @param {object} element Form element.
	 * @param {bool} isInit If this method is used in initial state or on every change.
	 *
	 * @returns void
	 */
	setValues(item, formId) {
		const {
			name,
			value,
			checked,
			type,
		} = item;

		// Datepicker and dropzone are set using native lib events.

		switch (type) {
			case 'radio':
				this.setState([this.ELEMENTS, name, this.VALUE], checked ? value : '', formId);
				break;
			case 'checkbox':
				this.setState([this.ELEMENTS, name, this.VALUE, value], checked ? value : '', formId);
				break;
			case 'select-one':
				const customField = this.getFormFieldElementByChild(item);
				const typeCustom = customField.getAttribute(this.getStateAttribute('fieldType'));
				const customData = JSON.parse(item.options[item.options.selectedIndex].getAttribute(this.getStateAttribute('selectCustomProperties')));

				switch (typeCustom) {
					case 'phone':
					case 'country':
						this.setState([this.ELEMENTS, name, this.VALUE_COUNTRY, 'code'], customData[this.getStateAttribute('selectCountryCode')], formId);
						this.setState([this.ELEMENTS, name, this.VALUE_COUNTRY, 'label'], customData[this.getStateAttribute('selectCountryLabel')], formId);
						this.setState([this.ELEMENTS, name, this.VALUE_COUNTRY, 'number'], customData[this.getStateAttribute('selectCountryNumber')], formId);
						break;
				}

				if (typeCustom !== 'phone') {
					this.setState([this.ELEMENTS, name, this.VALUE], value, formId);
				}
				break;
			default:
				this.setState([this.ELEMENTS, name, this.VALUE], value, formId);
				break;
		}
	}

	setState(keyArray, value, formId) {
		const formKey = isNaN(formId) ? formId : `form_${formId}`;
		let stateObject = window[prefix].state[formKey];

		keyArray.forEach((key, index) => {
			if (index === keyArray.length - 1) {
				stateObject[key] = value;
			} else {
				stateObject[key] = stateObject[key] || {};
				stateObject = stateObject[key];
			}
		});

		if (keyArray.length > 1) {
			window[prefix].state[formKey] = {
				...window[prefix].state[formKey],
				...stateObject[keyArray[0]]
			};
		} else {
			window[prefix].state[formKey] = {
				...window[prefix].state[formKey],
			};
		}
	}

	setStateConditionalTags(field, name, formId) {
		const conditionalTags = field.getAttribute(this.getStateAttribute('conditionalTags'));

		this.setState([this.ELEMENTS, name, this.CONDITIONAL_TAGS, this.TAGS_DEFAULTS], '', formId);
		this.setState([this.ELEMENTS, name, this.CONDITIONAL_TAGS, this.TAGS], [], formId);
		this.setState([this.ELEMENTS, name, this.CONDITIONAL_TAGS, this.TAGS_REF], [], formId);

		if (conditionalTags) {
			const tag = JSON.parse(conditionalTags)?.[0];

			this.setState([this.ELEMENTS, name, this.CONDITIONAL_TAGS, this.TAGS_DEFAULTS], tag[0], formId);
			this.setState([this.ELEMENTS, name, this.CONDITIONAL_TAGS, this.TAGS], tag[1], formId);

			this.setStateConditionalTagsInner(name, formId, tag[1]);
		}
	}

	setStateConditionalTagsItems(conditionalTags, name, innerName, formId) {
		if (!innerName) {
			return;
		}

		this.setState([this.ELEMENTS, name, this.CONDITIONAL_TAGS_INNER, innerName, this.TAGS_DEFAULTS], '', formId);
		this.setState([this.ELEMENTS, name, this.CONDITIONAL_TAGS_INNER, innerName, this.TAGS], [], formId);
		this.setState([this.ELEMENTS, name, this.CONDITIONAL_TAGS_INNER, innerName, this.TAGS_REF], [], formId);

		if (!conditionalTags) {
			return;
		}

		const tag = JSON.parse(conditionalTags)?.[0];

		this.setState([this.ELEMENTS, name, this.CONDITIONAL_TAGS_INNER, innerName, this.TAGS_DEFAULTS], tag[0], formId);
		this.setState([this.ELEMENTS, name, this.CONDITIONAL_TAGS_INNER, innerName, this.TAGS], tag[1], formId);

		this.setStateConditionalTagsInner(name, formId, tag[1], innerName);
	}

	setStateConditionalTagsInner(name, formId, tags, innerName = '') {
		const refOutput = [];

		const isInner = Boolean(innerName);

		const events = isInner ? this.getStateFormConditionalTagsInnerEvents(formId) : this.getStateFormConditionalTagsEvents(formId)

		const eventsOutput = {
			...events ?? {},
		};

		tags.forEach((item) => {
			refOutput.push(Array(item.length).fill(false));

			// Loop inner fields.
			item.forEach((inner) => {
				eventsOutput[inner[0]] = [
					...eventsOutput[inner[0]] ?? [],
					(isInner) ? `${name}---${innerName}` : name,
				];
			});
		});

		if (isInner) {
			this.setState([this.FORM, this.CONDITIONAL_TAGS_INNER_EVENTS], eventsOutput, formId);
			this.setState([this.ELEMENTS, name, this.CONDITIONAL_TAGS_INNER, innerName, this.TAGS_REF], refOutput, formId);
		} else {
			this.setState([this.FORM, this.CONDITIONAL_TAGS_EVENTS], eventsOutput, formId);
			this.setState([this.ELEMENTS, name, this.CONDITIONAL_TAGS, this.TAGS_REF], refOutput, formId);
		}
	}

	// Delete state item by key
	deleteState(key, formId) {
		// this.setFormStateByKey(key, [], formId);
	}

	// Get state by keys.
	getState(keys, formId) {
		const formKey = isNaN(formId) ? formId : `form_${formId}`;
		let stateObject = window?.[prefix]?.state?.[formKey];

		if (!stateObject) {
			return undefined;
		}
	
		keys.forEach((key) => {
			stateObject = stateObject?.[key];
			if (!stateObject) {
				return undefined;
			}
		});
	
		return stateObject;
	}

	getStateTop(name) {
		return window?.[prefix]?.state?.[name];
	}

	getEventName(name) {
		const output = name.charAt(0).toUpperCase() + name.slice(1);

		return `${prefix}${output}`;
	}

	// ----------------------------------------
	// Config
	getStateConfigIsAdmin() {
		return this.getState([this.IS_ADMIN], this.CONFIG);
	}
	getStateConfigNonce() {
		return this.getState([this.NONCE], this.CONFIG);
	}

	// ----------------------------------------
	// Form
	getStateForm(formId) {
		return this.getState([this.FORM], formId);
	}

	getStateFormIsSingleSubmit(formId) {
		return this.getState([this.FORM, this.IS_SINGLE_SUBMIT], formId);
	}

	getStateFormType(formId) {
		return this.getState([this.FORM, this.TYPE], formId);
	}

	getStateFormMethod(formId) {
		return this.getState([this.FORM, this.METHOD], formId);
	}

	getStateFormAction(formId) {
		return this.getState([this.FORM, this.ACTION], formId);
	}

	getStateFormActionExternal(formId) {
		return this.getState([this.FORM, this.ACTION_EXTERNAL], formId);
	}

	getStateFormTypeSettings(formId) {
		return this.getState([this.FORM, this.TYPE_SETTINGS], formId);
	}

	getStateFormLoader(formId) {
		return this.getState([this.FORM, this.LOADER], formId);
	}

	getStateFormTrackingEventName(formId) {
		return this.getState([this.FORM, this.TRACKING, this.TRACKING_EVENT_NAME], formId);
	}

	getStateFormTrackingEventAdditionalData(formId) {
		return this.getState([this.FORM, this.TRACKING, this.TRACKING_EVENT_ADDITIONAL_DATA], formId);
	}

	getStateFormConditionalTagsEvents(formId) {
		return this.getState([this.FORM, this.CONDITIONAL_TAGS_EVENTS], formId);
	}

	getStateFormConditionalTagsInnerEvents(formId) {
		return this.getState([this.FORM, this.CONDITIONAL_TAGS_INNER_EVENTS], formId);
	}

	getStateFormConditionalTagsForm(formId) {
		return this.getState([this.FORM, this.CONDITIONAL_TAGS_FORM], formId);
	}

	getStateFormGlobalMsgElement(formId) {
		return this.getState([this.FORM, this.GLOBAL_MSG, this.ELEMENT], formId);
	}

	getStateFormGlobalMsgHeadingSuccess(formId) {
		return this.getState([this.FORM, this.GLOBAL_MSG, this.HEADING_SUCCESS], formId);
	}

	getStateFormGlobalMsgHeadingError(formId) {
		return this.getState([this.FORM, this.GLOBAL_MSG, this.HEADING_ERROR], formId);
	}

	getStateFormConfigPhoneDisablePicker(formId) {
		return this.getState([this.FORM, this.CONFIG, this.CONFIG_PHONE_DISABLE_PICKER], formId);
	}

	getStateFormConfigPhoneUseSync(formId) {
		return this.getState([this.FORM, this.CONFIG, this.CONFIG_PHONE_USE_PHONE_SYNC], formId);
	}

	getStateFormConfigSuccessRedirect(formId) {
		return this.getState([this.FORM, this.CONFIG, this.CONFIG_SUCCESS_REDIRECT], formId);
	}

	getStateFormConfigSuccessRedirectVariation(formId) {
		return this.getState([this.FORM, this.CONFIG, this.CONFIG_SUCCESS_REDIRECT_VARIATION], formId);
	}

	getStateFormConfigDownloads(formId) {
		return this.getState([this.FORM, this.CONFIG, this.CONFIG_DOWNLOADS], formId);
	}


	getStateFormStepsFlow(formId) {
		return this.getState([this.FORM, this.STEPS, this.STEPS_FLOW], formId);
	}
	getStateFormStepsCurrent(formId) {
		return this.getState([this.FORM, this.STEPS, this.STEPS_CURRENT], formId);
	}
	getStateFormStepsFirstStep(formId) {
		return Object.keys(this.getState([this.FORM, this.STEPS, this.STEPS_ITEMS], formId))[0];
	}
	getStateFormStepsLastStep(formId) {
		return Object.keys(this.getState([this.FORM, this.STEPS, this.STEPS_ITEMS], formId)).pop();
	}
	getStateFormStepsItem(stepId, formId) {
		return this.getState([this.FORM, this.STEPS, this.STEPS_ITEMS, stepId], formId);
	}
	getStateFormStepsItems(formId) {
		return this.getState([this.FORM, this.STEPS, this.STEPS_ITEMS], formId);
	}
	getStateFormStepsElement(stepId, formId) {
		return this.getState([this.FORM, this.STEPS, this.STEPS_ELEMENTS, stepId], formId);
	}
	getStateFormStepsIsUsed(formId) {
		return this.getState([this.FORM, this.STEPS, this.IS_USED], formId);
	}

	// ----------------------------------------
	// Settings
	getStateSettingsDisableScrollToGlobalMsgOnSuccess() {
		return this.getState([this.SETTINGS_DISABLE_SCROLL_TO_GLOBAL_MSG_ON_SUCCESS], this.SETTINGS);
	}

	getStateSettingsDisableScrollToFieldOnError() {
		return this.getState([this.SETTINGS_DISABLE_SCROLL_TO_FIELD_ON_ERROR], this.SETTINGS);
	}

	getStateSettingsResetOnSuccess() {
		return this.getState([this.SETTINGS_FORM_RESET_ON_SUCCESS], this.SETTINGS);
	}

	getStateSettingsDisableNativeRedirectOnSuccess() {
		return this.getState([this.SETTINGS_FORM_DISABLE_NATIVE_REDIRECT_ON_SUCCESS], this.SETTINGS);
	}

	getStateSettingsRedirectionTimeout() {
		return this.getState([this.SETTINGS_REDIRECTION_TIMEOUT], this.SETTINGS);
	}

	getStateSettingsHideGlobalMessageTimeout() {
		return this.getState([this.SETTINGS_HIDE_GLOBAL_MESSAGE_TIMEOUT], this.SETTINGS);
	}

	getStateSettingsFileRemoveLabel() {
		return this.getState([this.SETTINGS_FILE_REMOVE_LABEL], this.SETTINGS);
	}

	// ----------------------------------------
	// Element

	getStateFormElement(formId) {
		return this.getState([this.FORM, this.ELEMENT], formId);
	}

	getStateElements(formId) {
		return Object.entries(this.getState([this.ELEMENTS], formId));
	}

	getStateElementConditionalTagsDefaults(name, formId) {
		return this.getState([this.ELEMENTS, name, this.CONDITIONAL_TAGS, this.TAGS_DEFAULTS], formId);
	}

	getStateElementConditionalTagsDefaultsInner(name, innerName, formId) {
		return this.getState([this.ELEMENTS, name, this.CONDITIONAL_TAGS_INNER, innerName, this.TAGS_DEFAULTS], formId);
	}

	getStateElementConditionalTagsRef(name, formId) {
		return this.getState([this.ELEMENTS, name, this.CONDITIONAL_TAGS, this.TAGS_REF], formId);
	}

	getStateElementConditionalTagsRefInner(name, innerName, formId) {
		return this.getState([this.ELEMENTS, name, this.CONDITIONAL_TAGS_INNER, innerName, this.TAGS_REF], formId);
	}

	getStateElementConditionalTagsTags(name, formId) {
		return this.getState([this.ELEMENTS, name, this.CONDITIONAL_TAGS, this.TAGS], formId);
	}

	getStateElementConditionalTagsTagsInner(name, innerName, formId) {
		return this.getState([this.ELEMENTS, name, this.CONDITIONAL_TAGS_INNER, innerName, this.TAGS], formId);
	}

	getStateElementConfig(name, type, formId) {
		return this.getState([this.ELEMENTS, name, this.CONFIG, type], formId);
	}

	getStateElementField(name, formId) {
		return this.getState([this.ELEMENTS, name, this.FIELD], formId);
	}

	getStateElementLoaded(name, formId) {
		return this.getState([this.ELEMENTS, name, this.LOADED], formId);
	}

	getStateElementTypeInternal(name, formId) {
		return this.getState([this.ELEMENTS, name, this.TYPE_INTERNAL], formId);
	}

	getStateElementTypeCustom(name, formId) {
		return this.getState([this.ELEMENTS, name, this.TYPE_CUSTOM], formId);
	}

	getStateElementCustom(name, formId) {
		return this.getState([this.ELEMENTS, name, this.CUSTOM], formId);
	}

	getStateElementIsSingleSubmit(name, formId) {
		return this.getState([this.ELEMENTS, name, this.IS_SINGLE_SUBMIT], formId);
	}

	getStateElementSaveAsJson(name, formId) {
		return this.getState([this.ELEMENTS, name, this.SAVE_AS_JSON], formId);
	}

	getStateElementValueCountry(name, formId) {
		return this.getState([this.ELEMENTS, name, this.VALUE_COUNTRY], formId);
	}

	getStateElementInitial(name, formId) {
		return this.getState([this.ELEMENTS, name, this.INITIAL], formId);
	}

	getStateElementItems(name, formId) {
		return this.getState([this.ELEMENTS, name, this.ITEMS], formId);
	}

	getStateElementItem(name, value, formId) {
		return this.getState([this.ELEMENTS, name, this.ITEMS, value], formId);
	}

	getStateElementInput(name, formId) {
		return this.getState([this.ELEMENTS, name, this.INPUT], formId);
	}

	getStateElementItemsInput(name, nameItem, formId) {
		return this.getState([this.ELEMENTS, name, this.ITEMS, nameItem, this.INPUT], formId);
	}

	getStateElementItemsField(name, nameItem, formId) {
		return this.getState([this.ELEMENTS, name, this.ITEMS, nameItem, this.FIELD], formId);
	}

	getStateElementValue(name, formId) {
		return this.getState([this.ELEMENTS, name, this.VALUE], formId);
	}

	getStateElementError(name, formId) {
		return this.getState([this.ELEMENTS, name, this.ERROR], formId);
	}

	getStateElementTracking(name, formId) {
		return this.getState([this.ELEMENTS, name, this.TRACKING], formId);
	}

	getStateElementType(name, formId) {
		return this.getState([this.ELEMENTS, name, this.TYPE], formId);
	}

	getStateElementInputSelect(name, formId) {
		return this.getState([this.ELEMENTS, name, this.INPUT_SELECT], formId);
	}

	// ----------------------------------------
	// Captcha
	getStateCaptchaIsUsed() {
		return this.getState([this.IS_USED], this.CAPTCHA);
	}
	getStateCaptchaSiteKey() {
		return this.getState([this.CAPTCHA_SITE_KEY], this.CAPTCHA);
	}
	getStateCaptchaIsEnterprise() {
		return this.getState([this.CAPTCHA_IS_ENTERPRISE], this.CAPTCHA);
	}
	getStateCaptchaSubmitAction() {
		return this.getState([this.CAPTCHA_SUBMIT_ACTION], this.CAPTCHA);
	}
	getStateCaptchaInitAction() {
		return this.getState([this.CAPTCHA_INIT_ACTION], this.CAPTCHA);
	}
	getStateCaptchaLoadOnInit() {
		return this.getState([this.CAPTCHA_LOAD_ON_INIT], this.CAPTCHA);
	}
	getStateCaptchaHideBadge() {
		return this.getState([this.CAPTCHA_HIDE_BADGE], this.CAPTCHA);
	}

	// ----------------------------------------
	// Enrichment
	getStateEnrichmentIsUsed() {
		return this.getState([this.IS_USED], this.ENRICHMENT);
	}
	getStateEnrichmentExpiration() {
		return this.getState([this.ENRICHMENT_EXPIRATION], this.ENRICHMENT);
	}
	getStateEnrichmentAllowed() {
		return this.getState([this.ENRICHMENT_ALLOWED], this.ENRICHMENT);
	}
	getStateEnrichmentStorageName() {
		return this.getState([this.NAME], this.ENRICHMENT);
	}

	// ----------------------------------------
	// Events

	getStateEventsBeforeFormSubmit() {
		return this.getState([this.EVENTS_BEFORE_FORM_SUBMIT], this.EVENT);
	}
	getStateEventsAfterFormSubmit() {
		return this.getState([this.EVENTS_AFTER_FORM_SUBMIT], this.EVENT);
	}
	getStateEventsAfterFormSubmitSuccessBeforeRedirect() {
		return this.getState([this.EVENTS_AFTER_FORM_SUBMIT_SUCCESS_BEFORE_REDIRECT], this.EVENT);
	}
	getStateEventsAfterFormSubmitSuccess() {
		return this.getState([this.EVENTS_AFTER_FORM_SUBMIT_SUCCESS], this.EVENT);
	}
	getStateEventsAfterFormSubmitReset() {
		return this.getState([this.EVENTS_AFTER_FORM_SUBMIT_RESET], this.EVENT);
	}
	getStateEventsAfterFormSubmitError() {
		return this.getState([this.EVENTS_AFTER_FORM_SUBMIT_ERROR], this.EVENT);
	}
	getStateEventsAfterFormSubmitErrorValidation() {
		return this.getState([this.EVENTS_AFTER_FORM_SUBMIT_ERROR_VALIDATION], this.EVENT);
	}
	getStateEventsAfterFormSubmitEnd() {
		return this.getState([this.EVENTS_AFTER_FORM_SUBMIT_END], this.EVENT);
	}
	getStateEventsBeforeGtmDataPush() {
		return this.getState([this.EVENTS_BEFORE_GTM_DATA_PUSH], this.EVENT);
	}
	getStateEventsFormJsLoaded() {
		return this.getState([this.EVENTS_FORM_JS_LOADED], this.EVENT);
	}
	getStateEventsAfterCaptchaInit() {
		return this.getState([this.EVENTS_AFTER_CAPTCHA_INIT], this.EVENT);
	}

	// ----------------------------------------
	// Selectors
	getStateSelectorsClassActive() {
		return this.getState([this.SELECTORS_CLASS_ACTIVE], this.SELECTORS);
	}
	getStateSelectorsClassFilled() {
		return this.getState([this.SELECTORS_CLASS_FILLED], this.SELECTORS);
	}
	getStateSelectorsClassLoading() {
		return this.getState([this.SELECTORS_CLASS_LOADING], this.SELECTORS);
	}
	getStateSelectorsClassHidden() {
		return this.getState([this.SELECTORS_CLASS_HIDDEN], this.SELECTORS);
	}
	getStateSelectorsClassVisible() {
		return this.getState([this.SELECTORS_CLASS_VISIBLE], this.SELECTORS);
	}
	getStateSelectorsClassHasError() {
		return this.getState([this.SELECTORS_CLASS_HAS_ERROR], this.SELECTORS);
	}
	getStateSelectorsForm() {
		return this.getState([this.SELECTORS_FORM], this.SELECTORS);
	}

	getStateSelectorsSubmitSingle() {
		return this.getState([this.SELECTORS_SUBMIT_SINGLE], this.SELECTORS);
	}

	getStateSelectorsStep() {
		return this.getState([this.SELECTORS_STEP], this.SELECTORS);
	}

	getStateSelectorsStepSubmit() {
		return this.getState([this.SELECTORS_STEP_SUBMIT], this.SELECTORS);
	}

	getStateSelectorsError() {
		return this.getState([this.SELECTORS_ERROR], this.SELECTORS);
	}

	getStateSelectorsLoader() {
		return this.getState([this.SELECTORS_LOADER], this.SELECTORS);
	}

	getStateSelectorsGlobalMsg() {
		return this.getState([this.SELECTORS_GLOBAL_MSG], this.SELECTORS);
	}

	getStateSelectorsGroup() {
		return this.getState([this.SELECTORS_GROUP], this.SELECTORS);
	}

	getStateSelectorsField() {
		return this.getState([this.SELECTORS_FIELD], this.SELECTORS);
	}

	// ----------------------------------------
	// Attributes
	getStateAttributes() {
		return this.getStateTop(this.ATTRIBUTES);
	}
	getStateAttribute(name) {
		return this.getStateAttributes()[name];
	}

	// ----------------------------------------
	// Params
	getStateParams() {
		return this.getStateTop(this.PARAMS);
	}
	getStateParam(name) {
		return this.getStateParams()[name];
	}

	// ----------------------------------------
	// Other

	getStateFilteredBykey(obj, targetKey, findItem, formId) {
		return Object.values(Object.fromEntries(Object.entries(this.getState([obj], formId)).filter(([key, value]) => value[targetKey] === findItem)));
	}

	getFormElementByChild(element) {
		return element.closest(this.getStateSelectorsForm());
	}

	getFormFieldElementByChild(element) {
		return element.closest(this.getStateSelectorsField());
	}

	getFormIdByElement(element) {
		return this.getFormElementByChild(element).getAttribute(this.getStateAttribute('formPostId'));
	}

	getRestUrl(value) {
		return getRestUrl(value);
	}

	getRestUrlByType(type, value) {
		return getRestUrlByType(type, value);
	}
}

export const ROUTES = {
	// Common.
	PREFIX: esFormsLocalization.restRoutes.prefix,
	PREFIX_PROJECT: esFormsLocalization.restRoutes.prefixProject,
	PREFIX_SUBMIT: esFormsLocalization.restRoutes.prefixSubmit,
	PREFIX_TEST_API: esFormsLocalization.restRoutes.prefixTestApi,
	FILES: esFormsLocalization.restRoutes.files,

	// Admin.
	SETTINGS: esFormsLocalization.restRoutes.settings,
	CACHE_CLEAR: esFormsLocalization.restRoutes.cacheClear,
	MIGRATION: esFormsLocalization.restRoutes.migration,
	TRANSFER: esFormsLocalization.restRoutes.transfer,
	SYNC_DIRECT: esFormsLocalization.restRoutes.syncDirect,

	// Editor.
	PREFIX_INTEGRATIONS_ITEMS_INNER: esFormsLocalization.restRoutes.prefixIntegrationItemsInner,
	PREFIX_INTEGRATIONS_ITEMS: esFormsLocalization.restRoutes.prefixIntegrationItems,
	PREFIX_INTEGRATION_EDITOR: esFormsLocalization.restRoutes.prefixIntegrationEditor,
	INTEGRATIONS_EDITOR_SYNC: esFormsLocalization.restRoutes.integrationsEditorSync,
	INTEGRATIONS_EDITOR_CREATE: esFormsLocalization.restRoutes.integrationsEditorCreate,
	FORM_FIELDS: esFormsLocalization.restRoutes.formFields,
	COUNTRIES_GEOLOCATION: esFormsLocalization.restRoutes.countriesGeolocation,

	// Public.
	CAPTCHA: esFormsLocalization.restRoutes.captcha,
	VALIDATION_STEP: esFormsLocalization.restRoutes.validationStep,
}

export function getRestUrl(value, isPartial = false) {
	const prefix = isPartial ? ROUTES.PREFIX_PROJECT : ROUTES.PREFIX;

	const url = prefix.replace(/\/$/, ''); // Remove trailing slash.
	const sufix = value.replace(/^\/+/, ''); // Remove leading slash.

	return `${url}/${sufix}`;
}

export function getRestUrlByType(type, value, isPartial = false) {
	const prefix = isPartial ? ROUTES.PREFIX_PROJECT : ROUTES.PREFIX;

	const url = prefix.replace(/\/$/, ''); // Remove trailing slash.
	const sufix = value.replace(/^\/+/, ''); // Remove leading slash.
	const typePrefix = type.replace(/^\/|\/$/g, ''); // Remove leading and trailing slash.

	return `${url}/${typePrefix}/${sufix}`;
}
