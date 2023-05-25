/* global esFormsLocalization */
import manifest from './../manifest.json';
import selectManifest from './../../select/manifest.json';

const {
	componentJsClass,
} = manifest

export class Data {
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
		this.FORM_PARAMS = esFormsLocalization.customFormParams ?? {};

		// Custom data attributes.
		this.DATA_ATTRIBUTES = esFormsLocalization.customFormDataAttributes ?? {};

		// Settings options from the backend.
		this.SETTINGS = {
			FORM_DISABLE_SCROLL_TO_FIELD_ON_ERROR: Boolean(esFormsLocalization.formDisableScrollToFieldOnError),
			FORM_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS: Boolean(esFormsLocalization.formDisableScrollToGlobalMessageOnSuccess),
			FORM_DISABLE_NATIVE_REDIRECT_ON_SUCCESS: Boolean(esFormsLocalization.formDisableNativeRedirectOnSuccess),
			FORM_RESET_ON_SUCCESS: Boolean(esFormsLocalization.formResetOnSuccess),
			REDIRECTION_TIMEOUT: esFormsLocalization.redirectionTimeout ?? 600,
			HIDE_GLOBAL_MESSAGE_TIMEOUT: esFormsLocalization.hideGlobalMessageTimeout ?? 6000,
			HIDE_LOADING_STATE_TIMEOUT: esFormsLocalization.hideLoadingStateTimeout ?? 600,
			FILE_CUSTOM_REMOVE_LABEL: esFormsLocalization.fileCustomRemoveLabel ?? '',
			FORM_SERVER_ERROR_MSG: esFormsLocalization.formServerErrorMsg ?? '',
			CAPTCHA: esFormsLocalization.captcha ?? [],
			ENRICHMENT_CONFIG: esFormsLocalization.enrichmentConfig ?? '[]',
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
	}
}

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
 * or - activate condition if at least one condition in rules array is met.
 * and - activate condition if all conditions in rules array are met.
 */
export const CONDITIONAL_TAGS_LOGIC = {
	OR: 'or',
	AND: 'and',
};
