/* global esFormsLocalization */
import manifest from './../../manifest.json';
import { CONDITIONAL_TAGS_ACTIONS } from '../../../conditional-tags/assets/utils';

////////////////////////////////////////////////////////////////
// Constants
////////////////////////////////////////////////////////////////

// Prefix all forms JS with this string.
export const prefix = 'esForms';

// Enum object for all state items.
export const StateEnum = {
	// State names.
	ISLOADED: 'isloaded',
	ELEMENTS: 'elements',
	FORM: 'form',

	POST_ID: 'postId',
	METHOD: 'method',
	ACTION: 'action',
	ACTION_EXTERNAL: 'actionExternal',
	FIELD: 'field',
	VALUE: 'value',
	VALUE_COMBINED: 'valueCombined',
	INITIAL: 'initial',
	VALUES: 'values',
	VALUE_COUNTRY: 'valueCountry',
	INPUT: 'input',
	INPUT_SELECT: 'inputSelect',
	ITEMS: 'items',
	CUSTOM: 'custom',
	IS_DISABLED: 'disabled',
	TYPE: 'type',
	TYPE_SETTINGS: 'typeSettings',
	TYPE_INTERNAL: 'typeInternal',
	TYPE_CUSTOM: 'typeCustom',
	NAME: 'name',
	ERROR: 'error',
	GLOBAL_MSG: 'globalMsg',
	HAS_ERROR: 'hasError',
	LOADED: 'loaded',
	LOADER: 'loader',
	ELEMENT: 'element',
	HEADING_SUCCESS: 'headingSuccess',
	HEADING_ERROR: 'headingError',
	IS_SINGLE_SUBMIT: 'isSingleSubmit',
	SAVE_AS_JSON: 'saveAsJson',
	IS_ADMIN: 'isAdmin',
	IS_USED: 'isUsed',
	NONCE: 'nonce',

	// Conditional tags
	CONDITIONAL_TAGS: 'conditionalTags',
	CONDITIONAL_TAGS_INNER: 'conditionalTagsInner',
	TAGS: 'tags',
	TAGS_REF: 'reference',
	TAGS_DEFAULTS: 'defaults',
	TAGS_EVENTS: 'events',
	CONDITIONAL_TAGS_IGNORE: 'conditionalTagsIgnore',
	CONDITIONAL_TAGS_FORM: 'conditionalTagsForm',
	CONDITIONAL_TAGS_EVENTS: 'conditionalTagsEvents',
	CONDITIONAL_TAGS_INNER_EVENTS: 'conditionalTagsInnerEvents',

	CONFIG: 'config',
	CONFIG_SELECT_USE_SEARCH: 'useSearch',
	CONFIG_PHONE_DISABLE_PICKER: 'disablePicker',
	CONFIG_PHONE_USE_PHONE_SYNC: 'usePhoneSync',
	CONFIG_SUCCESS_REDIRECT: 'successRedirect',
	CONFIG_SUCCESS_REDIRECT_VARIATION: 'successRedirectVariation',
	CONFIG_DOWNLOADS: 'downloads',

	SETTINGS: 'settings',
	SETTINGS_DISABLE_SCROLL_TO_GLOBAL_MSG_ON_SUCCESS: 'disableScrollToGlobalMsgOnSuccess',
	SETTINGS_DISABLE_SCROLL_TO_FIELD_ON_ERROR: 'disableScrollToFieldOnError',
	SETTINGS_FORM_RESET_ON_SUCCES: 'formResetOnSuccess',
	SETTINGS_FORM_DISABLE_NATIVE_REDIRECT_ON_SUCCESS: 'formDisableNativeRedirectOnSuccess',
	SETTINGS_REDIRECTION_TIMEOUT: 'redirectionTimeout',
	SETTINGS_HIDE_GLOBAL_MESSAGE_TIMEOUT: 'hideGlobalMessageTimeout',
	SETTINGS_FILE_REMOVE_LABEL: 'fileRemoveLabel',

	CAPTCHA: 'captcha',
	CAPTCHA_SITE_KEY: 'site_key',
	CAPTCHA_IS_ENTERPRISE: 'isEnterprise',
	CAPTCHA_SUBMIT_ACTION: 'submitAction',
	CAPTCHA_INIT_ACTION: 'initAction',
	CAPTCHA_LOAD_ON_INIT: 'loadOnInit',
	CAPTCHA_HIDE_BADGE: 'hideBadge',

	ENRICHMENT: 'enrichment',
	ENRICHMENT_EXPIRATION: 'expiration',
	ENRICHMENT_ALLOWED: 'allowed',

	EVENTS: 'events',
	EVENTS_BEFORE_FORM_SUBMIT: 'beforeFormSubmit',
	EVENTS_AFTER_FORM_SUBMIT: 'afterFormSubmit',
	EVENTS_AFTER_FORM_SUBMIT_SUCCESS: 'afterFormSubmitSuccess',
	EVENTS_AFTER_FORM_SUBMIT_ERROR: 'afterFormSubmitError',
	EVENTS_AFTER_FORM_SUBMIT_ERROR_VALIDATION: 'afterFormSubmitErrorValidation',
	EVENTS_AFTER_FORM_SUBMIT_END: 'afterFormSubmitEnd',
	EVENTS_AFTER_GTM_DATA_PUSH: 'afterGtmDataPush',
	EVENTS_AFTER_FORM_SUBMIT_RESET: 'afterFormSubmitReset',
	EVENTS_AFTER_FORM_SUBMIT_SUCCESS_BEFORE_REDIRECT: 'afterFormSubmitSuccessBeforeRedirect',
	EVENTS_FORM_JS_LOADED: 'jsFormLoaded',
	EVENTS_AFTER_CAPTCHA_INIT: 'afterCaptchaInit',

	EVENTS_STEPS_GO_TO_NEXT_STEP: 'goToNextStep',
	EVENTS_STEPS_GO_TO_PREV_STEP: 'goToPrevStep',

	SELECTORS: 'selectors',
	SELECTORS_CLASS_ACTIVE: 'isActive',
	SELECTORS_CLASS_FILLED: 'isFilled',
	SELECTORS_CLASS_LOADING: 'isLoading',
	SELECTORS_CLASS_HIDDEN: 'isHidden',
	SELECTORS_CLASS_VISIBLE: 'isVisible',
	SELECTORS_CLASS_HAS_ERROR: 'hasError',

	TRACKING: 'tracking',
	TRACKING_EVENT_NAME: 'eventName',
	TRACKING_EVENT_ADDITIONAL_DATA: 'eventAdditionalData',

	STEPS: 'steps',
	STEPS_FLOW: 'flow',
	STEPS_CURRENT: 'current',
	STEPS_ITEMS: 'items',
	STEPS_ELEMENTS: 'elements',
	STEPS_ELEMENTS_PROGRESS_BAR: 'elementsProgressBar',

	// Specific selectors.
	SELECTORS_PREFIX: 'prefix',
	SELECTORS_FORM: 'form',
	SELECTORS_SUBMIT_SINGLE: 'singleSubmit',
	SELECTORS_STEP: `step`,
	SELECTORS_STEP_PROGRESS_BAR: `stepProgressBar`,
	SELECTORS_STEP_SUBMIT: `stepSubmit`,
	SELECTORS_ERROR: `error`,
	SELECTORS_LOADER: `loader`,
	SELECTORS_GLOBAL_MSG: `globalMsg`,
	SELECTORS_GROUP: `group`,
	SELECTORS_FIELD: `field`,

	ATTRIBUTES: 'attributes',
	PARAMS: 'params',
};

////////////////////////////////////////////////////////////////
// Initial states.
////////////////////////////////////////////////////////////////

/**
 * Set state initial window if it doesn't exist.
 *
 * @returns {void}
 */
export function setStateWindow() {
	if (!window[prefix]) {
		window[prefix] = {};
	}
}

/**
 * Set state initial values.
 *
 * @returns {void}
 */
export function setStateInitial() {
	setStateWindow();

	if (window[prefix].state) {
		return;
	}

	window[prefix].state = {};
	window[prefix].state = {
		[StateEnum.CAPTCHA]: {},
		[StateEnum.ENRICHMENT]: {},
		[StateEnum.SETTINGS]: {},
		[StateEnum.EVENTS]: {},
		[StateEnum.SELECTORS]: {},
		[StateEnum.ATTRIBUTES]: {},
		[StateEnum.PARAMS]: {},
		[StateEnum.CONFIG]: {},
	};

	// Attributes.
	for (const [key, item] of Object.entries(esFormsLocalization.customFormDataAttributes ?? {})) {
		setState([key], item, StateEnum.ATTRIBUTES);
	}

	// Params.
	for (const [key, item] of Object.entries(esFormsLocalization.customFormParams ?? {})) {
		setState([key], item, StateEnum.PARAMS);
	}

	setState([StateEnum.IS_ADMIN], esFormsLocalization.isAdmin, StateEnum.CONFIG);
	setState([StateEnum.NONCE], esFormsLocalization.nonce, StateEnum.CONFIG);

	// Global settings.
	setState([StateEnum.SETTINGS_DISABLE_SCROLL_TO_GLOBAL_MSG_ON_SUCCESS], Boolean(esFormsLocalization.formDisableScrollToGlobalMessageOnSuccess), StateEnum.SETTINGS);
	setState([StateEnum.SETTINGS_DISABLE_SCROLL_TO_FIELD_ON_ERROR], Boolean(esFormsLocalization.formDisableScrollToFieldOnError), StateEnum.SETTINGS);
	setState([StateEnum.SETTINGS_FORM_RESET_ON_SUCCESS], Boolean(esFormsLocalization.formResetOnSuccess), StateEnum.SETTINGS);
	setState([StateEnum.SETTINGS_FORM_DISABLE_NATIVE_REDIRECT_ON_SUCCESS], Boolean(esFormsLocalization.formDisableNativeRedirectOnSuccess), StateEnum.SETTINGS);
	setState([StateEnum.SETTINGS_REDIRECTION_TIMEOUT], esFormsLocalization.redirectionTimeout ?? 600, StateEnum.SETTINGS);
	setState([StateEnum.SETTINGS_HIDE_GLOBAL_MESSAGE_TIMEOUT], esFormsLocalization.hideGlobalMessageTimeout ?? 6000, StateEnum.SETTINGS);
	setState([StateEnum.SETTINGS_FILE_REMOVE_LABEL], esFormsLocalization.fileRemoveLabel ?? '', StateEnum.SETTINGS);
	setState([StateEnum.SETTINGS_FORM_DISABLE_AUTO_INIT], Boolean(esFormsLocalization.formDisableAutoInit), StateEnum.SETTINGS);

	// Captcha.
	const captcha = esFormsLocalization.captcha ?? {};
	setState([StateEnum.IS_USED], Boolean(captcha.isUsed), StateEnum.CAPTCHA);

	if (captcha.isUsed) {
		setState([StateEnum.CAPTCHA_SITE_KEY], captcha.siteKey, StateEnum.CAPTCHA);
		setState([StateEnum.CAPTCHA_IS_ENTERPRISE], Boolean(captcha.isEnterprise), StateEnum.CAPTCHA);
		setState([StateEnum.CAPTCHA_SUBMIT_ACTION], captcha.submitAction, StateEnum.CAPTCHA);
		setState([StateEnum.CAPTCHA_INIT_ACTION], captcha.initAction, StateEnum.CAPTCHA);
		setState([StateEnum.CAPTCHA_LOAD_ON_INIT], Boolean(captcha.loadOnInit), StateEnum.CAPTCHA);
		setState([StateEnum.CAPTCHA_HIDE_BADGE], Boolean(captcha.hideBadge), StateEnum.CAPTCHA);
	}

	// Enrichment.
	const enrichment = esFormsLocalization.enrichment ?? {};
	setState([StateEnum.IS_USED], Boolean(enrichment.isUsed), StateEnum.ENRICHMENT);

	if (enrichment.isUsed) {
		setState([StateEnum.ENRICHMENT_EXPIRATION], enrichment.expiration, StateEnum.ENRICHMENT);
		setState([StateEnum.ENRICHMENT_ALLOWED], enrichment.allowed, StateEnum.ENRICHMENT);
		setState([StateEnum.NAME], 'es-storage', StateEnum.ENRICHMENT);
	}

	// Events.
	setState([StateEnum.EVENTS_BEFORE_FORM_SUBMIT], getStateEventName(StateEnum.EVENTS_BEFORE_FORM_SUBMIT), StateEnum.EVENTS);
	setState([StateEnum.EVENTS_AFTER_FORM_SUBMIT], getStateEventName(StateEnum.EVENTS_AFTER_FORM_SUBMIT), StateEnum.EVENTS);
	setState([StateEnum.EVENTS_AFTER_FORM_SUBMIT_SUCCESS_BEFORE_REDIRECT], getStateEventName(StateEnum.EVENTS_AFTER_FORM_SUBMIT_SUCCESS_BEFORE_REDIRECT), StateEnum.EVENTS);
	setState([StateEnum.EVENTS_AFTER_FORM_SUBMIT_SUCCESS], getStateEventName(StateEnum.EVENTS_AFTER_FORM_SUBMIT_SUCCESS), StateEnum.EVENTS);
	setState([StateEnum.EVENTS_AFTER_FORM_SUBMIT_RESET], getStateEventName(StateEnum.EVENTS_AFTER_FORM_SUBMIT_RESET), StateEnum.EVENTS);
	setState([StateEnum.EVENTS_AFTER_FORM_SUBMIT_ERROR], getStateEventName(StateEnum.EVENTS_AFTER_FORM_SUBMIT_ERROR), StateEnum.EVENTS);
	setState([StateEnum.EVENTS_AFTER_FORM_SUBMIT_ERROR_VALIDATION], getStateEventName(StateEnum.EVENTS_AFTER_FORM_SUBMIT_ERROR_VALIDATION), StateEnum.EVENTS);
	setState([StateEnum.EVENTS_AFTER_FORM_SUBMIT_END], getStateEventName(StateEnum.EVENTS_AFTER_FORM_SUBMIT_END), StateEnum.EVENTS);
	setState([StateEnum.EVENTS_AFTER_GTM_DATA_PUSH], getStateEventName(StateEnum.EVENTS_AFTER_GTM_DATA_PUSH), StateEnum.EVENTS);
	setState([StateEnum.EVENTS_FORM_JS_LOADED], getStateEventName(StateEnum.EVENTS_FORM_JS_LOADED), StateEnum.EVENTS);
	setState([StateEnum.EVENTS_AFTER_CAPTCHA_INIT], getStateEventName(StateEnum.EVENTS_AFTER_CAPTCHA_INIT), StateEnum.EVENTS);
	setState([StateEnum.EVENTS_STEPS_GO_TO_NEXT_STEP], getStateEventName(StateEnum.EVENTS_STEPS_GO_TO_NEXT_STEP), StateEnum.EVENTS);
	setState([StateEnum.EVENTS_STEPS_GO_TO_PREV_STEP], getStateEventName(StateEnum.EVENTS_STEPS_GO_TO_PREV_STEP), StateEnum.EVENTS);

	// Selectors.
	setState([StateEnum.SELECTORS_CLASS_ACTIVE], 'is-active', StateEnum.SELECTORS);
	setState([StateEnum.SELECTORS_CLASS_FILLED], 'is-filled', StateEnum.SELECTORS);
	setState([StateEnum.SELECTORS_CLASS_LOADING], 'is-loading', StateEnum.SELECTORS);
	setState([StateEnum.SELECTORS_CLASS_HIDDEN], 'is-hidden', StateEnum.SELECTORS);
	setState([StateEnum.SELECTORS_CLASS_VISIBLE], 'is-visible', StateEnum.SELECTORS);
	setState([StateEnum.SELECTORS_CLASS_HAS_ERROR], 'has-error', StateEnum.SELECTORS);

	setState([StateEnum.SELECTORS_PREFIX], `.${manifest.componentJsClass}`, StateEnum.SELECTORS);
	setState([StateEnum.SELECTORS_FORM], `.${manifest.componentJsClass}`, StateEnum.SELECTORS);
	setState([StateEnum.SELECTORS_SUBMIT_SINGLE], `.${manifest.componentJsClass}-single-submit`, StateEnum.SELECTORS);
	setState([StateEnum.SELECTORS_STEP], `.${manifest.componentJsClass}-step`, StateEnum.SELECTORS);
	setState([StateEnum.SELECTORS_STEP_PROGRESS_BAR], `.${manifest.componentJsClass}-step-progress-bar`, StateEnum.SELECTORS);
	setState([StateEnum.SELECTORS_STEP_SUBMIT], `.${manifest.componentJsClass}-step-trigger`, StateEnum.SELECTORS);
	setState([StateEnum.SELECTORS_ERROR], `.${manifest.componentJsClass}-error`, StateEnum.SELECTORS);
	setState([StateEnum.SELECTORS_LOADER], `.${manifest.componentJsClass}-loader`, StateEnum.SELECTORS);
	setState([StateEnum.SELECTORS_GLOBAL_MSG], `.${manifest.componentJsClass}-global-msg`, StateEnum.SELECTORS);
	setState([StateEnum.SELECTORS_GROUP], `.${manifest.componentJsClass}-group`, StateEnum.SELECTORS);
	setState([StateEnum.SELECTORS_FIELD], `.${manifest.componentJsClass}-field`, StateEnum.SELECTORS);
}

/**
 * Set state initial form values.
 *
 * @param {string} formId Form ID.
 *
 * @returns {void}
 */
export function setStateFormInitial(formId) {
	setStateWindow();
	window[prefix].state[`form_${formId}`] = {};
	window[prefix].state[`form_${formId}`] = {
		[StateEnum.ELEMENTS]: {},
		[StateEnum.FORM]: {},
	};

	let formElement = '';

	if (formId === 0) {
		formElement = document.querySelector(getState([StateEnum.SELECTORS_FORM], StateEnum.SELECTORS));
	} else {
		formElement = document.querySelector(`${getState([StateEnum.SELECTORS_FORM], StateEnum.SELECTORS)}[${getStateAttribute('formId')}="${formId}"]`);
	}

	setState([StateEnum.FORM, StateEnum.POST_ID],formElement.getAttribute(getStateAttribute('postId')), formId);
	setState([StateEnum.FORM, StateEnum.ISLOADED], false, formId);
	setState([StateEnum.FORM, StateEnum.IS_SINGLE_SUBMIT], false, formId);
	setState([StateEnum.FORM, StateEnum.ELEMENT], formElement, formId);
	setState([StateEnum.FORM, StateEnum.TYPE], formElement.getAttribute(getStateAttribute('formType')), formId);
	setState([StateEnum.FORM, StateEnum.METHOD], formElement.getAttribute('method'), formId);
	setState([StateEnum.FORM, StateEnum.ACTION], formElement.getAttribute('action'), formId);
	setState([StateEnum.FORM, StateEnum.ACTION_EXTERNAL], formElement.getAttribute(getStateAttribute('actionExternal')), formId);
	setState([StateEnum.FORM, StateEnum.TYPE_SETTINGS], formElement.getAttribute(getStateAttribute('settingsType')), formId);
	setState([StateEnum.FORM, StateEnum.LOADER], formElement.querySelector(getState([StateEnum.SELECTORS_LOADER], StateEnum.SELECTORS)), formId);
	setState([StateEnum.FORM, StateEnum.TRACKING, StateEnum.TRACKING_EVENT_NAME], formElement.getAttribute(getStateAttribute('trackingEventName')), formId);
	setState([StateEnum.FORM, StateEnum.TRACKING, StateEnum.TRACKING_EVENT_ADDITIONAL_DATA], JSON.parse(formElement.getAttribute(getStateAttribute('trackingAdditionalData'))), formId);

	// Form settings
	setState([StateEnum.FORM, StateEnum.CONFIG, StateEnum.CONFIG_PHONE_DISABLE_PICKER], Boolean(formElement.getAttribute(getStateAttribute('phoneDisablePicker'))), formId);
	setState([StateEnum.FORM, StateEnum.CONFIG, StateEnum.CONFIG_PHONE_USE_PHONE_SYNC], Boolean(formElement.getAttribute(getStateAttribute('phoneSync'))), formId);
	setState([StateEnum.FORM, StateEnum.CONFIG, StateEnum.CONFIG_SUCCESS_REDIRECT], formElement.getAttribute(getStateAttribute('successRedirect')), formId);
	setState([StateEnum.FORM, StateEnum.CONFIG, StateEnum.CONFIG_SUCCESS_REDIRECT_VARIATION], formElement.getAttribute(getStateAttribute('successRedirectVariation')), formId);
	setState([StateEnum.FORM, StateEnum.CONFIG, StateEnum.CONFIG_DOWNLOADS], JSON.parse(formElement.getAttribute(getStateAttribute('downloads'))), formId);

	const globalMsg = formElement.querySelector(getState([StateEnum.SELECTORS_GLOBAL_MSG], StateEnum.SELECTORS));
	setState([StateEnum.FORM, StateEnum.GLOBAL_MSG, StateEnum.ELEMENT], globalMsg, formId);
	setState([StateEnum.FORM, StateEnum.GLOBAL_MSG, StateEnum.HEADING_SUCCESS], globalMsg.getAttribute(getStateAttribute('globalMsgHeadingSuccess')), formId);
	setState([StateEnum.FORM, StateEnum.GLOBAL_MSG, StateEnum.HEADING_ERROR], globalMsg.getAttribute(getStateAttribute('globalMsgHeadingError')), formId);

	// Conditional tags
	setState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_EVENTS], {}, formId);
	setState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_INNER_EVENTS], {}, formId);
	setState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_IGNORE], [], formId);
	setState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_FORM], JSON.parse(formElement.getAttribute(getStateAttribute('conditionalTags'))), formId);

	// Steps.
	const steps = formElement.querySelectorAll(getState([StateEnum.SELECTORS_STEP], StateEnum.SELECTORS));
	setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.IS_USED], false, formId);

	if (steps.length) {
		setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.IS_USED], true, formId);
		setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_FLOW], [], formId);
		setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_CURRENT], '', formId);
		setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_ITEMS], {}, formId);
		setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_ELEMENTS], {}, formId);

		Object.values(steps).forEach((item, index) => {
			const stepFields = item.querySelectorAll(getState([StateEnum.SELECTORS_FIELD], StateEnum.SELECTORS));
			const stepId = item.getAttribute(getStateAttribute('stepId'));
			const stepOutput = [];

			stepFields.forEach((stepField) => {
				const stepFieldName = stepField.getAttribute(getStateAttribute('fieldName'));

				if (stepFieldName) {
					stepOutput.push(stepFieldName);
				}
			});

			setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_ELEMENTS, stepId], item, formId);
			setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_ITEMS, stepId], stepOutput, formId);

			if (index === 0) {
				setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_CURRENT], stepId, formId);
			}
		});

		const stepsProgressBar = formElement.querySelectorAll(getState([StateEnum.SELECTORS_STEP_PROGRESS_BAR], StateEnum.SELECTORS));
		if (stepsProgressBar.length) {
			setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_ELEMENTS_PROGRESS_BAR], {}, formId);

			Object.values(stepsProgressBar).forEach((item, index) => {
				const stepId = item.getAttribute(getStateAttribute('stepId'));
				setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_ELEMENTS_PROGRESS_BAR, stepId], item, formId);
			});
		}
	}

	// Loop all fields.
	for (const item of Object.values(formElement.querySelectorAll('input, select, textarea'))) {
		const {
			value,
			name,
			type,
			disabled,
		} = item;

		if (name === 'search_terms') {
			continue;
		}

		const field = formElement.querySelector(`${getState([StateEnum.SELECTORS_FIELD], StateEnum.SELECTORS)}[${getStateAttribute('fieldName')}="${name}"]`);

		// Make changes depending on the field type.
		switch (type) {
			case 'radio':
			case 'checkbox':
				setState([StateEnum.ELEMENTS, name, StateEnum.TYPE], type, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.TYPE_INTERNAL], type, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.INPUT], '', formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.ITEMS, value, StateEnum.VALUE], value, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.ITEMS, value, StateEnum.FIELD], item.parentNode.parentNode, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.ITEMS, value, StateEnum.INPUT], item, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.ITEMS, value, StateEnum.NAME], name, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.IS_DISABLED, value], disabled, formId);

				if (type === 'radio') {
					if (!getState([StateEnum.ELEMENTS, name, StateEnum.INITIAL], formId)) {
						setState([StateEnum.ELEMENTS, name, StateEnum.INITIAL], item.checked ? value : '', formId);
					}

					if (!getState([StateEnum.ELEMENTS, name, StateEnum.VALUE], formId)) {
						setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], item.checked ? value : '', formId);
					}

					setState([StateEnum.ELEMENTS, name, StateEnum.TRACKING], field.getAttribute(getStateAttribute('tracking')), formId);
				}

				if (type === 'checkbox') {
					setState([StateEnum.ELEMENTS, name, StateEnum.VALUE, value], item.checked ? value : '', formId);
					setState([StateEnum.ELEMENTS, name, StateEnum.INITIAL, value], item.checked ? value : '', formId);
					setState([StateEnum.ELEMENTS, name, StateEnum.TRACKING, value], item.parentNode.parentNode.getAttribute(getStateAttribute('tracking')), formId);
				}

				setStateConditionalTagsItems(item.parentNode.parentNode.getAttribute(getStateAttribute('conditionalTags')), name, value, formId);

				break;
			case 'select-one':
				// Combined fields like phone can have field null.
				const customField = item.closest(getState([StateEnum.SELECTORS_FIELD], StateEnum.SELECTORS)); // eslint-disable-line no-case-declarations
				const typeTemp = customField.getAttribute(getStateAttribute('fieldType')); // eslint-disable-line no-case-declarations

				if (item.options.length) {
					const customData = JSON.parse(item.options[item.options.selectedIndex].getAttribute(getStateAttribute('selectCustomProperties')));

					switch (typeTemp) {
						case 'phone':
						case 'country':
							setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY, 'code'], customData[getStateAttribute('selectCountryCode')], formId);
							setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY, 'label'], customData[getStateAttribute('selectCountryLabel')], formId);
							setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY, 'number'], customData[getStateAttribute('selectCountryNumber')], formId);
							break;
						}
				}

				if (typeTemp !== 'phone') {
					setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], value, formId);
					setState([StateEnum.ELEMENTS, name, StateEnum.INITIAL], value, formId);
				}

				setState([StateEnum.ELEMENTS, name, StateEnum.IS_DISABLED], disabled, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.TYPE_INTERNAL], typeTemp, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.TYPE], 'select', formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.INPUT], item, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.CONFIG, StateEnum.CONFIG_SELECT_USE_SEARCH], Boolean(item.getAttribute(getStateAttribute('selectAllowSearch'))), formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.TRACKING], field.getAttribute(getStateAttribute('tracking')), formId);
				break;
			case 'tel':
				setState([StateEnum.ELEMENTS, name, StateEnum.INITIAL], value, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], value, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.TYPE], type, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.TYPE_INTERNAL], 'tel', formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.INPUT], item, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.IS_DISABLED], disabled, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.INPUT_SELECT], field.querySelector('select'), formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.TRACKING], field.getAttribute(getStateAttribute('tracking')), formId);

				if (!value) {
					setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COMBINED], '', formId);
				} else {
					setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COMBINED], `${getState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY], formId).number}${value}`, formId);
				}
				break;
			case 'date':
			case 'datetime-local':
				setState([StateEnum.ELEMENTS, name, StateEnum.INITIAL], value, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], value, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.TYPE], type, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.TYPE_INTERNAL], type, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.INPUT], item, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.IS_DISABLED], disabled, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.TRACKING], field.getAttribute(getStateAttribute('tracking')), formId);

				if (type === 'datetime-local') {
					setState([StateEnum.ELEMENTS, name, StateEnum.TYPE], 'date', formId);
					setState([StateEnum.ELEMENTS, name, StateEnum.TYPE_INTERNAL], 'datetime', formId);
				}
				break;
			default:
				setState([StateEnum.ELEMENTS, name, StateEnum.INITIAL], value, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], value, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.TYPE], type, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.TYPE_INTERNAL], type, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.INPUT], item, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.IS_DISABLED], disabled, formId);

				if (field.getAttribute(getStateAttribute('fieldPreventSubmit'))) {
					setState([StateEnum.ELEMENTS, name, StateEnum.IS_DISABLED], Boolean(field.getAttribute(getStateAttribute('fieldPreventSubmit'))), formId);
				}
				setState([StateEnum.ELEMENTS, name, StateEnum.TRACKING], field.getAttribute(getStateAttribute('tracking')), formId);
				break;
		}

		setState([StateEnum.ELEMENTS, name, StateEnum.HAS_ERROR], false, formId);
		setState([StateEnum.ELEMENTS, name, StateEnum.LOADED], false, formId);
		setState([StateEnum.ELEMENTS, name, StateEnum.NAME], name, formId);
		setState([StateEnum.ELEMENTS, name, StateEnum.FIELD], field, formId);
		setState([StateEnum.ELEMENTS, name, StateEnum.ERROR], field?.querySelector(getState([StateEnum.SELECTORS_ERROR], StateEnum.SELECTORS)), formId);
		setState([StateEnum.ELEMENTS, name, StateEnum.IS_SINGLE_SUBMIT], item?.classList?.contains(getState([StateEnum.SELECTORS_SUBMIT_SINGLE], StateEnum.SELECTORS).substring(1)), formId);
		setState([StateEnum.ELEMENTS, name, StateEnum.TYPE_CUSTOM], field?.getAttribute(getStateAttribute('fieldTypeCustom')), formId);
		setState([StateEnum.ELEMENTS, name, StateEnum.SAVE_AS_JSON], Boolean(item.getAttribute(getStateAttribute('saveAsJson'))), formId);

		// Conditional tags.
		if (field) {
			setStateConditionalTags(field, name, formId);
		}
	}
}

/**
 * Set state values when the field changes.
 *
 * @param {object} item Item/field to check.
 * @param {string} formId Form ID.
 *
 * * @returns {void}
 */
export function setStateValues(item, formId) {
 const {
	 name,
	 value,
	 checked,
	 type,
 } = item;

 // Datepicker and dropzone are set using native lib events.

	switch (type) {
		case 'radio':
			setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], checked ? value : '', formId);
			break;
		case 'checkbox':
			setState([StateEnum.ELEMENTS, name, StateEnum.VALUE, value], checked ? value : '', formId);
			break;
		case 'select-one':
			const customField = item.closest(getState([StateEnum.SELECTORS_FIELD], StateEnum.SELECTORS)); // eslint-disable-line no-case-declarations
			const typeCustom = customField.getAttribute(getStateAttribute('fieldType')); // eslint-disable-line no-case-declarations
			const customData = JSON.parse(item.options[item.options.selectedIndex].getAttribute(getStateAttribute('selectCustomProperties'))); // eslint-disable-line no-case-declarations

			switch (typeCustom) {
				case 'phone':
				case 'country':
					setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY, 'code'], customData[getStateAttribute('selectCountryCode')], formId);
					setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY, 'label'], customData[getStateAttribute('selectCountryLabel')], formId);
					setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY, 'number'], customData[getStateAttribute('selectCountryNumber')], formId);
					break;
			}

			if (typeCustom !== 'phone') {
				setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], value, formId);
			}

			if (typeCustom === 'phone') {
				setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COMBINED], '', formId);

				if (getState([StateEnum.ELEMENTS, name, StateEnum.VALUE], formId)) {
					setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COMBINED], `${getState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY], formId).number}${getState([StateEnum.ELEMENTS, name, StateEnum.VALUE], formId)}`, formId);
				}
			}
		break;
		case 'tel':
			setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], value, formId);
			setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COMBINED], '', formId);

			if (value) {
				setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COMBINED], `${getState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY], formId).number}${value}`, formId);
			}
			break;
		default:
			setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], value, formId);
			break;
	}
}

/**
 * Set state conditional tags on one field.
 *
 * @param {object} field Field object.
 * @param {string} name Field name.
 * @param {string} formId Form ID.
 * 
 * @returns {void}
 */
export function setStateConditionalTags(field, name, formId) {
	const conditionalTags = field.getAttribute(getStateAttribute('conditionalTags'));

	setState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS, StateEnum.TAGS_DEFAULTS], CONDITIONAL_TAGS_ACTIONS.SHOW, formId);
	setState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS, StateEnum.TAGS], [], formId);
	setState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS, StateEnum.TAGS_REF], [], formId);

	if (conditionalTags) {
		const tag = JSON.parse(conditionalTags)?.[0];

		setState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS, StateEnum.TAGS_DEFAULTS], tag[0], formId);
		setState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS, StateEnum.TAGS], tag[1], formId);

		setStateConditionalTagsInner(name, formId, tag[1]);
	}
}


/**
 * Set state conditional tags inner items on one field.
 *
 * @param {object} conditionalTags Field object.
 * @param {string} name Field name.
 * @param {string} innerName Conditional tag inner name.
 * @param {string} formId Form ID.
 * 
 * @returns {void}
 */
export function setStateConditionalTagsItems(conditionalTags, name, innerName, formId) {
	if (!innerName) {
		return;
	}

	setState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS_INNER, innerName, StateEnum.TAGS_DEFAULTS], CONDITIONAL_TAGS_ACTIONS.SHOW, formId);
	setState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS_INNER, innerName, StateEnum.TAGS], [], formId);
	setState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS_INNER, innerName, StateEnum.TAGS_REF], [], formId);

	if (!conditionalTags) {
		return;
	}

	const tag = JSON.parse(conditionalTags)?.[0];

	setState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS_INNER, innerName, StateEnum.TAGS_DEFAULTS], tag[0], formId);
	setState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS_INNER, innerName, StateEnum.TAGS], tag[1], formId);

	setStateConditionalTagsInner(name, formId, tag[1], innerName);
}

/**
 * Set state conditional tags inner item on one field.
 *
 * @param {string} name Field name.
 * @param {string} formId Form ID.
 * @param {array} tags Tags array.
 * @param {string} innerName Conditional tag inner name.
 * 
 * @returns {void}
 */
export function setStateConditionalTagsInner(name, formId, tags, innerName = '') {
	const refOutput = [];

	const isInner = Boolean(innerName);

	const events = isInner ? getState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_INNER_EVENTS], formId) : getState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_EVENTS], formId);

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
		setState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_INNER_EVENTS], eventsOutput, formId);
		setState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS_INNER, innerName, StateEnum.TAGS_REF], refOutput, formId);
	} else {
		setState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_EVENTS], eventsOutput, formId);
		setState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS, StateEnum.TAGS_REF], refOutput, formId);
	}
}

////////////////////////////////////////////////////////////////
// Helpers.
////////////////////////////////////////////////////////////////

/**
 * Set state helper.
 *
 * @param {array} keyArray Array of keys of object.
 * @param {mixed} value Any mixed value to store.
 * @param {string} formId Form Id.
 *
 * @returns {void}
 */
export function setState(keyArray, value, formId) {
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

/**
 * Get state helper.
 *
 * @param {array} keys Array of keys of object.
 * @param {string} formId Form Id.
 *
 * @returns {mixed}
 */
export function getState(keys, formId) {
	const formKey = isNaN(formId) ? formId : `form_${formId}`;
	let stateObject = window?.[prefix]?.state?.[formKey];

	if (!stateObject) {
		return undefined;
	}

	keys.forEach((key) => { // eslint-disable-line consistent-return
		stateObject = stateObject?.[key];
		if (!stateObject) {
			return undefined;
		}
	});

	return stateObject;
}

/**
 * Get state top level key.
 *
 * @param {string} name Name key to get.
 *
 * @returns {mixed}
 */
export function getStateTop(name) {
	return window?.[prefix]?.state?.[name];
}

/**
 * Get state event name with prefix.
 *
 * @param {string} name Name key to get.
 *
 * @returns {string}
 */
export function getStateEventName(name) {
	const output = name.charAt(0).toUpperCase() + name.slice(1);

	return `${prefix}${output}`;
}

/**
 * Get state attributes.
 *
 * @returns {object}
 */
export function getStateAttributes() {
	return getStateTop(StateEnum.ATTRIBUTES);
}

/**
 * Get state attribute.
 *
 * @returns {string}
 */
export function getStateAttribute(name) {
	return getStateAttributes()[name];
}
