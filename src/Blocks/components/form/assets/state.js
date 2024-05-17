import {
	StateEnum,
	getState,
	prefix,
	setState,
	setStateWindow,
	getStateTop,
	getStateAttribute,
	getStateSelector,
	getStateFieldType,
	getStateParam,
	getStateEvent,
	getStateSelectorAdmin,
	getStateRoute,
	getRestUrl,
	getRestUrlByType,
	getStateResponseOutputKey,
	getStateSuccessRedirectUrlKey,
} from './state-init';

export class State {
	constructor() {
		// Set all public methods.
		this.publicMethods();
	}
	////////////////////////////////////////////////////////////////
	// Helpers getters.
	////////////////////////////////////////////////////////////////

	setState = (keyArray, value, formId) => {
		setState(keyArray, value, formId);
	};
	getState = (keys, formId) => {
		getState(keys, formId);
	};

	////////////////////////////////////////////////////////////////
	// Config getters.
	////////////////////////////////////////////////////////////////

	getStateConfigIsAdmin = () => {
		return getState([StateEnum.IS_ADMIN], StateEnum.CONFIG);
	};
	getStateConfigNonce = () => {
		return getState([StateEnum.NONCE], StateEnum.CONFIG);
	};

	////////////////////////////////////////////////////////////////
	// Form getters.
	////////////////////////////////////////////////////////////////

	getStateForms = () => {
		return getStateTop(StateEnum.FORMS);
	};
	getStateForm = (formId) => {
		return getState([StateEnum.FORM], formId);
	};
	getStateFormElement = (formId) => {
		return getState([StateEnum.FORM, StateEnum.ELEMENT], formId);
	};
	getStateFormPostId = (formId) => {
		return getState([StateEnum.FORM, StateEnum.POST_ID], formId);
	};
	getStateFormIsAdminSingleSubmit = (formId) => {
		return getState([StateEnum.FORM, StateEnum.IS_ADMIN_SINGLE_SUBMIT], formId);
	};
	getStateFormType = (formId) => {
		return getState([StateEnum.FORM, StateEnum.TYPE], formId);
	};
	getStateFormMethod = (formId) => {
		return getState([StateEnum.FORM, StateEnum.METHOD], formId);
	};
	getStateFormAction = (formId) => {
		return getState([StateEnum.FORM, StateEnum.ACTION], formId);
	};
	getStateFormActionExternal = (formId) => {
		return getState([StateEnum.FORM, StateEnum.ACTION_EXTERNAL], formId);
	};
	getStateFormTypeSettings = (formId) => {
		return getState([StateEnum.FORM, StateEnum.TYPE_SETTINGS], formId);
	};
	getStateFormLoader = (formId) => {
		return getState([StateEnum.FORM, StateEnum.LOADER], formId);
	};

	setStateFormIsLoaded = (value, formId) => {
		setState([StateEnum.FORM, StateEnum.ISLOADED], value, formId);
	};
	setStateFormIsAdminSingleSubmit = (value, formId) => {
		setState([StateEnum.FORM, StateEnum.IS_ADMIN_SINGLE_SUBMIT], value, formId);
	};

	////////////////////////////////////////////////////////////////
	// Tracking getters.
	////////////////////////////////////////////////////////////////

	getStateFormTrackingEventName = (formId) => {
		return getState([StateEnum.FORM, StateEnum.TRACKING, StateEnum.TRACKING_EVENT_NAME], formId);
	};
	getStateFormTrackingEventAdditionalData = (formId) => {
		return getState([StateEnum.FORM, StateEnum.TRACKING, StateEnum.TRACKING_EVENT_ADDITIONAL_DATA], formId);
	};

	////////////////////////////////////////////////////////////////
	// Conditional tags getters.
	////////////////////////////////////////////////////////////////

	getStateFormConditionalTagsEvents = (formId) => {
		return getState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_EVENTS], formId);
	};
	getStateFormConditionalTagsStateHideForms = (formId) => {
		return getState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_STATE_FORM_HIDE], formId);
	};
	getStateFormConditionalTagsStateCt = (formId) => {
		return getState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_STATE_CT], formId);
	};
	getStateFormConditionalTagsInnerEvents = (formId) => {
		return getState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_INNER_EVENTS], formId);
	};
	getStateFormConditionalTagsForm = (formId) => {
		return getState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_FORM], formId);
	};

	////////////////////////////////////////////////////////////////
	// Global msg getters.
	////////////////////////////////////////////////////////////////

	getStateFormGlobalMsgElement = (formId) => {
		return getState([StateEnum.FORM, StateEnum.GLOBAL_MSG, StateEnum.ELEMENT], formId);
	};
	getStateFormGlobalMsgHeadingSuccess = (formId) => {
		return getState([StateEnum.FORM, StateEnum.GLOBAL_MSG, StateEnum.HEADING_SUCCESS], formId);
	};
	getStateFormGlobalMsgHeadingError = (formId) => {
		return getState([StateEnum.FORM, StateEnum.GLOBAL_MSG, StateEnum.HEADING_ERROR], formId);
	};
	getStateFormGlobalMsgHideOnSuccess = (formId) => {
		return getState([StateEnum.FORM, StateEnum.GLOBAL_MSG, StateEnum.HIDE_ON_SUCCESS], formId);
	};

	////////////////////////////////////////////////////////////////
	// Config getters.
	////////////////////////////////////////////////////////////////

	getStateFormConfigPhoneDisablePicker = (formId) => {
		return getState([StateEnum.FORM, StateEnum.CONFIG, StateEnum.CONFIG_PHONE_DISABLE_PICKER], formId);
	};
	getStateFormConfigPhoneUseSync = (formId) => {
		return getState([StateEnum.FORM, StateEnum.CONFIG, StateEnum.CONFIG_PHONE_USE_PHONE_SYNC], formId);
	};
	getStateFormConfigSuccessRedirect = (formId) => {
		return getState([StateEnum.FORM, StateEnum.CONFIG, StateEnum.CONFIG_SUCCESS_REDIRECT], formId);
	};
	getStateFormConfigSuccessRedirectVariation = (formId) => {
		return getState([StateEnum.FORM, StateEnum.CONFIG, StateEnum.CONFIG_SUCCESS_REDIRECT_VARIATION], formId);
	};
	getStateFormConfigSuccessRedirectDownloads = (formId) => {
		return getState([StateEnum.FORM, StateEnum.CONFIG, StateEnum.CONFIG_SUCCESS_REDIRECT_DOWNLOADS], formId);
	};
	getStateFormConfigUseSingleSubmit = (formId) => {
		return getState([StateEnum.FORM, StateEnum.CONFIG, StateEnum.CONFIG_USE_SINGLE_SUBMIT], formId);
	};

	////////////////////////////////////////////////////////////////
	// Steps getters.
	////////////////////////////////////////////////////////////////

	getStateFormStepsFlow = (formId) => {
		return getState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_FLOW], formId);
	};
	getStateFormStepsCurrent = (formId) => {
		return getState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_CURRENT], formId);
	};
	getStateFormStepsFirstStep = (formId) => {
		const items = getState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_ORDER], formId);
		return typeof items !== 'undefined' ? items[0] : '';
	};
	getStateFormStepsLastStep = (formId) => {
		const items = getState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_ORDER], formId);
		return typeof items !== 'undefined' ? items?.[items?.length - 1] : '';
	};
	getStateFormStepsItem = (stepId, formId) => {
		return getState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_ITEMS, stepId], formId);
	};
	getStateFormStepsItems = (formId) => {
		return getState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_ITEMS], formId);
	};
	getStateFormStepsOrder = (formId) => {
		return getState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_ORDER], formId);
	};
	getStateFormStepsElements = (formId) => {
		const items = getState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_ELEMENTS], formId);
		return items ? Object.values(items) : [];
	};
	getStateFormStepsElement = (stepId, formId) => {
		return getState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_ELEMENTS, stepId], formId);
	};
	getStateFormStepsElementsProgressBar = (formId) => {
		const items = getState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_ELEMENTS_PROGRESS_BAR], formId);
		return items ? Object.values(items) : [];
	};
	getStateFormStepsElementProgressBar = (stepId, formId) => {
		return getState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_ELEMENTS_PROGRESS_BAR, stepId], formId);
	};
	getStateFormStepsProgressBar = (formId) => {
		return getState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_PROGRESS_BAR], formId);
	};
	getStateFormStepsProgressBarCountInitial = (formId) => {
		return getState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_PROGRESS_BAR_COUNT_INITIAL], formId);
	};
	getStateFormStepsProgressBarCount = (formId) => {
		return getState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_PROGRESS_BAR_COUNT], formId);
	};
	getStateFormStepsIsUsed = (formId) => {
		return getState([StateEnum.FORM, StateEnum.STEPS, StateEnum.IS_USED], formId);
	};
	getStateFormStepsIsMultiflow = (formId) => {
		return getState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_IS_MULTIFLOW], formId);
	};

	setStateFormStepsCurrent = (value, formId) => {
		setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_CURRENT], value, formId);
	};
	setStateFormStepsFlow = (value, formId) => {
		setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_FLOW], value, formId);
	};
	setStateFormStepsProgressBarCount = (value, formId) => {
		setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_PROGRESS_BAR_COUNT], value, formId);
	};
	setStateFormStepsProgressBarCountInitial = (value, formId) => {
		setState([StateEnum.FORM, StateEnum.STEPS, StateEnum.STEPS_PROGRESS_BAR_COUNT_INITIAL], value, formId);
	};

	////////////////////////////////////////////////////////////////
	// Settings getters.
	////////////////////////////////////////////////////////////////

	getStateSettingsDisableScrollToGlobalMsgOnSuccess = () => {
		return getState([StateEnum.SETTINGS_DISABLE_SCROLL_TO_GLOBAL_MSG_ON_SUCCESS], StateEnum.SETTINGS);
	};
	getStateSettingsDisableScrollToFieldOnError = () => {
		return getState([StateEnum.SETTINGS_DISABLE_SCROLL_TO_FIELD_ON_ERROR], StateEnum.SETTINGS);
	};
	getStateSettingsResetOnSuccess = () => {
		return getState([StateEnum.SETTINGS_FORM_RESET_ON_SUCCESS], StateEnum.SETTINGS);
	};
	getStateSettingsDisableNativeRedirectOnSuccess = () => {
		return getState([StateEnum.SETTINGS_FORM_DISABLE_NATIVE_REDIRECT_ON_SUCCESS], StateEnum.SETTINGS);
	};
	getStateSettingsRedirectionTimeout = () => {
		return getState([StateEnum.SETTINGS_REDIRECTION_TIMEOUT], StateEnum.SETTINGS);
	};
	getStateSettingsHideGlobalMessageTimeout = () => {
		return getState([StateEnum.SETTINGS_HIDE_GLOBAL_MESSAGE_TIMEOUT], StateEnum.SETTINGS);
	};
	getStateSettingsFileRemoveLabel = () => {
		return getState([StateEnum.SETTINGS_FILE_REMOVE_LABEL], StateEnum.SETTINGS);
	};
	getStateSettingsFormDisableAutoInit = () => {
		return getState([StateEnum.SETTINGS_FORM_DISABLE_AUTO_INIT], StateEnum.SETTINGS);
	};
	getStateSettingsFormServerErrorMsg = () => {
		return getState([StateEnum.SETTINGS_FORM_SERVER_ERROR_MSG], StateEnum.SETTINGS);
	};
	getStateSettingsFormCaptchaErrorMsg = () => {
		return getState([StateEnum.SETTINGS_FORM_CAPTCHA_ERROR_MSG], StateEnum.SETTINGS);
	};
	getStateSettingsFormMisconfiguredMsg = () => {
		return getState([StateEnum.SETTINGS_FORM_MISCONFIGURED_MSG], StateEnum.SETTINGS);
	};

	////////////////////////////////////////////////////////////////
	// Element getters.
	////////////////////////////////////////////////////////////////
	getStateElementByTypeField = (type, formId) => {
		const intType = this.getStateFieldType(type);
		return this.getStateFilteredBykey(StateEnum.ELEMENTS, StateEnum.TYPE_FIELD, intType, formId);
	};
	getStateElementByHasError = (type, formId) => {
		return this.getStateFilteredBykey(StateEnum.ELEMENTS, StateEnum.HAS_ERROR, type, formId);
	};
	getStateElementByLoaded = (type, formId) => {
		return this.getStateFilteredBykey(StateEnum.ELEMENTS, StateEnum.LOADED, type, formId);
	};
	getStateElements = (formId) => {
		const items = getState([StateEnum.ELEMENTS], formId);
		return items ? Object.entries(items) : [];
	};
	getStateElementsFields = (formId) => {
		const items = getState([StateEnum.ELEMENTS_FIELDS], formId);
		return items ? Object.entries(items) : [];
	};
	getStateElementConditionalTagsDefaults = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS, StateEnum.TAGS_DEFAULTS], formId);
	};
	getStateElementFieldConditionalTagsDefaults = (name, formId) => {
		return getState([StateEnum.ELEMENTS_FIELDS, name, StateEnum.CONDITIONAL_TAGS, StateEnum.TAGS_DEFAULTS], formId);
	};
	getStateElementConditionalTagsDefaultsInner = (name, innerName, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS_INNER, innerName, StateEnum.TAGS_DEFAULTS], formId);
	};
	getStateElementConditionalTagsRef = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS, StateEnum.TAGS_REF], formId);
	};
	getStateElementFieldConditionalTagsRef = (name, formId) => {
		return getState([StateEnum.ELEMENTS_FIELDS, name, StateEnum.CONDITIONAL_TAGS, StateEnum.TAGS_REF], formId);
	};
	getStateElementConditionalTagsRefInner = (name, innerName, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS_INNER, innerName, StateEnum.TAGS_REF], formId);
	};
	getStateElementConditionalTagsTags = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS, StateEnum.TAGS], formId);
	};
	getStateElementFieldConditionalTagsTags = (name, formId) => {
		return getState([StateEnum.ELEMENTS_FIELDS, name, StateEnum.CONDITIONAL_TAGS, StateEnum.TAGS], formId);
	};
	getStateElementConditionalTagsTagsInner = (name, innerName, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS_INNER, innerName, StateEnum.TAGS], formId);
	};
	getStateElementConfig = (name, type, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.CONFIG, type], formId);
	};
	getStateElementField = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.FIELD], formId);
	};
	getStateElementIsDisabled = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.IS_DISABLED], formId);
	};
	getStateElementLoaded = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.LOADED], formId);
	};
	getStateElementTypeField = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.TYPE_FIELD], formId);
	};
	getStateElementTypeCustom = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.TYPE_CUSTOM], formId);
	};
	getStateElementCustom = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.CUSTOM], formId);
	};
	getStateElementIsSingleSubmit = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.IS_ADMIN_SINGLE_SUBMIT], formId);
	};
	getStateElementSaveAsJson = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.SAVE_AS_JSON], formId);
	};
	getStateElementValueCountry = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY], formId);
	};
	getStateElementInitial = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.INITIAL], formId);
	};
	getStateElementItems = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.ITEMS], formId);
	};
	getStateElementItem = (name, value, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.ITEMS, value], formId);
	};
	getStateElementInput = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.INPUT], formId);
	};
	getStateElementRangeCurrent = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.RANGE_CURRENT], formId);
	};
	getStateElementItemsInput = (name, nameItem, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.ITEMS, nameItem, StateEnum.INPUT], formId);
	};
	getStateElementItemsField = (name, nameItem, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.ITEMS, nameItem, StateEnum.FIELD], formId);
	};
	getStateElementValue = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.VALUE], formId);
	};
	getStateElementValueCombined = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COMBINED], formId);
	};
	getStateElementError = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.ERROR], formId);
	};
	getStateElementTracking = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.TRACKING], formId);
	};
	getStateElementInputSelect = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.INPUT_SELECT], formId);
	};
	getStateElementHasChanged = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.HAS_CHANGED], formId);
	};

	setStateElementValue = (name, value, formId) => {
		setState([StateEnum.ELEMENTS, name, StateEnum.VALUE], value, formId);
	};
	setStateElementValueCountry = (name, value, formId) => {
		setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COUNTRY], value, formId);
	};
	setStateElementValueCombined = (name, value, formId) => {
		setState([StateEnum.ELEMENTS, name, StateEnum.VALUE_COMBINED], value, formId);
	};
	setStateElementLoaded = (name, value, formId) => {
		setState([StateEnum.ELEMENTS, name, StateEnum.LOADED], value, formId);
	};
	setStateElementInitial = (name, value, formId) => {
		setState([StateEnum.ELEMENTS, name, StateEnum.INITIAL], value, formId);
	};
	setStateElementCustom = (name, value, formId) => {
		setState([StateEnum.ELEMENTS, name, StateEnum.CUSTOM], value, formId);
	};
	setStateElementHasError = (name, value, formId) => {
		setState([StateEnum.ELEMENTS, name, StateEnum.HAS_ERROR], value, formId);
	};


	////////////////////////////////////////////////////////////////
	// Captcha getters.
	////////////////////////////////////////////////////////////////

	getStateCaptchaIsUsed = () => {
		return getState([StateEnum.IS_USED], StateEnum.CAPTCHA);
	};
	getStateCaptchaSiteKey = () => {
		return getState([StateEnum.CAPTCHA_SITE_KEY], StateEnum.CAPTCHA);
	};
	getStateCaptchaIsEnterprise = () => {
		return getState([StateEnum.CAPTCHA_IS_ENTERPRISE], StateEnum.CAPTCHA);
	};
	getStateCaptchaSubmitAction = () => {
		return getState([StateEnum.CAPTCHA_SUBMIT_ACTION], StateEnum.CAPTCHA);
	};
	getStateCaptchaInitAction = () => {
		return getState([StateEnum.CAPTCHA_INIT_ACTION], StateEnum.CAPTCHA);
	};
	getStateCaptchaLoadOnInit = () => {
		return getState([StateEnum.CAPTCHA_LOAD_ON_INIT], StateEnum.CAPTCHA);
	};
	getStateCaptchaHideBadge = () => {
		return getState([StateEnum.CAPTCHA_HIDE_BADGE], StateEnum.CAPTCHA);
	};

	////////////////////////////////////////////////////////////////
	// Geolocation getters.
	////////////////////////////////////////////////////////////////

	getStateGeolocationIsUsed = () => {
		return getState([StateEnum.IS_USED], StateEnum.GEOLOCATION) && !getState([StateEnum.IS_ADMIN], StateEnum.CONFIG);
	};

	////////////////////////////////////////////////////////////////
	// Enrichment getters.
	////////////////////////////////////////////////////////////////

	getStateEnrichmentIsUsed = () => {
		return getState([StateEnum.IS_USED], StateEnum.ENRICHMENT) && !getState([StateEnum.IS_ADMIN], StateEnum.CONFIG);
	};
	getStateEnrichmentIsLocalStorageUsed = () => {
		return getState([StateEnum.IS_USED_LOCALSTORAGE], StateEnum.ENRICHMENT) && getState([StateEnum.IS_USED], StateEnum.ENRICHMENT) && !getState([StateEnum.IS_ADMIN], StateEnum.CONFIG);
	};
	getStateEnrichmentIsPrefillUsed = () => {
		return getState([StateEnum.IS_USED_PREFILL], StateEnum.ENRICHMENT) && getState([StateEnum.IS_USED], StateEnum.ENRICHMENT) && !getState([StateEnum.IS_ADMIN], StateEnum.CONFIG);
	};
	getStateEnrichmentIsPrefillUrlUsed = () => {
		return getState([StateEnum.IS_USED_PREFILL_URL], StateEnum.ENRICHMENT) && getState([StateEnum.IS_USED], StateEnum.ENRICHMENT) && !getState([StateEnum.IS_ADMIN], StateEnum.CONFIG);
	};
	getStateEnrichmentExpiration = () => {
		return getState([StateEnum.ENRICHMENT_EXPIRATION], StateEnum.ENRICHMENT);
	};
	getStateEnrichmentExpirationPrefill = () => {
		return getState([StateEnum.ENRICHMENT_EXPIRATION_PREFILL], StateEnum.ENRICHMENT);
	};
	getStateEnrichmentAllowed = () => {
		return getState([StateEnum.ENRICHMENT_ALLOWED], StateEnum.ENRICHMENT);
	};
	getStateEnrichmentStorageName = () => {
		return getState([StateEnum.NAME], StateEnum.ENRICHMENT);
	};
	getStateEnrichmentFormPrefillStorageName = (formId) => {
		return `${getState([StateEnum.NAME], StateEnum.ENRICHMENT)}-${formId}`;
	};

	////////////////////////////////////////////////////////////////
	// Event getters.
	////////////////////////////////////////////////////////////////

	getStateEvent = (name) => {
		return getStateEvent(name);
	};

	////////////////////////////////////////////////////////////////
	// Route getters.
	////////////////////////////////////////////////////////////////

	getStateRoute = (name) => {
		return getStateRoute(name);
	};

	getStateResponseOutputKey = (name) => {
		return getStateResponseOutputKey(name);
	};

	////////////////////////////////////////////////////////////////
	// Selector getters.
	////////////////////////////////////////////////////////////////

	getStateSelector = (name, usePrefix = false) => {
		return getStateSelector(name, usePrefix);
	};

	getStateSelectorAdmin = (name, usePrefix = false) => {
		return getStateSelectorAdmin(name, usePrefix);
	};

	////////////////////////////////////////////////////////////////
	// Attributes getters.
	////////////////////////////////////////////////////////////////

	getStateAttribute = (name) => {
		return getStateAttribute(name);
	};

	////////////////////////////////////////////////////////////////
	// Success redirect getters.
	////////////////////////////////////////////////////////////////

	getStateSuccessRedirectUrlKey = (name) => {
		return getStateSuccessRedirectUrlKey(name);
	};

	////////////////////////////////////////////////////////////////
	// Params getters.
	////////////////////////////////////////////////////////////////

	getStateParam = (name) => {
		return getStateParam(name);
	};

	////////////////////////////////////////////////////////////////
	// Field Type getters.
	////////////////////////////////////////////////////////////////

	getStateFieldType = (name) => {
		return getStateFieldType(name);
	};

	////////////////////////////////////////////////////////////////
	// Other getters.
	////////////////////////////////////////////////////////////////

	getStateFilteredBykey = (obj, targetKey, findItem, formId) => {
		return Object?.values(Object?.fromEntries(Object?.entries(getState([obj], formId) ?? {})?.filter(([key, value]) => value[targetKey] === findItem))); // eslint-disable-line no-unused-vars
	};
	getFormElementByChild = (element) => {
		return element?.closest(this.getStateSelector('form', true));
	};
	getFormFieldElementByChild = (element) => {
		return element?.closest(this.getStateSelector('field', true));
	};
	getFormId = (element) => {
		return element?.getAttribute(this.getStateAttribute('formId'));
	};
	getFormIdByElement = (element) => {
		return this.getFormElementByChild(element)?.getAttribute(this.getStateAttribute('formId'));
	};
	getFieldNameByElement = (element) => {
		return this.getFormFieldElementByChild(element)?.getAttribute(this.getStateAttribute('fieldName'));
	};
	getRestUrl = (value, isPartial = false) => {
		return getRestUrl(value, isPartial);
	};
	getRestUrlByType = (type, value, isPartial = false, checkRef = false) => {
		return getRestUrlByType(type, value, isPartial, checkRef);
	};

	////////////////////////////////////////////////////////////////
	// Private methods - not shared to the public window object.
	////////////////////////////////////////////////////////////////

	/**
	 * Set all public methods.
	 *
	 * @returns {void}
	 */
	publicMethods() {
		setStateWindow();

		if (window[prefix].store) {
			return;
		}

		window[prefix].store = this;
	}
}
