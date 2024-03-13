/* global esFormsLocalization */

import globalManifest from '../../../manifest.json';
import utilsManifest from '../../../../../vendor/infinum/eightshift-forms-utils/src/manifest.json';
import { CONDITIONAL_TAGS_ACTIONS } from '../../conditional-tags/assets/utils';

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
	ELEMENTS_FIELDS: 'elementsFields',
	FORM: 'form',
	FORMS: 'forms',

	POST_ID: 'postId',
	METHOD: 'method',
	ACTION: 'action',
	ACTION_EXTERNAL: 'actionExternal',
	FIELD: 'field',
	RANGE_CURRENT: 'rangeCurrent',
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
	TYPE_FIELD: 'typeField',
	TYPE_CUSTOM: 'typeCustom',
	NAME: 'name',
	ERROR: 'error',
	GLOBAL_MSG: 'globalMsg',
	HAS_ERROR: 'hasError',
	HAS_CHANGED: 'hasChanged',
	LOADED: 'loaded',
	LOADER: 'loader',
	ELEMENT: 'element',
	HEADING_SUCCESS: 'headingSuccess',
	HEADING_ERROR: 'headingError',
	HIDE_ON_SUCCESS: 'hideOnSuccess',
	IS_SINGLE_SUBMIT: 'isSingleSubmit',
	SAVE_AS_JSON: 'saveAsJson',
	IS_ADMIN: 'isAdmin',
	IS_USED: 'isUsed',
	IS_USED_PREFILL: 'isUsedPrefill',
	IS_USED_PREFILL_URL: 'isUsedPrefillUrl',
	NONCE: 'nonce',

	// Conditional tags
	CONDITIONAL_TAGS: 'conditionalTags',
	CONDITIONAL_TAGS_INNER: 'conditionalTagsInner',
	TAGS: 'tags',
	TAGS_REF: 'reference',
	TAGS_DEFAULTS: 'defaults',
	TAGS_EVENTS: 'events',
	CONDITIONAL_TAGS_FORM: 'conditionalTagsForm',
	CONDITIONAL_TAGS_EVENTS: 'conditionalTagsEvents',
	CONDITIONAL_TAGS_STATE_FORM_HIDE: 'conditionalTagsStateFormHide',
	CONDITIONAL_TAGS_STATE_FORM_SHOW: 'conditionalTagsStateFormShow',
	CONDITIONAL_TAGS_STATE_CT: 'conditionalTagsStateCt',
	CONDITIONAL_TAGS_INNER_EVENTS: 'conditionalTagsInnerEvents',

	CONFIG: 'config',
	CONFIG_SELECT_USE_SEARCH: 'useSearch',
	CONFIG_SELECT_USE_MULTIPLE: 'useMultiple',
	CONFIG_PHONE_DISABLE_PICKER: 'disablePhoneCountryPicker',
	CONFIG_PHONE_USE_PHONE_SYNC: 'usePhoneSync',
	CONFIG_SUCCESS_REDIRECT: 'successRedirect',
	CONFIG_SUCCESS_REDIRECT_VARIATION: 'successRedirectVariation',
	CONFIG_SUCCESS_REDIRECT_DOWNLOADS: 'successRedirectDownloads',
	CONFIG_USE_SINGLE_SUBMIT: 'useSingleSubmit',

	SETTINGS: 'settings',
	SETTINGS_DISABLE_SCROLL_TO_GLOBAL_MSG_ON_SUCCESS: 'disableScrollToGlobalMsgOnSuccess',
	SETTINGS_DISABLE_SCROLL_TO_FIELD_ON_ERROR: 'disableScrollToFieldOnError',
	SETTINGS_FORM_RESET_ON_SUCCESS: 'formResetOnSuccess',
	SETTINGS_FORM_DISABLE_NATIVE_REDIRECT_ON_SUCCESS: 'formDisableNativeRedirectOnSuccess',
	SETTINGS_REDIRECTION_TIMEOUT: 'redirectionTimeout',
	SETTINGS_HIDE_GLOBAL_MESSAGE_TIMEOUT: 'hideGlobalMessageTimeout',
	SETTINGS_FILE_REMOVE_LABEL: 'fileRemoveLabel',
	SETTINGS_FORM_DISABLE_AUTO_INIT: 'formDisableAutoInit',
	SETTINGS_FORM_SERVER_ERROR_MSG: 'formServerErrorMsg',
	SETTINGS_FORM_CAPTCHA_ERROR_MSG: 'formCaptchaErrorMsg',
	SETTINGS_FORM_MISCONFIGURED_MSG: 'formMisconfigured',

	CAPTCHA: 'captcha',
	CAPTCHA_SITE_KEY: 'site_key',
	CAPTCHA_IS_ENTERPRISE: 'isEnterprise',
	CAPTCHA_SUBMIT_ACTION: 'submitAction',
	CAPTCHA_INIT_ACTION: 'initAction',
	CAPTCHA_LOAD_ON_INIT: 'loadOnInit',
	CAPTCHA_HIDE_BADGE: 'hideBadge',

	ENRICHMENT: 'enrichment',
	ENRICHMENT_FORM_PREFILL: 'formPrefill',
	ENRICHMENT_EXPIRATION: 'expiration',
	ENRICHMENT_EXPIRATION_PREFILL: 'expirationPrefill',
	ENRICHMENT_ALLOWED: 'allowed',

	GEOLOCATION: 'geolocation',

	TRACKING: 'tracking',
	TRACKING_EVENT_NAME: 'eventName',
	TRACKING_EVENT_ADDITIONAL_DATA: 'eventAdditionalData',

	STEPS: 'steps',
	STEPS_FLOW: 'flow',
	STEPS_CURRENT: 'current',
	STEPS_ITEMS: 'items',
	STEPS_ORDER: 'order',
	STEPS_ELEMENTS: 'elements',
	STEPS_IS_MULTIFLOW: 'isMultiflow',
	STEPS_PROGRESS_BAR_COUNT: 'progressBarCount',
	STEPS_PROGRESS_BAR_COUNT_INITIAL: 'progressBarCountInitial',
	STEPS_PROGRESS_BAR: 'progressBar',
	STEPS_ELEMENTS_PROGRESS_BAR: 'elementsProgressBar',

	ROUTES: 'routes',
	ATTRIBUTES: 'attributes',
	PARAMS: 'params',
	FIELD_TYPE: 'fieldType',
	EVENTS: 'events',
	SELECTORS: 'selectors',
	SELECTORS_ADMIN: 'selectorsAdmin',
	RESPONSE_OUTPUT_KEYS: 'responseOutputKeys',
	SUCCESS_REDIRECT_URL_KEYS: 'successRedirectUrlKey',
};

/**
 * Routes enum connected to enqueu object.
 * Used as a constant to be able to be reused on block editor because we don't have this state there.
 */
export const ROUTES = esFormsLocalization?.restRoutes ?? {};

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

	// Don't set initial state if it already exists.
	if (window[prefix].state && Object.keys(window[prefix].state).length) {
		return;
	}

	window[prefix].state = {};
	window[prefix].state = {
		[StateEnum.FORMS]: [],
		[StateEnum.CAPTCHA]: {},
		[StateEnum.ENRICHMENT]: {},
		[StateEnum.GEOLOCATION]: {},
		[StateEnum.SETTINGS]: {},
		[StateEnum.EVENTS]: {},
		[StateEnum.SELECTORS]: {},
		[StateEnum.SELECTORS_ADMIN]: {},
		[StateEnum.ATTRIBUTES]: {},
		[StateEnum.RESPONSE_OUTPUT_KEYS]: {},
		[StateEnum.SUCCESS_REDIRECT_URL_KEYS]: {},
		[StateEnum.PARAMS]: {},
		[StateEnum.FIELD_TYPE]: {},
		[StateEnum.CONFIG]: {},
		[StateEnum.ROUTES]: {},
	};

	// Selectors.
	for (const [key, item] of Object.entries(utilsManifest.enums.selectors ?? {})) {
		setState([key], item, StateEnum.SELECTORS);
	}

	// Selectors Admin.
	for (const [key, item] of Object.entries(utilsManifest.enums.selectorsAdmin ?? {})) {
		setState([key], item, StateEnum.SELECTORS_ADMIN);
	}

	// Response output keys.
	for (const [key, item] of Object.entries(utilsManifest.enums.responseOutputKeys ?? {})) {
		setState([key], item, StateEnum.RESPONSE_OUTPUT_KEYS);
	}

	// Success Redirect Url keys.
	for (const [key, item] of Object.entries(utilsManifest.enums.successRedirectUrlKeys ?? {})) {
		setState([key], item, StateEnum.SUCCESS_REDIRECT_URL_KEYS);
	}

	// Attributes.
	for (const [key, item] of Object.entries(utilsManifest.enums.attrs ?? {})) {
		setState([key], item, StateEnum.ATTRIBUTES);
	}

	// Params.
	for (const [key, item] of Object.entries(utilsManifest.enums.params ?? {})) {
		setState([key], item, StateEnum.PARAMS);
	}

	// Type Int.
	for (const [key, item] of Object.entries(globalManifest.enums.events ?? {})) {
		setState([key], item, StateEnum.EVENTS);
	}

	// Type Int.
	for (const [key, item] of Object.entries(globalManifest.enums.typeInternal ?? {})) {
		setState([key], item, StateEnum.FIELD_TYPE);
	}

	// Routes.
	for (const [key, item] of Object.entries(ROUTES)) {
		setState([key], item, StateEnum.ROUTES);
	}

	// Config.
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
	setState([StateEnum.SETTINGS_FORM_SERVER_ERROR_MSG], esFormsLocalization.formServerErrorMsg ?? '', StateEnum.SETTINGS);
	setState([StateEnum.SETTINGS_FORM_CAPTCHA_ERROR_MSG], esFormsLocalization.formCaptchaErrorMsg ?? '', StateEnum.SETTINGS);
	setState([StateEnum.SETTINGS_FORM_MISCONFIGURED_MSG], esFormsLocalization.formMisconfigured ?? '', StateEnum.SETTINGS);

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

	// Geolocation.
	const geolocation = esFormsLocalization.geolocation ?? {};
	setState([StateEnum.IS_USED], Boolean(geolocation.isUsed), StateEnum.GEOLOCATION);

	// Enrichment.
	const enrichment = esFormsLocalization.enrichment ?? {};
	setState([StateEnum.IS_USED], Boolean(enrichment.isUsed), StateEnum.ENRICHMENT);
	
	if (enrichment.isUsed) {
		setState([StateEnum.IS_USED_PREFILL], Boolean(enrichment.isUsedPrefill), StateEnum.ENRICHMENT);
		setState([StateEnum.IS_USED_PREFILL_URL], Boolean(enrichment.isUsedPrefillUrl), StateEnum.ENRICHMENT);
		setState([StateEnum.ENRICHMENT_EXPIRATION], enrichment.expiration, StateEnum.ENRICHMENT);
		setState([StateEnum.ENRICHMENT_EXPIRATION_PREFILL], enrichment.expirationPrefill, StateEnum.ENRICHMENT);
		setState([StateEnum.ENRICHMENT_ALLOWED], Object.values(enrichment.allowed), StateEnum.ENRICHMENT);
		setState([StateEnum.NAME], 'es-storage', StateEnum.ENRICHMENT);
	}
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
		[StateEnum.ELEMENTS_FIELDS]: {},
		[StateEnum.FORM]: {},
	};

	// Push only form ID if it doesn't exist.
	if (window[prefix].state[StateEnum.FORMS].indexOf(formId) === -1) {
		window[prefix].state[StateEnum.FORMS].push(formId);
	}

	let formElement = '';

	if (formId === 0) {
		formElement = document.querySelector(getStateSelector('form', true));
	} else {
		formElement = document.querySelector(`${getStateSelector('form', true)}[${getStateAttribute('formId')}="${formId}"]`);
	}

	setState([StateEnum.FORM, StateEnum.POST_ID],formElement?.getAttribute(getStateAttribute('postId')), formId);
	setState([StateEnum.FORM, StateEnum.ISLOADED], false, formId);
	setState([StateEnum.FORM, StateEnum.IS_SINGLE_SUBMIT], false, formId);
	setState([StateEnum.FORM, StateEnum.ELEMENT], formElement, formId);
	setState([StateEnum.FORM, StateEnum.TYPE], formElement?.getAttribute(getStateAttribute('formType')), formId);
	setState([StateEnum.FORM, StateEnum.METHOD], formElement?.getAttribute('method'), formId);
	setState([StateEnum.FORM, StateEnum.ACTION], formElement?.getAttribute('action'), formId);
	setState([StateEnum.FORM, StateEnum.ACTION_EXTERNAL], formElement?.getAttribute(getStateAttribute('actionExternal')), formId);
	setState([StateEnum.FORM, StateEnum.TYPE_SETTINGS], formElement?.getAttribute(getStateAttribute('settingsType')), formId);
	setState([StateEnum.FORM, StateEnum.LOADER], formElement?.querySelector(getStateSelector('loader', true)), formId);
	setState([StateEnum.FORM, StateEnum.TRACKING, StateEnum.TRACKING_EVENT_NAME], formElement?.getAttribute(getStateAttribute('trackingEventName')), formId);
	setState([StateEnum.FORM, StateEnum.TRACKING, StateEnum.TRACKING_EVENT_ADDITIONAL_DATA], JSON.parse(formElement?.getAttribute(getStateAttribute('trackingAdditionalData')) ?? '{}'), formId);

	// Form settings
	setState([StateEnum.FORM, StateEnum.CONFIG, StateEnum.CONFIG_PHONE_DISABLE_PICKER], Boolean(formElement?.getAttribute(getStateAttribute('phoneDisablePicker'))), formId);
	setState([StateEnum.FORM, StateEnum.CONFIG, StateEnum.CONFIG_PHONE_USE_PHONE_SYNC], Boolean(formElement?.getAttribute(getStateAttribute('phoneSync'))), formId);
	setState([StateEnum.FORM, StateEnum.CONFIG, StateEnum.CONFIG_SUCCESS_REDIRECT], formElement?.getAttribute(getStateAttribute('successRedirect')), formId);
	setState([StateEnum.FORM, StateEnum.CONFIG, StateEnum.CONFIG_SUCCESS_REDIRECT_VARIATION], formElement?.getAttribute(getStateAttribute('successRedirectVariation')), formId);
	setState([StateEnum.FORM, StateEnum.CONFIG, StateEnum.CONFIG_SUCCESS_REDIRECT_DOWNLOADS], JSON.parse(formElement?.getAttribute(getStateAttribute('successRedirectDownloads')) ?? '{}'), formId);
	setState([StateEnum.FORM, StateEnum.CONFIG, StateEnum.CONFIG_USE_SINGLE_SUBMIT], Boolean(formElement?.getAttribute(getStateAttribute('singleSubmit'))), formId);

	const globalMsg = formElement?.querySelector(getStateSelector('globalMsg', true));
	setState([StateEnum.FORM, StateEnum.GLOBAL_MSG, StateEnum.HIDE_ON_SUCCESS], Boolean(formElement?.getAttribute(getStateAttribute('globalMsgHideOnSuccess'))), formId);
	setState([StateEnum.FORM, StateEnum.GLOBAL_MSG, StateEnum.ELEMENT], globalMsg, formId);
	setState([StateEnum.FORM, StateEnum.GLOBAL_MSG, StateEnum.HEADING_SUCCESS], globalMsg?.getAttribute(getStateAttribute('globalMsgHeadingSuccess')), formId);
	setState([StateEnum.FORM, StateEnum.GLOBAL_MSG, StateEnum.HEADING_ERROR], globalMsg?.getAttribute(getStateAttribute('globalMsgHeadingError')), formId);

	// Conditional tags
	setState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_EVENTS], {}, formId);
	setState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_INNER_EVENTS], {}, formId);
	setState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_FORM], JSON.parse(formElement?.getAttribute(getStateAttribute('conditionalTags')) ?? '{}'), formId);
	setState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_STATE_FORM_HIDE], {}, formId);
	setState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_STATE_FORM_SHOW], {}, formId);
	setState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_STATE_CT], {}, formId);

	// Steps.
	setSteps(formElement, formId);

	const formFields = formElement?.querySelectorAll('input, select, textarea') ?? [];

	// Loop all fields.
	[...formFields].forEach((item) => {
		const {
			value,
			name,
			type, // this is used as a native type not field type.
			disabled,
		} = item;

		if (name === 'search_terms') {
			return;
		}

		const field = formElement.querySelector(`${getStateSelector('field', true)}[${getStateAttribute('fieldName')}="${name}"]`);
		const fieldType = field?.getAttribute(getStateAttribute('fieldType'));

		// Make changes depending on the field type.
		switch (type) {
			case 'radio':
			case 'checkbox':
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

				break;
			case 'select-one':
			case 'select-multiple':
				// Combined fields like phone can have field null.
				const isMultiple = Boolean(item.getAttribute(getStateAttribute('selectIsMultiple'))); // eslint-disable-line no-case-declarations

				if (item.options.length && (fieldType === 'phone' || fieldType === 'country')) {
					let customData = item?.options[item?.options?.selectedIndex]?.getAttribute(getStateAttribute('selectCustomProperties'));

					if (typeof customData === 'string') {
						customData = JSON.parse(customData);
					}

					setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY, 'code'], customData[getStateAttribute('selectCountryCode')], formId);
					setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY, 'label'], customData[getStateAttribute('selectCountryLabel')], formId);
					setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY, 'number'], customData[getStateAttribute('selectCountryNumber')], formId);
				}

				if (fieldType !== 'phone') {
					setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], value, formId);
					setState([StateEnum.ELEMENTS, name, StateEnum.INITIAL], value, formId);

					[...item.children].forEach((option) => {
						const value = option?.value;
						if (value) {
							setState([StateEnum.ELEMENTS, name, StateEnum.ITEMS, name, StateEnum.NAME], value, formId);
						}
					});

					if (isMultiple) {
						const multipleValues = [...item.options].filter((option) => option?.selected).map((option) => option?.value);

						setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], multipleValues, formId);
						setState([StateEnum.ELEMENTS, name, StateEnum.INITIAL], multipleValues, formId);
					}
				}

				setState([StateEnum.ELEMENTS, name, StateEnum.IS_DISABLED], disabled, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.INPUT], item, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.CONFIG, StateEnum.CONFIG_SELECT_USE_SEARCH], Boolean(item.getAttribute(getStateAttribute('selectAllowSearch'))), formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.CONFIG, StateEnum.CONFIG_SELECT_USE_MULTIPLE], isMultiple, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.TRACKING], field.getAttribute(getStateAttribute('tracking')), formId);
				break;
			case 'tel':
				setState([StateEnum.ELEMENTS, name, StateEnum.INITIAL], value, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], value, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.INPUT], item, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.IS_DISABLED], disabled, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.INPUT_SELECT], field.querySelector('select'), formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.TRACKING], field.getAttribute(getStateAttribute('tracking')), formId);

				if (!value) {
					setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COMBINED], '', formId);
				} else {
					const countryValue = getState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY], formId)?.number ?? '';
					setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COMBINED], `${countryValue}${value}`, formId);
				}
				break;
			case 'date':
			case 'datetime-local':
				setState([StateEnum.ELEMENTS, name, StateEnum.INITIAL], value, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], value, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.INPUT], item, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.IS_DISABLED], disabled, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.TRACKING], field.getAttribute(getStateAttribute('tracking')), formId);
				break;
			case 'range':
				setState([StateEnum.ELEMENTS, name, StateEnum.INITIAL], value, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], value, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.INPUT], item, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.IS_DISABLED], disabled, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.RANGE_CURRENT], field.querySelector(getStateSelector('inputRangeCurrent', true)), formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.TRACKING], field.getAttribute(getStateAttribute('tracking')), formId);
				break;
			default:
				setState([StateEnum.ELEMENTS, name, StateEnum.INITIAL], value, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], value, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.INPUT], item, formId);
				setState([StateEnum.ELEMENTS, name, StateEnum.IS_DISABLED], disabled, formId);

				if (fieldType === 'rating') {
					setState([StateEnum.ELEMENTS, name, StateEnum.CUSTOM], field.querySelector(getStateSelector('rating', true)), formId);
				}

				if (field.getAttribute(getStateAttribute('fieldPreventSubmit'))) {
					setState([StateEnum.ELEMENTS, name, StateEnum.IS_DISABLED], Boolean(field.getAttribute(getStateAttribute('fieldPreventSubmit'))), formId);
				}
				setState([StateEnum.ELEMENTS, name, StateEnum.TRACKING], field.getAttribute(getStateAttribute('tracking')), formId);
				break;
		}

		setState([StateEnum.ELEMENTS, name, StateEnum.TYPE_FIELD], fieldType, formId);
		setState([StateEnum.ELEMENTS, name, StateEnum.HAS_ERROR], false, formId);
		setState([StateEnum.ELEMENTS, name, StateEnum.HAS_CHANGED], false, formId);
		setState([StateEnum.ELEMENTS, name, StateEnum.LOADED], false, formId);
		setState([StateEnum.ELEMENTS, name, StateEnum.NAME], name, formId);
		setState([StateEnum.ELEMENTS, name, StateEnum.FIELD], field, formId);
		setState([StateEnum.ELEMENTS, name, StateEnum.ERROR], field?.querySelector(getStateSelector('error', true)), formId);
		setState([StateEnum.ELEMENTS, name, StateEnum.IS_SINGLE_SUBMIT], item?.classList?.contains(getStateSelector('submitSingle', true).substring(1)), formId);
		setState([StateEnum.ELEMENTS, name, StateEnum.TYPE_CUSTOM], field?.getAttribute(getStateAttribute('fieldTypeCustom')), formId);
		setState([StateEnum.ELEMENTS, name, StateEnum.SAVE_AS_JSON], Boolean(item.getAttribute(getStateAttribute('saveAsJson'))), formId);
	});

	// Loop all fields for conditional tags later because we need to have all state set.
	for (const item of Object.values(formFields)) {
		const {
			value,
			name,
			type,
		} = item;

		if (name === 'search_terms') {
			continue;
		}

		const field = formElement.querySelector(`${getStateSelector('field', true)}[${getStateAttribute('fieldName')}="${name}"]`);

		if (type ==='radio' || type ==='checkbox') {
			setStateConditionalTagsItems(item.parentNode.parentNode.getAttribute(getStateAttribute('conditionalTags')), name, value, formId);
		}

		// Conditional tags.
		if (field) {
			setStateConditionalTags(field, name, false, formId);
		}
	}

	const customFields = formElement?.querySelectorAll(getStateSelector('fieldNoFormsBlock', true)) ?? [];

	// Loop all fields for conditional tags later because we need to have all state set beforehand.
	[...customFields].forEach((field) => {
			
		const name = field.getAttribute(getStateAttribute('fieldName'));

		// Conditional tags.
		if (name) {
			setState([StateEnum.ELEMENTS_FIELDS, name, StateEnum.NAME], name, formId);
			setState([StateEnum.ELEMENTS_FIELDS, name, StateEnum.FIELD], field, formId);

			setStateConditionalTags(field, name, true, formId);
		}
	});
}

/**
 * Set state for steps.
 *
 * @param {object} formElement Form element.
 * @param {string} formId Form ID.
 *
 * * @returns {void}
 */
export function setSteps(formElement, formId) {
	const steps = formElement?.querySelectorAll(getStateSelector('step', true));
	setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.IS_USED], false, formId);

	if (steps?.length) {
		setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.IS_USED], true, formId);
		setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_FLOW], [], formId);
		setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_CURRENT], '', formId);
		setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_ITEMS], {}, formId);
		setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_ORDER], [], formId);
		setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_ELEMENTS], {}, formId);
		setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_IS_MULTIFLOW], false, formId);

		const stepsOrder = [];
		Object.values(steps).forEach((item, index) => {
			const stepFields = item?.querySelectorAll(getStateSelector('field', true));
			const stepId = String(item.getAttribute(getStateAttribute('stepId')));
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

			if (stepFields) {
				stepsOrder.push(stepId);
			}
		});
		setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_ORDER], stepsOrder, formId);

		const stepsProgressBarMultiflow = formElement.querySelector(getStateSelector('stepProgressBarMultiflow', true));

		if (stepsProgressBarMultiflow) {
			setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_PROGRESS_BAR], stepsProgressBarMultiflow, formId);
			setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_PROGRESS_BAR_COUNT], stepsProgressBarMultiflow?.children?.length, formId);
			setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_PROGRESS_BAR_COUNT_INITIAL], stepsProgressBarMultiflow?.children?.length, formId);
			setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_IS_MULTIFLOW], true, formId);
		}

		const stepsProgressBar = formElement.querySelector(getStateSelector('stepProgressBar', true));

		if (stepsProgressBar) {
			setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_PROGRESS_BAR], stepsProgressBar, formId);
			setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_ELEMENTS_PROGRESS_BAR], {}, formId);

			[...stepsProgressBar.children].forEach((item) => {
				const stepId = item.getAttribute(getStateAttribute('stepId'));
				setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_ELEMENTS_PROGRESS_BAR, stepId], item, formId);
			});
		}
	}
}

/**
 * Set state values when the field changes - Input.
 *
 * @param {object} item Item/field to check.
 * @param {string} formId Form ID.
 *
 * @returns {void}
 */
export function setStateValuesInput(item, formId) {
	const {
		name,
		value,
	} = item;

	// Datepicker and dropzone are set using native lib events.
	setState([StateEnum.ELEMENTS, name, StateEnum.HAS_CHANGED], true, formId);
	setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], value, formId);
}

/**
 * Set state values when the field changes - Radio.
 *
 * @param {object} item Item/field to check.
 * @param {string} formId Form ID.
 *
 * @returns {void}
 */
export function setStateValuesRadio(item, formId) {
	const {
		name,
		value,
		checked,
	} = item;

	setState([StateEnum.ELEMENTS, name, StateEnum.HAS_CHANGED], true, formId);
	setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], checked ? value : '', formId);
}

/**
 * Set state values when the field changes - Checkbox.
 *
 * @param {object} item Item/field to check.
 * @param {string} formId Form ID.
 *
 * @returns {void}
 */
export function setStateValuesCheckbox(item, formId) {
	const {
		name,
		value,
		checked,
	} = item;

	setState([StateEnum.ELEMENTS, name, StateEnum.HAS_CHANGED], true, formId);
	setState([StateEnum.ELEMENTS, name, StateEnum.VALUE, value], checked ? value : '', formId);
}

/**
 * Set state values when the field changes - Phone input.
 *
 * @param {object} item Item/field to check.
 * @param {string} formId Form ID.
 *
 * @returns {void}
 */
export function setStateValuesPhoneInput(item, formId) {
	const {
		name,
		value,
	} = item;

	setState([StateEnum.ELEMENTS, name, StateEnum.HAS_CHANGED], true, formId);
	setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], value, formId);
	setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COMBINED], '', formId);

	if (value) {
		const countryValue = getState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY], formId)?.number ?? '';
		setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COMBINED], `${countryValue}${value}`, formId);
	}
}

/**
 * Set state values when the field changes - Select.
 *
 * @param {object} item Item/field to check.
 * @param {string} formId Form ID.
 *
 * @returns {void}
 */
export function setStateValuesSelect(item, formId) {
	const {
		name,
		value,
	} = item;

	setState([StateEnum.ELEMENTS, name, StateEnum.HAS_CHANGED], true, formId);
	setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], value, formId);

	if (getState([StateEnum.ELEMENTS, name, StateEnum.CONFIG, StateEnum.CONFIG_SELECT_USE_MULTIPLE], formId)) {
		const multipleValues = [...item.options].filter((option) => option?.selected).map((option) => option?.value);
		setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], multipleValues, formId);
	}
}

/**
 * Set state values when the field changes - Country.
 *
 * @param {object} item Item/field to check.
 * @param {string} formId Form ID.
 *
 * @returns {void}
 */
export function setStateValuesCountry(item, formId) {
	const {
		name,
		value,
	} = item;

	setState([StateEnum.ELEMENTS, name, StateEnum.HAS_CHANGED], true, formId);

	let customData = item?.options[item?.options?.selectedIndex]?.getAttribute(getStateAttribute('selectCustomProperties'));

	if (typeof customData === 'string') {
		customData = JSON.parse(customData);
	}

	setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY, 'code'], customData?.[getStateAttribute('selectCountryCode')], formId);
	setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY, 'label'], customData?.[getStateAttribute('selectCountryLabel')], formId);
	setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY, 'number'], customData?.[getStateAttribute('selectCountryNumber')], formId);
	setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], value, formId);

	if (getState([StateEnum.ELEMENTS, name, StateEnum.CONFIG, StateEnum.CONFIG_SELECT_USE_MULTIPLE], formId)) {
		const multipleValues = [...item.options].filter((option) => option?.selected).map((option) => option?.value);
		setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], multipleValues, formId);
	}
}

/**
 * Set state values when the field changes - Phone select.
 *
 * @param {object} item Item/field to check.
 * @param {string} formId Form ID.
 *
 * @returns {void}
 */
export function setStateValuesPhoneSelect(item, formId) {
	const {
		name,
	} = item;

	setState([StateEnum.ELEMENTS, name, StateEnum.HAS_CHANGED], true, formId);

	let customData = item?.options[item?.options?.selectedIndex]?.getAttribute(getStateAttribute('selectCustomProperties'));

	if (typeof customData === 'string') {
		customData = JSON.parse(customData);
	}

	setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY, 'code'], customData?.[getStateAttribute('selectCountryCode')], formId);
	setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY, 'label'], customData?.[getStateAttribute('selectCountryLabel')], formId);
	setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY, 'number'], customData?.[getStateAttribute('selectCountryNumber')], formId);

	setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COMBINED], '', formId);

	if (getState([StateEnum.ELEMENTS, name, StateEnum.VALUE], formId)) {
		const countryValue = getState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY], formId)?.number ?? '';
		setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COMBINED], `${countryValue}${getState([StateEnum.ELEMENTS, name, StateEnum.VALUE], formId)}`, formId);
	}
}


/**
 * Set state conditional tags on one field.
 *
 * @param {object} field Field object.
 * @param {string} name Field name.
 * @param {boolean} isNoneFormBlock Is none form block.
 * @param {string} formId Form ID.
 *
 * @returns {void}
 */
export function setStateConditionalTags(field, name, isNoneFormBlock = false, formId) {
	const conditionalTags = field.getAttribute(getStateAttribute('conditionalTags'));

	const parentStorage = isNoneFormBlock ? StateEnum.ELEMENTS_FIELDS : StateEnum.ELEMENTS;

	setState([parentStorage, name, StateEnum.CONDITIONAL_TAGS, StateEnum.TAGS_DEFAULTS], CONDITIONAL_TAGS_ACTIONS.SHOW, formId);
	setState([parentStorage, name, StateEnum.CONDITIONAL_TAGS, StateEnum.TAGS], [], formId);
	setState([parentStorage, name, StateEnum.CONDITIONAL_TAGS, StateEnum.TAGS_REF], [], formId);

	if (!conditionalTags) {
		return;
	}

	const tag = JSON.parse(conditionalTags)?.[0];

	// Check if fields exist and remove conditional tags if not.
	// This can happend if the user deletes a field and the conditional tag is still there on other field.
	const output = tag[1].map((item) => item.filter((inner) => {
		const itemName = inner[0] ?? '';
		return itemName !== '' && getState([StateEnum.ELEMENTS, itemName], formId);
	})).filter(outputInner => outputInner.length > 0);

	if (!output.length) {
		return;
	}

	setState([parentStorage, name, StateEnum.CONDITIONAL_TAGS, StateEnum.TAGS_DEFAULTS], tag[0], formId);
	setState([parentStorage, name, StateEnum.CONDITIONAL_TAGS, StateEnum.TAGS], output, formId);

	setStateConditionalTagsInner(name, formId, output, isNoneFormBlock);
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

	// Check if fields exist and remove conditional tags if not.
	// This can happend if the user deletes a field and the conditional tag is still there on other field.
	const output = tag[1].map((item) => item.filter((inner) => {
		const itemName = inner[0] ?? '';
		return itemName !== '' && getState([StateEnum.ELEMENTS, itemName], formId);
	})).filter(outputInner => outputInner.length > 0);

	if (!output.length) {
		return;
	}

	setState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS_INNER, innerName, StateEnum.TAGS_DEFAULTS], tag[0], formId);
	setState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS_INNER, innerName, StateEnum.TAGS], output, formId);

	setStateConditionalTagsInner(name, formId, output, false, innerName);
}

/**
 * Set state conditional tags inner item on one field.
 *
 * @param {string} name Field name.
 * @param {string} formId Form ID.
 * @param {array} tags Tags array.
 * @param {boolean} isNoneFormBlock Is none form block.
 * @param {string} innerName Conditional tag inner name.
 *
 * @returns {void}
 */
export function setStateConditionalTagsInner(name, formId, tags, isNoneFormBlock = false, innerName = '') {
	const refOutput = [];

	const parentStorage = isNoneFormBlock ? StateEnum.ELEMENTS_FIELDS : StateEnum.ELEMENTS;

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
		setState([parentStorage, name, StateEnum.CONDITIONAL_TAGS_INNER, innerName, StateEnum.TAGS_REF], refOutput, formId);
	} else {
		setState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_EVENTS], eventsOutput, formId);
		setState([parentStorage, name, StateEnum.CONDITIONAL_TAGS, StateEnum.TAGS_REF], refOutput, formId);
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
 * Remove form state helper.
 *
 * @param {string} formId Form Id.
 *
 * @returns {void}
 */
export function removeStateForm(formId) {
	const { state } = window[prefix];

	if (state && `form_${formId}` in state) {
		delete state[`form_${formId}`];

		window[prefix].state = {
			...state,
		};
	}

	const index = state[StateEnum.FORMS].indexOf(formId);
	const forms = state[StateEnum.FORMS];
	if (index > -1) {
		forms.splice(index, 1);

		window[prefix].state[StateEnum.FORMS] = [
			...forms,
		];
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
export function getStateEvent(name) {
	return getStateTop(StateEnum.EVENTS)[name];
}

/**
 * Get state route name.
 *
 * @param {string} name Name key to get.
 *
 * @returns {string}
 */
export function getStateRoute(name) {
	return getStateTop(StateEnum.ROUTES)[name];
}

/**
 * Get state selector.
 * 
 * @param {string} name Name key to get.
 *
 * @returns {string}
 */
export function getStateResponseOutputKey(name) {
	return getStateTop(StateEnum.RESPONSE_OUTPUT_KEYS)[name];
}

/**
 * Get state selector.
 * 
 * @param {string} name Name key to get.
 * @param {boolean} usePrefix Use prefix.
 *
 * @returns {string}
 */
export function getStateSelector(name, usePrefix = false) {
	return usePrefix ? `.${getStateTop(StateEnum.SELECTORS)[name]}` : getStateTop(StateEnum.SELECTORS)[name];
}

/**
 * Get state selector admin.
 * 
 * @param {string} name Name key to get.
 * @param {boolean} usePrefix Use prefix.
 *
 * @returns {string}
 */
export function getStateSelectorAdmin(name, usePrefix = false) {
	return usePrefix ? `.${getStateTop(StateEnum.SELECTORS_ADMIN)[name]}` : getStateTop(StateEnum.SELECTORS_ADMIN)[name];
}

/**
 * Get state attribute.
 *
 * @returns {string}
 */
export function getStateAttribute(name) {
	return getStateTop(StateEnum.ATTRIBUTES)[name];
}

/**
 * Get state field type.
 *
 * @returns {string}
 */
export function getStateFieldType(name) {
	return getStateTop(StateEnum.FIELD_TYPE)[name];
}

/**
 * Get state Params.
 *
 * @returns {string}
 */
export function getStateParam(name) {
	return getStateTop(StateEnum.PARAMS)[name];
}

/**
 * Get state Success Redirect Url Key.
 *
 * @returns {string}
 */
export function getStateSuccessRedirectUrlKey(name) {
	return getStateTop(StateEnum.SUCCESS_REDIRECT_URL_KEYS)[name];
}

/**
 * Get rest api url link.
 *
 * @param {string} value Value to get
 * @param {bool} isPartial Is relative or absolute url.
 *
 * @returns {string}
 */
export function getRestUrl(value, isPartial = false) {
	const prefix = isPartial ? ROUTES?.prefixProject : ROUTES?.prefix;

	const url = prefix.replace(/\/$/, ''); // Remove trailing slash.
	const sufix = ROUTES?.[value].replace(/^\/+/, ''); // Remove leading slash.

	return `${url}/${sufix}`;
}

/**
 * Get rest api url link with integration prefix.
 *
 * @param {string} type Integration type.
 * @param {string} value Value to get
 * @param {bool} isPartial Is relative or absolute url.
 * @param {bool} checkRef Check if value is reference.
 *
 * @returns {string}
 */
export function getRestUrlByType(type, value, isPartial = false, checkRef = false) {
	const prefix = isPartial ? ROUTES?.prefixProject : ROUTES?.prefix;

	const newVal = checkRef ? ROUTES?.[value] : value;

	const url = prefix.replace(/\/$/, ''); // Remove trailing slash.
	const sufix = newVal.replace(/^\/+/, ''); // Remove leading slash.
	const typePrefix = ROUTES?.[type].replace(/^\/|\/$/g, ''); // Remove leading and trailing slash.

	return `${url}/${typePrefix}/${sufix}`;
}

////////////////////////////////////////////////////////////////
// Block editor only.
////////////////////////////////////////////////////////////////

/**
 * Get utils icons, used in the block editor only.
 *
 * @returns {string}
 */
export function getUtilsIcons(name) {
	return utilsManifest?.icons?.[name];
}
