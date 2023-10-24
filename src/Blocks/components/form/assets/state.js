/* global esFormsLocalization */

import {
	StateEnum,
	getStateEventName,
	getState,
	getStateAttribute,
	getStateAttributes,
	prefix,
	setState,
	setStateWindow,
	getStateTop,
} from './state/init';

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
	getStateFormIsSingleSubmit = (formId) => {
		return getState([StateEnum.FORM, StateEnum.IS_SINGLE_SUBMIT], formId);
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
	getStateFormIsConfigured = (formId) => {
		return getState([StateEnum.FORM, StateEnum.ISCONFIGURED], formId);
	};

	setStateFormIsLoaded = (value, formId) => {
		setState([StateEnum.FORM, StateEnum.ISLOADED], value, formId);
	};
	setStateFormIsSingleSubmit = (value, formId) => {
		setState([StateEnum.FORM, StateEnum.IS_SINGLE_SUBMIT], value, formId);
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
	getStateFormConditionalTagsInnerEvents = (formId) => {
		return getState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_INNER_EVENTS], formId);
	};
	getStateFormConditionalTagsIgnore = (formId) => {
		return getState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_IGNORE], formId);
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
	getStateFormConfigDownloads = (formId) => {
		return getState([StateEnum.FORM, StateEnum.CONFIG, StateEnum.CONFIG_DOWNLOADS], formId);
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
	getStateSettingsFormMisconfiguredMsg = () => {
		return getState([StateEnum.SETTINGS_FORM_MISCONFIGURED_MSG], StateEnum.SETTINGS);
	};

	////////////////////////////////////////////////////////////////
	// Element getters.
	////////////////////////////////////////////////////////////////
	getStateElementByType = (type, formId) => {
		return this.getStateFilteredBykey(StateEnum.ELEMENTS, StateEnum.TYPE, type, formId);
	};
	getStateElementByTypeInternal = (type, formId) => {
		return this.getStateFilteredBykey(StateEnum.ELEMENTS, StateEnum.TYPE_INTERNAL, type, formId);
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
	getStateElementConditionalTagsDefaults = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS, StateEnum.TAGS_DEFAULTS], formId);
	};
	getStateElementConditionalTagsDefaultsInner = (name, innerName, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS_INNER, innerName, StateEnum.TAGS_DEFAULTS], formId);
	};
	getStateElementConditionalTagsRef = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS, StateEnum.TAGS_REF], formId);
	};
	getStateElementConditionalTagsRefInner = (name, innerName, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS_INNER, innerName, StateEnum.TAGS_REF], formId);
	};
	getStateElementConditionalTagsTags = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.CONDITIONAL_TAGS, StateEnum.TAGS], formId);
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
	getStateElementTypeInternal = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.TYPE_INTERNAL], formId);
	};
	getStateElementTypeCustom = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.TYPE_CUSTOM], formId);
	};
	getStateElementCustom = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.CUSTOM], formId);
	};
	getStateElementIsSingleSubmit = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.IS_SINGLE_SUBMIT], formId);
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
	getStateElementType = (name, formId) => {
		return getState([StateEnum.ELEMENTS, name, StateEnum.TYPE], formId);
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
	// Enrichment getters.
	////////////////////////////////////////////////////////////////

	getStateEnrichmentIsUsed = () => {
		return getState([StateEnum.IS_USED], StateEnum.ENRICHMENT);
	};
	getStateEnrichmentExpiration = () => {
		return getState([StateEnum.ENRICHMENT_EXPIRATION], StateEnum.ENRICHMENT);
	};
	getStateEnrichmentAllowed = () => {
		return getState([StateEnum.ENRICHMENT_ALLOWED], StateEnum.ENRICHMENT);
	};
	getStateEnrichmentStorageName = () => {
		return getState([StateEnum.NAME], StateEnum.ENRICHMENT);
	};

	////////////////////////////////////////////////////////////////
	// Events getters.
	////////////////////////////////////////////////////////////////

	getStateEventName = (name) => {
		getStateEventName(name);
	};
	getStateEventsBeforeFormSubmit = () => {
		return getState([StateEnum.EVENTS_BEFORE_FORM_SUBMIT], StateEnum.EVENTS);
	};
	getStateEventsAfterFormSubmit = () => {
		return getState([StateEnum.EVENTS_AFTER_FORM_SUBMIT], StateEnum.EVENTS);
	};
	getStateEventsAfterFormSubmitSuccessBeforeRedirect = () => {
		return getState([StateEnum.EVENTS_AFTER_FORM_SUBMIT_SUCCESS_BEFORE_REDIRECT], StateEnum.EVENTS);
	};
	getStateEventsAfterFormSubmitSuccess = () => {
		return getState([StateEnum.EVENTS_AFTER_FORM_SUBMIT_SUCCESS], StateEnum.EVENTS);
	};
	getStateEventsAfterFormSubmitReset = () => {
		return getState([StateEnum.EVENTS_AFTER_FORM_SUBMIT_RESET], StateEnum.EVENTS);
	};
	getStateEventsAfterFormSubmitError = () => {
		return getState([StateEnum.EVENTS_AFTER_FORM_SUBMIT_ERROR], StateEnum.EVENTS);
	};
	getStateEventsAfterFormSubmitErrorValidation = () => {
		return getState([StateEnum.EVENTS_AFTER_FORM_SUBMIT_ERROR_VALIDATION], StateEnum.EVENTS);
	};
	getStateEventsAfterFormSubmitEnd = () => {
		return getState([StateEnum.EVENTS_AFTER_FORM_SUBMIT_END], StateEnum.EVENTS);
	};
	getStateEventsAfterGtmDataPush = () => {
		return getState([StateEnum.EVENTS_AFTER_GTM_DATA_PUSH], StateEnum.EVENTS);
	};
	getStateEventsFormJsLoaded = () => {
		return getState([StateEnum.EVENTS_FORM_JS_LOADED], StateEnum.EVENTS);
	};
	getStateEventsFormManualInitLoaded = () => {
		return getState([StateEnum.EVENTS_FORM_MANUAL_INIT_LOADED], StateEnum.EVENTS);
	};
	getStateEventsAfterCaptchaInit = () => {
		return getState([StateEnum.EVENTS_AFTER_CAPTCHA_INIT], StateEnum.EVENTS);
	};
	getStateEventsStepsGoToNextStep = () => {
		return getState([StateEnum.EVENTS_STEPS_GO_TO_NEXT_STEP], StateEnum.EVENTS);
	};
	getStateEventsStepsGoToPrevStep = () => {
		return getState([StateEnum.EVENTS_STEPS_GO_TO_PREV_STEP], StateEnum.EVENTS);
	};

	////////////////////////////////////////////////////////////////
	// Selectors getters.
	////////////////////////////////////////////////////////////////

	getStateSelectorsClassActive = () => {
		return getState([StateEnum.SELECTORS_CLASS_ACTIVE], StateEnum.SELECTORS);
	};
	getStateSelectorsClassFilled = () => {
		return getState([StateEnum.SELECTORS_CLASS_FILLED], StateEnum.SELECTORS);
	};
	getStateSelectorsClassLoading = () => {
		return getState([StateEnum.SELECTORS_CLASS_LOADING], StateEnum.SELECTORS);
	};
	getStateSelectorsClassHidden = () => {
		return getState([StateEnum.SELECTORS_CLASS_HIDDEN], StateEnum.SELECTORS);
	};
	getStateSelectorsClassVisible = () => {
		return getState([StateEnum.SELECTORS_CLASS_VISIBLE], StateEnum.SELECTORS);
	};
	getStateSelectorsClassLoaderDisableOverlay = () => {
		return getState([StateEnum.SELECTORS_CLASS_LOADER_DISABLE_OVERLAY], StateEnum.SELECTORS);
	};
	getStateSelectorsClassHiddenConditionalTags = () => {
		return getState([StateEnum.SELECTORS_CLASS_HIDDEN_CT], StateEnum.SELECTORS);
	};
	getStateSelectorsClassVisibleConditionalTags = () => {
		return getState([StateEnum.SELECTORS_CLASS_VISIBLE_CT], StateEnum.SELECTORS);
	};
	getStateSelectorsClassDisabled = () => {
		return getState([StateEnum.SELECTORS_CLASS_DISABLED], StateEnum.SELECTORS);
	};
	getStateSelectorsClassHasError = () => {
		return getState([StateEnum.SELECTORS_CLASS_HAS_ERROR], StateEnum.SELECTORS);
	};
	getStateSelectorsClassGeolocationLoading = () => {
		return getState([StateEnum.SELECTORS_CLASS_GEOLOCATION_LOADING], StateEnum.SELECTORS);
	};
	getStateSelectorsForm = () => {
		return getState([StateEnum.SELECTORS_FORM], StateEnum.SELECTORS);
	};
	getStateSelectorsForms = () => {
		return getState([StateEnum.SELECTORS_FORMS], StateEnum.SELECTORS);
	};
	getStateSelectorsSubmitSingle = () => {
		return getState([StateEnum.SELECTORS_SUBMIT_SINGLE], StateEnum.SELECTORS);
	};
	getStateSelectorsStep = () => {
		return getState([StateEnum.SELECTORS_STEP], StateEnum.SELECTORS);
	};
	getStateSelectorsStepSubmit = () => {
		return getState([StateEnum.SELECTORS_STEP_SUBMIT], StateEnum.SELECTORS);
	};
	getStateSelectorsError = () => {
		return getState([StateEnum.SELECTORS_ERROR], StateEnum.SELECTORS);
	};
	getStateSelectorsLoader = () => {
		return getState([StateEnum.SELECTORS_LOADER], StateEnum.SELECTORS);
	};
	getStateSelectorsGlobalMsg = () => {
		return getState([StateEnum.SELECTORS_GLOBAL_MSG], StateEnum.SELECTORS);
	};
	getStateSelectorsGroup = () => {
		return getState([StateEnum.SELECTORS_GROUP], StateEnum.SELECTORS);
	};
	getStateSelectorsField = () => {
		return getState([StateEnum.SELECTORS_FIELD], StateEnum.SELECTORS);
	};
	getStateSelectorsSelectClearable = () => {
		return getState([StateEnum.SELECTORS_SELECT_CLEARABLE], StateEnum.SELECTORS);
	};

	////////////////////////////////////////////////////////////////
	// Attributes getters.
	////////////////////////////////////////////////////////////////

	getStateAttributes = () => {
		return getStateAttributes();
	};
	getStateAttribute = (name) => {
		return getStateAttribute(name);
	};

	////////////////////////////////////////////////////////////////
	// Params getters.
	////////////////////////////////////////////////////////////////
	
	getStateParams = () => {
		return getStateTop(StateEnum.PARAMS);
	};
	getStateParam = (name) => {
		return this.getStateParams()[name];
	};

	////////////////////////////////////////////////////////////////
	// Other getters.
	////////////////////////////////////////////////////////////////

	getStateFilteredBykey = (obj, targetKey, findItem, formId) => {
		return Object?.values(Object?.fromEntries(Object?.entries(getState([obj], formId) ?? {})?.filter(([key, value]) => value[targetKey] === findItem))); // eslint-disable-line no-unused-vars
	};
	getFormElementByChild = (element) => {
		return element.closest(this.getStateSelectorsForm());
	};
	getFormFieldElementByChild = (element) => {
		return element.closest(this.getStateSelectorsField());
	};
	getFormId = (element) => {
		return element.getAttribute(getStateAttribute('formId'));
	};
	getFormIdByElement = (element) => {
		return this.getFormElementByChild(element).getAttribute(getStateAttribute('formId'));
	};
	getFieldNameByElement = (element) => {
		return this.getFormFieldElementByChild(element).getAttribute(getStateAttribute('fieldName'));
	};
	getRestUrl = (value) => {
		return getRestUrl(value);
	};
	getRestUrlByType = (type, value) => {
		return getRestUrlByType(type, value);
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

/**
 * Routes enum connected to enqueu object.
 */
export const ROUTES = {
	// Common.
	PREFIX: esFormsLocalization?.restRoutes?.prefix,
	PREFIX_PROJECT: esFormsLocalization?.restRoutes?.prefixProject,
	PREFIX_SUBMIT: esFormsLocalization?.restRoutes?.prefixSubmit,
	PREFIX_TEST_API: esFormsLocalization?.restRoutes?.prefixTestApi,
	FILES: esFormsLocalization?.restRoutes?.files,

	// Admin.
	SETTINGS: esFormsLocalization?.restRoutes?.settings,
	CACHE_CLEAR: esFormsLocalization?.restRoutes?.cacheClear,
	MIGRATION: esFormsLocalization?.restRoutes?.migration,
	TRANSFER: esFormsLocalization?.restRoutes?.transfer,
	BULK: esFormsLocalization?.restRoutes?.bulk,
	LOCATIONS: esFormsLocalization?.restRoutes?.locations,

	// Editor.
	PREFIX_INTEGRATIONS_ITEMS_INNER: esFormsLocalization?.restRoutes?.prefixIntegrationItemsInner,
	PREFIX_INTEGRATIONS_ITEMS: esFormsLocalization?.restRoutes?.prefixIntegrationItems,
	PREFIX_INTEGRATION_EDITOR: esFormsLocalization?.restRoutes?.prefixIntegrationEditor,
	INTEGRATIONS_EDITOR_SYNC: esFormsLocalization?.restRoutes?.integrationsEditorSync,
	INTEGRATIONS_EDITOR_CREATE: esFormsLocalization?.restRoutes?.integrationsEditorCreate,
	FORM_FIELDS: esFormsLocalization?.restRoutes?.formFields,
	COUNTRIES_GEOLOCATION: esFormsLocalization?.restRoutes?.countriesGeolocation,

	// Public.
	CAPTCHA: esFormsLocalization?.restRoutes?.captcha,
	GEOLOCATION: esFormsLocalization?.restRoutes?.geolocation,
	VALIDATION_STEP: esFormsLocalization?.restRoutes?.validationStep,
};

/**
 * Get rest api url link.
 *
 * @param {string} value Value to get
 * @param {bool} isPartial Is relative or absolute url.
 *
 * @returns {string}
 */
export function getRestUrl(value, isPartial = false) {
	const prefix = isPartial ? ROUTES.PREFIX_PROJECT : ROUTES.PREFIX;

	const url = prefix.replace(/\/$/, ''); // Remove trailing slash.
	const sufix = value.replace(/^\/+/, ''); // Remove leading slash.

	return `${url}/${sufix}`;
}

/**
 * Get rest api url link with integration prefix.
 *
 * @param {string} type Integration type.
 * @param {string} value Value to get
 * @param {bool} isPartial Is relative or absolute url.
 *
 * @returns {string}
 */
export function getRestUrlByType(type, value, isPartial = false) {
	const prefix = isPartial ? ROUTES.PREFIX_PROJECT : ROUTES.PREFIX;

	const url = prefix.replace(/\/$/, ''); // Remove trailing slash.
	const sufix = value.replace(/^\/+/, ''); // Remove leading slash.
	const typePrefix = type.replace(/^\/|\/$/g, ''); // Remove leading and trailing slash.

	return `${url}/${typePrefix}/${sufix}`;
}
