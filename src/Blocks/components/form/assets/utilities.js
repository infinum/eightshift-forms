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

export const utils = (
	formSelector,
	formIsAdmin,
) => {

	const submitSingleSelector =  `${formSelector}-single-submit`;
	const errorSelector =  `${formSelector}-error`;
	const loaderSelector =  `${formSelector}-loader`;
	const globalMsgSelector =  `${formSelector}-global-msg`;
	const groupSelector =  `${formSelector}-group`;
	const groupInnerSelector =  `${formSelector}-group-inner`;
	const customSelector =  `${formSelector}-custom`;
	const fieldSelector =  `${formSelector}-field`;
	const inputSelector =  `${fieldSelector} input`;
	const textareaSelector =  `${fieldSelector} textarea`;
	const selectSelector =  `${fieldSelector} select`;
	const fileSelector =  `${fieldSelector} input[type='file']`;
	const conditionalTagsSelector =  `${fieldSelector} input[id='conditional-tags']`;

	return {
		submitSingleSelector,
		errorSelector,
		loaderSelector,
		globalMsgSelector,
		groupSelector,
		groupInnerSelector,
		customSelector,
		fieldSelector,
		inputSelector,
		textareaSelector,
		selectSelector,
		fileSelector,
		conditionalTagsSelector,

		// Reset for in general.
		reset: (element) => {
			const items = element.querySelectorAll(errorSelector);
			[...items].forEach((item) => {
				item.innerHTML = '';
			});
		
			// Reset all error classes on fields.
			element.querySelectorAll(`.${FORM_SELECTORS.CLASS_HAS_ERROR}`).forEach((element) => element.classList.remove(FORM_SELECTORS.CLASS_HAS_ERROR));

			this.unsetGlobalMsg(element);
		},

		// Unset global message.
		unsetGlobalMsg: (element) => {
			console.log(element);
			const messageContainer = element.querySelector(globalMsgSelector);
		
			if (!messageContainer) {
				return;
			}
		
			messageContainer.classList.remove(FORM_SELECTORS.CLASS_ACTIVE);
			messageContainer.dataset.status = '';
			messageContainer.innerHTML = '';
		},

		// Determine if field is custom type or normal.
		isCustom: (element) => {
			return element.closest(fieldSelector).classList.contains(customSelector.substring(1)) && !formIsAdmin;
		},
	}
};

/**
 * List all utility methods exported in the window object.
 */
export const windowUtilities = {
	utilIsCustom: (element, fieldSelector, customSelector, isAdmin) => {
		utilIsCustom(element, fieldSelector, customSelector, isAdmin);
	},
};
