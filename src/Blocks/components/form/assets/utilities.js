const ePrefix = 'esForms';

/**
 * All custom events.
 */
export const FORM_EVENTS = {
	BEFORE_FORM_SUBMIT: `${ePrefix}BeforeFormSubmit`,
	AFTER_FORM_SUBMIT: `${ePrefix}AfterFormSubmit`,
	AFTER_FORM_SUBMIT_SUCCESS_REDIRECT: `${ePrefix}AfterFormSubmitSuccessRedirect`,
	AFTER_FORM_SUBMIT_SUCCESS: `${ePrefix}AfterFormSubmitSuccess`,
	AFTER_FORM_SUBMIT_RESET: `${ePrefix}AfterFormSubmitReset`,
	AFTER_FORM_SUBMIT_ERROR: `${ePrefix}AfterFormSubmitError`,
	AFTER_FORM_SUBMIT_ERROR_FATAL: `${ePrefix}AfterFormSubmitErrorFatal`,
	AFTER_FORM_SUBMIT_ERROR_VALIDATION: `${ePrefix}AfterFormSubmitErrorValidation`,
	AFTER_FORM_SUBMIT_END: `${ePrefix}AfterFormSubmitEnd`,
	AFTER_FORM_EVENTS_CLEAR: `${ePrefix}AfterFormEventsClear`,
	BEFORE_GTM_DATA_PUSH: `${ePrefix}BeforeGtmDataPush`,
	FORMS_JS_LOADED: `${ePrefix}JsLoaded`,
};

/**
 * All form custom state selectors.
 */
export const FORM_SELECTORS = {
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
export const CONDITIONAL_TAGS_CONSTANTS = {
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

/**
 * Determine if field is custom type or normal.
 *
 * @param {object} element Selected element like file, textarea, etc...
 * @param {string} fieldSelector Fields selector string.
 * @param {string} customSelector Custom selector string.
 * @param {boolean} isAdmin Check if this is used in admin or frontend.
 *
 * @returns {boolean}
 */
export const utilIsCustom = (element, fieldSelector, customSelector, isAdmin) => {
	return element.closest(fieldSelector).classList.contains(customSelector) && !isAdmin;
};

/**
 * List all utility methods exported in the window object.
 */
export const windowUtilities = {
	utilIsCustom: (element, fieldSelector, customSelector, isAdmin) => {
		utilIsCustom(element, fieldSelector, customSelector, isAdmin);
	}
};
