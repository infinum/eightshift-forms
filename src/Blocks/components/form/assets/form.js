/* global grecaptcha */

import { cookies, debounce } from '@eightshift/frontend-libs/scripts/helpers';
import { State, ROUTES } from './state';
import { Utils } from './utilities';
import { Steps } from './step';
import { Enrichment } from './enrichment';
import { Captcha } from './captcha';
import { ConditionalTags } from './conditional-tags';
import selectManifest from './../../select/manifest.json';
import {
	StateEnum,
	prefix,
	setStateFormInitial,
	setStateInitial,
	setStateWindow,
	setStateValues,
	setStateConditionalTagsItems,
} from './state/init';

/**
 * Main Forms class.
 */
export class Form {
	constructor() {
		this.state = new State();
		this.utils = new Utils();
		this.enrichment = new Enrichment();
		this.captcha = new Captcha();
		this.conditionalTags = new ConditionalTags();
		this.steps = new Steps();

		this.FORM_DATA = new FormData();

		this.FILTER_IS_STEPS_FINAL_SUBMIT = 'isStepsFinalSubmit';
		this.FILTER_SKIP_FIELDS = 'skipFields';
		this.FILTER_USE_ONLY_FIELDS = 'useOnlyFields';
		this.GLOBAL_MSG_TIMEOUT_ID = undefined;
	}

	////////////////////////////////////////////////////////////////
	// Public methods
	////////////////////////////////////////////////////////////////

	/**
	 * Init all actions.
	 * 
	 * @returns {void}
	 */
	init() {
		setStateInitial();

		// Set all public methods.
		this.publicMethods();

		// Init all forms.
		this.initOnlyForms();

		// Init captcha.
		this.captcha.init();

		// Init enrichment.
		this.enrichment.init();
	}

	/**
	 * Init only forms.
	 * 
	 * @returns {void}
	 */
	initOnlyForms() {
		// Loop all forms on the page.
		[...document.querySelectorAll(this.state.getStateSelectorsForm())].forEach((element) => {
			const formId = element.getAttribute(this.state.getStateAttribute('formId')) || 0;

			setStateFormInitial(formId);

			this.initOne(formId);

			this.conditionalTags.initOne(formId);

			this.steps.initOne(formId);
		});
	}

	/**
	 * Init one form by form Id.
	 * 
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	initOne(formId) {
		// Regular submit.
		this.state.getStateFormElement(formId).addEventListener('submit', this.onFormSubmitEvent);

		// Setup select inputs.
		[...this.state.getStateElementByType('select', formId)].forEach((select) => {
			this.setupSelectField(formId, select.name);
		});

		// Setup file single inputs.
		[...this.state.getStateElementByType('file', formId)].forEach((file) => {
			this.setupFileField(formId, file.name);
		});

		// Setup regular inputs.
		[...this.state.getStateElementByType('text', formId)].forEach((input) => {
			this.setupInputField(formId, input.name);
		});

		[...this.state.getStateElementByType('number', formId)].forEach((input) => {
			this.setupInputField(formId, input.name);
		});

		// Date.
		[...this.state.getStateElementByType('date', formId)].forEach((input) => {
			this.setupDateField(formId, input.name);
		});

		[...this.state.getStateElementByType('tel', formId)].forEach((tel) => {
			this.setupTelField(formId, tel.name);
		});

		[...this.state.getStateElementByType('checkbox', formId)].forEach((checkbox) => {
			[...Object.values(checkbox.items)].forEach((checkboxItem) => {
				this.setupRadioCheckboxField(formId, checkboxItem.value, checkboxItem.name);
			});
		});

		[...this.state.getStateElementByType('radio', formId)].forEach((radio) => {
			[...Object.values(radio.items)].forEach((radioItem) => {
				this.setupRadioCheckboxField(formId, radioItem.value, radioItem.name);
			});
		});

		// Setup textarea inputs.
		[...this.state.getStateElementByType('textarea', formId)].forEach((textarea) => {
			this.setupTextareaField(formId, textarea.name);
		});

		// Form loaded.
		this.utils.isFormLoaded(formId);
	}

	/**
	 * Handle form submit and all logic.
	 * 
	 * @param {string} formId Form Id.
	 * @param {object} filter Additional filter to pass.
	 *
	 * @returns {void}
	 */
	formSubmit(formId, filter = {}) {
		// Dispatch event.
		this.utils.dispatchFormEvent(formId, this.state.getStateEventsBeforeFormSubmit());

		this.setFormData(formId, filter);

		const formType = this.state.getStateFormType(formId);

		// Populate body data.
		const body = {
			method: this.state.getStateFormMethod(formId),
			mode: 'same-origin',
			headers: {
				Accept: 'multipart/form-data',
			},
			body: this.FORM_DATA,
			credentials: 'same-origin',
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		// Url for frontend forms.
		let url = this.state.getRestUrlByType(ROUTES.PREFIX_SUBMIT, formType);

		// For admin settings use different url and add nonce.
		if (this.state.getStateConfigIsAdmin()) {
			url = this.state.getRestUrl(ROUTES.SETTINGS);
			body.headers['X-WP-Nonce'] = this.state.getStateConfigNonce();
		}

		fetch(url, body)
			.then((response) => {
				return response.json();
			})
			.then((response) => {
				this.formSubmitBefore(formId, response);

				// On success state.
				if (response.status === 'success') {
					this.formSubmitSuccess(formId, response, filter?.[this.FILTER_IS_STEPS_FINAL_SUBMIT]);
				} else {
					this.formSubmitError(formId, response, filter?.[this.FILTER_IS_STEPS_FINAL_SUBMIT]);
				}

				this.formSubmitAfter(formId, response);
			});

			this.FORM_DATA = new FormData();
	}

	/**
	 * Handle form submit and all logic for steps form.
	 * 
	 * @param {string} formId Form Id.
	 * @param {object} filter Additional filter to pass.
	 *
	 * @returns {void}
	 */
	formSubmitStep(formId, filter = {}) {
		this.setFormData(formId, filter);
		this.setFormDataStep(formId);

		// Populate body data.
		const body = {
			method: this.state.getStateFormMethod(formId),
			mode: 'same-origin',
			headers: {
				Accept: 'multipart/form-data',
			},
			body: this.FORM_DATA,
			credentials: 'same-origin',
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		const url = this.state.getRestUrl(ROUTES.VALIDATION_STEP);

		fetch(url, body)
			.then((response) => {
				return response.json();
			})
			.then((response) => {
				this.formSubmitBefore(formId, response);
				this.steps.formStepSubmit(formId, response);
			});

			this.FORM_DATA = new FormData();
	}

	/**
	 * Actions to run before form submit.
	 *
	 * @param {string} formId Form Id.
	 * @param {object} response Api response.
	 *
	 * @returns {void}
	 */
	formSubmitBefore(formId, response) {
		// Dispatch event.
		this.utils.dispatchFormEvent(formId, this.state.getStateEventsAfterFormSubmit(), response);

		// Clear all errors.
		this.utils.resetErrors(formId);

		// Remove loader.
		this.utils.hideLoader(formId);
	}

	/**
	 * Actions to run after form submit on success.
	 *
	 * @param {string} formId Form Id.
	 * @param {object} response Api response.
	 * @param {bool} isFinalStep Check in steps if we are on final step.
	 *
	 * @returns {void}
	 */
	formSubmitSuccess(formId, response, isFinalStep = false) {
		const {
			status,
			message,
			data,
		} = response;

		this.utils.dispatchFormEvent(formId, this.state.getStateEventsAfterFormSubmitSuccess(), response);

		if (this.state.getStateConfigIsAdmin()) {
			// Set global msg.
			this.utils.setGlobalMsg(formId, message, status);

			if (this.state.getStateFormIsSingleSubmit(formId)) {
				this.utils.redirectToUrlByRefference(formId, window.location.href, true);
			}
		} else {
			// Send GTM.
			this.utils.gtmSubmit(formId, status);

			if (this.state.getStateFormConfigSuccessRedirect(formId)) {
				// Redirect to url and update url params from from data.
				this.utils.redirectToUrl(formId, this.FORM_DATA);
			} else {
				// Clear form values.
				this.utils.resetForm(formId);

				// Set global msg.
				this.utils.setGlobalMsg(formId, message, status);

				// Do normal success without redirect.
				// Do the actual redirect after some time for custom form processed externally.
				if (data?.processExternaly) {
					setTimeout(() => {
						this.state.getStateFormElement().submit();
					}, parseInt(this.state.getStateSettingsRedirectionTimeout(formId), 10));
				}

				// Return to original first step.
				if (isFinalStep) {
					this.steps.resetSteps(formId);
				}
			}
		}
	}

	/**
	 * Actions to run after form submit on error.
	 *
	 * @param {string} formId Form Id.
	 * @param {object} response Api response.
	 * @param {bool} isFinalStep Check in steps if we are on final step.
	 *
	 * @returns {void}
	 */
	formSubmitError(formId, response, isFinalStep = false) {
		const {
			status,
			message,
			data,
		} = response;

		this.utils.setGlobalMsg(formId, message, status);

		this.utils.gtmSubmit(formId, status, data?.validation);

		// Dispatch event.
		if (data?.validation !== undefined) {
			this.utils.dispatchFormEvent(formId, this.state.getStateEventsAfterFormSubmitErrorValidation(), response);

			this.utils.outputErrors(formId, data?.validation);

			if (isFinalStep) {
				this.steps.goToStepWithError(formId, data?.validation);
			}
		} else {
			this.utils.dispatchFormEvent(formId, this.state.getStateEventsAfterFormSubmitError(), response);
		}
	}

	/**
	 * Actions to run after form submit.
	 *
	 * @param {string} formId Form Id.
	 * @param {object} response Api response.
	 *
	 * @returns {void}
	 */
	formSubmitAfter(formId, response) {
		// Reset timeout for after each submit.
		if (typeof this.GLOBAL_MSG_TIMEOUT_ID === "number") {
			clearTimeout(this.GLOBAL_MSG_TIMEOUT_ID);
		}

		// Hide global msg in any case after some time.
		this.GLOBAL_MSG_TIMEOUT_ID = setTimeout(() => {
			this.utils.unsetGlobalMsg(formId);
		}, parseInt(this.state.getStateSettingsHideGlobalMessageTimeout(formId), 10));

		// Dispatch event.
		this.utils.dispatchFormEvent(formId, this.state.getStateEventsAfterFormSubmitEnd(), response);
	}

	/**
	 * Handle form submit on captcha.
	 * 
	 * @param {string} formId Form Id.
	 * @param {object} filter Additional filter to pass.
	 *
	 * @returns {void}
	 */
	runFormCaptcha(formId, filter = {}) {
		if (!this.state.getStateCaptchaIsUsed()) {
			return;
		}

		const actionName = this.state.getStateCaptchaSubmitAction();
		const siteKey = this.state.getStateCaptchaSiteKey();

		if (this.state.getStateCaptchaIsEnterprise()) {
			grecaptcha.enterprise.ready(async () => {
				await grecaptcha.enterprise.execute(siteKey, {action: actionName}).then((token) => {
					this.setFormDataCaptcha({
						token,
						isEnterprise: true,
						action: actionName,
					});

					this.formSubmit(formId, filter);
				});
			});
		} else {
			grecaptcha.ready(async () => {
				await grecaptcha.execute(siteKey, {action: actionName}).then((token) => {
					this.setFormDataCaptcha({
						token,
						isEnterprise: false,
						action: actionName,
					});
					this.formSubmit(formId, filter);
				});
			});
		}
	}

	////////////////////////////////////////////////////////////////
	// Form Data
	////////////////////////////////////////////////////////////////

	/**
	 * Set form data object for all forms.
	 * 
	 * @param {string} formId Form Id.
	 * @param {object} filter Additional filter to pass.
	 *
	 * @returns {void}
	 */
	setFormData(formId, filter = {}) {
		let internalFilter = filter;

		if (this.state.getStateConfigIsAdmin()) {
			this.setFormDataAdmin(formId);
			internalFilter = {
				...internalFilter,
				[this.FILTER_SKIP_FIELDS]: this.getFormDataGroup(formId) ?? [],
			};
		} else {
			this.setFormDataPerType(formId);
			this.setFormDataEnrichment();
		}

		this.setFormDataFields(formId, internalFilter);
		this.setFormDataCommon(formId);
	}

	/**
	 * Set form data object for all forms - fields.
	 * 
	 * @param {string} formId Form Id.
	 * @param {object} filter Additional filter to pass.
	 *
	 * @returns {void}
	 */
	setFormDataFields(formId, filter = {}) {
		const formType = this.state.getStateFormType(formId);

		// Used for single submit.
		const useOnlyFields = filter?.[this.FILTER_USE_ONLY_FIELDS] ?? [];
		this.state.setStateFormIsSingleSubmit(Boolean(useOnlyFields.length), formId);

		// Used for group submit.
		const skipFields = filter?.[this.FILTER_SKIP_FIELDS] ?? [];

		// Iterate all form items.
		for (const [key] of this.state.getStateElements(formId)) { // eslint-disable-line no-unused-vars

			const name = key;
			const internalType = this.state.getStateElementTypeInternal(key, formId);
			const value = this.state.getStateElementValue(key, formId);
			const typeCustom = this.state.getStateElementTypeCustom(key, formId);
			const saveAsJson = this.state.getStateElementSaveAsJson(key, formId);
			const items = this.state.getStateElementItems(key, formId);
			const field = this.state.getStateElementField(key, formId);
			const valueCountry = this.state.getStateElementValueCountry(key, formId);
			const disabled = this.state.getStateElementIsDisabled(key, formId);

			// Used for single submit.
			if (useOnlyFields.length && !useOnlyFields.includes(name)) {
				continue;
			}

			// Used for group submit.
			if (skipFields.length && skipFields.includes(name)) {
				continue;
			}

			// Build data object.
			const data = {
				name,
				value,
				type: internalType,
				typeCustom,
				custom: '',
				innerName: '',
			};

			switch (formType) {
				case 'hubspot':
						data.custom = field.getAttribute(this.state.getStateAttribute('hubspotTypeId')) ?? '';
					break;
			}

			switch (internalType) {
				case 'checkbox':
					let indexCheck = 0; // eslint-disable-line no-case-declarations
					for(const [checkName, checkValue] of Object.entries(value)) {
						if (disabled[checkName]) {
							continue;
						}

						data.value = checkValue;
						data.innerName = checkName;

						this.FORM_DATA.append(`${name}[${indexCheck}]`, JSON.stringify(data));
						indexCheck++;
					}
					break;
				case 'radio':
					let indexRadio = 0; // eslint-disable-line no-case-declarations
					for(const [radioName, radioValue] of Object.entries(items)) {
						if (disabled[radioName]) {
							continue;
						}

						data.value = radioValue.input.checked ? radioValue.value : '';
						data.innerName = radioName;

						this.FORM_DATA.append(`${name}[${indexRadio}]`, JSON.stringify(data));
						indexRadio++;
					}
					break;
				case 'textarea':
					if (disabled) {
						break;
					}

					// Convert textarea to json format with : as delimiter.
					if (saveAsJson) {
						data.value = this.utils.getSaveAsJsonFormatOutput(formId, name);
					}

					this.FORM_DATA.append(name, JSON.stringify(data));
					break;
				case 'tel':
					if (disabled) {
						break;
					}

					if (!this.state.getStateFormConfigPhoneDisablePicker(formId) && value) {
						data.value = `${valueCountry.number}${value}`;
					}

					this.FORM_DATA.append(name, JSON.stringify(data));
					break;
				case 'file':
					if (disabled) {
						break;
					}

					// If custom file use files got from the global object of files uploaded.
					const fileList = this.state.getStateElementCustom(name, formId)?.files ?? []; // eslint-disable-line no-case-declarations

					// Loop files and append.
					if (fileList.length) {
						for (const [key, file] of Object.entries(fileList)) {
							const status = file?.xhr?.response ? JSON.parse(file.xhr.response)?.status : 'error';

							// Check if the file is ok.
							if (status === 'success') {
								data.value = this.utils.getFileNameFromFileObject(file);
								this.FORM_DATA.append(`${name}[${key}]`, JSON.stringify(data));
							}
						}
					} else {
						this.FORM_DATA.append(`${name}[0]`, JSON.stringify(data));
					}
					break;
				default:
					if (disabled) {
						break;
					}

					this.FORM_DATA.append(name, JSON.stringify(data));
					break;
			}
		}
	}

	/**
	 * Set form data object for all forms - group.
	 * 
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	getFormDataGroup(formId) {
		const output = [];
		const groups = this.state.getStateFormElement(formId).querySelectorAll(`${this.state.getStateSelectorsGroup()}`);

		// Check if we are saving group items in one key.
		if (!groups.length) {
			return output;
		}

		for (const [key, group] of Object.entries(groups)) { // eslint-disable-line no-unused-vars
			const groupSaveAsOneField = Boolean(group.getAttribute(this.state.getStateAttribute('groupSaveAsOneField')));

			if (!groupSaveAsOneField) {
				continue;
			}

			const groupInner = group.querySelectorAll('input, select, textarea');

			if (!groupInner.length) {
				continue;
			}

			const groupInnerItems = {};

			for (const [key, groupInnerItem] of Object.entries(groupInner)) { // eslint-disable-line no-unused-vars
				const {
					name,
					value,
					disabled,
				} = groupInnerItem;

				if (disabled) {
					continue;
				}

				groupInnerItems[name] = value;
				output.push(name);
			}

			const groupId = group.getAttribute(this.state.getStateAttribute('fieldId'));

			if (groupId) {
				this.FORM_DATA.append(groupId, JSON.stringify({
					name: groupId,
					value: groupInnerItems,
					type: 'group',
					typeCustom: 'group',
				}));
			}
		}

		return output;
	}

	/**
	 * Set form data object for all forms - steps.
	 * 
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	setFormDataStep(formId) {
		this.buildFormDataItems([
			{
				name: this.state.getStateParam('steps'),
				value: this.state.getStateFormStepsItem(this.state.getStateFormStepsCurrent(formId), formId),
				custom: this.state.getStateFormStepsCurrent(formId),
			},
		]);
	}

	/**
	 * Set form data object for all forms - common.
	 * 
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	setFormDataCommon(formId) {
		this.buildFormDataItems([
			{
				name: this.state.getStateParam('formId'),
				value: formId,
			},
			{
				name: this.state.getStateParam('postId'),
				value: this.state.getStateFormPostId(formId),
			},
			{
				name: this.state.getStateParam('type'),
				value: this.state.getStateFormType(formId),
			},
			{
				name: this.state.getStateParam('action'),
				value: this.state.getStateFormAction(formId),
			},
			{
				name: this.state.getStateParam('actionExternal'),
				value: this.state.getStateFormActionExternal(formId),
			},
		]);
	}

	/**
	 * Set form data object for all forms - enrichment.
	 * 
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	setFormDataEnrichment() {
		if (!this.state.getStateEnrichmentIsUsed()) {
			return;
		}

		const data = this.enrichment.getLocalStorage();

		if (data) {
			this.buildFormDataItems([
				{
					name: this.state.getStateParam('storage'),
					value: this.enrichment.getLocalStorage(),
				},
			]);
		}
	}

	/**
	 * Set form data object for all forms - admin.
	 * 
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	setFormDataAdmin(formId) {
		this.buildFormDataItems([
			{
				name: this.state.getStateParam('settingsType'),
				value: this.state.getStateFormTypeSettings(formId),
			},
		]);
	}

	/**
	 * Set form data object for all forms - per form type.
	 * 
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	setFormDataPerType(formId) {
		let output = [];

		switch (this.state.getStateFormType(formId)) {
			case 'hubspot':
				output = [
					...output,
					{
						name: this.state.getStateParam('hubspotCookie'),
						value: cookies.getCookie('hubspotutk'),
					},
					{
						name: this.state.getStateParam('hubspotPageName'),
						value: document.title,
					},
					{
						name: this.state.getStateParam('hubspotPageUrl'),
						value: window.location.href,
					},
				];
				break;
		}

		this.buildFormDataItems(output);
	}

	/**
	 * Set form data object for all forms - captcha.
	 * 
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	setFormDataCaptcha(data) {
		this.buildFormDataItems([
			{
				name: this.state.getStateParam('captcha'),
				value: data,
			},
		]);
	}

	/**
	 * Build helper for form data object.
	 * 
	 * @param {string} formId Form Id.
	 * @param {object} dataSet Object to build.
	 *
	 * @returns {void}
	 */
	buildFormDataItems(data, dataSet = this.FORM_DATA) {
		data.forEach((item) => {
			const {
				name,
				value,
				type = 'hidden',
				typeCustom = 'hidden',
				custom = '',
			} = item;

			dataSet.append(name, JSON.stringify({
				name,
				value,
				type,
				typeCustom,
				custom,
			}));
		});
	}

	////////////////////////////////////////////////////////////////
	// Fields
	////////////////////////////////////////////////////////////////

	/**
	 * Setup text field.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	setupInputField(formId, name) {
		const input = this.state.getStateElementInput(name, formId);

		this.state.setStateElementLoaded(name, true, formId);

		this.utils.setFieldFilledState(formId, name);

		input.addEventListener('keydown', this.onFocusEvent);
		input.addEventListener('focus', this.onFocusEvent);
		input.addEventListener('blur', this.onBlurEvent);
		input.addEventListener('input', this.onInputEvent);
	}

	/**
	 * Setup tel field.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	setupTelField(formId, name) {
		this.setupInputField(formId, name);

		if (this.state.getStateFormConfigPhoneDisablePicker(formId)) {
			this.state.getStateElementField(name, formId).querySelector('select').remove();
		} else {
			this.setupSelectField(formId, name);
		}
	}

	/**
	 * Setup radio/checkbox field.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} value Field value.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	setupRadioCheckboxField(formId, value, name) {
		const input = this.state.getStateElementItemsInput(name, value, formId);

		this.state.setStateElementLoaded(name, true, formId);

		this.utils.setFieldFilledState(formId, name);

		input.addEventListener('keydown', this.onFocusEvent);
		input.addEventListener('focus', this.onFocusEvent);
		input.addEventListener('blur', this.onBlurEvent);
		input.addEventListener('input', this.onInputEvent);
	}

	/**
	 * Setup date field.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	setupDateField(formId, name) {
		const input = this.state.getStateElementInput(name, formId);

		import('flatpickr').then((flatpickr) => {
			const state = this.state;
			const utils = this.utils;
			const conditionalTags = this.conditionalTags;

			flatpickr.default(input, {
				enableTime: this.state.getStateElementTypeInternal(name, formId) === 'datetime',
				dateFormat: input.getAttribute(this.state.getStateAttribute('dateOutputFormat')),
				altFormat: input.getAttribute(this.state.getStateAttribute('datePreviewFormat')),
				altInput: true,
				onReady: function(selectedDates, value) {
					state.setStateElementInitial(name, value, formId);
					state.setStateElementLoaded(name, true, formId);
					state.setStateElementValue(name, value, formId);
					state.setStateElementCustom(name, this, formId);

					utils.setFieldFilledState(formId, name);
				},
				onOpen: function () {
					utils.setFieldActiveState(formId, name);
				},
				onChange: function (selectedDates, value) {
					state.setStateElementValue(name, value, formId);

					utils.setFieldFilledState(formId, name);
					conditionalTags.setField(formId, name);
				},
			});
		});
	}

	/**
	 * Setup select field.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	setupSelectField(formId, name) {
		let input = this.state.getStateElementInput(name, formId);
		const type = this.state.getStateElementType(name, formId);

		 if (type === 'tel') {
			 input = this.state.getStateElementInputSelect(name, formId);
		 }

		import('choices.js').then((Choices) => {
			const state = this.state;

			const customProperties = [
				this.state.getStateAttribute('selectCountryCode'),
				this.state.getStateAttribute('selectCountryLabel'),
				this.state.getStateAttribute('selectCountryNumber'),
				this.state.getStateAttribute('conditionalTags'),
				this.state.getStateAttribute('selectVisibility'),
			];
 
			const choices = new Choices.default(input, {
				searchEnabled: this.state.getStateElementConfig(name, StateEnum.CONFIG_SELECT_USE_SEARCH, formId),
				shouldSort: false,
				position: 'bottom',
				allowHTML: true,
				duplicateItemsAllowed: false,
				placeholder: this.state.getStateElementConfig(name, StateEnum.CONFIG_SELECT_USE_PLACEHOLDER, formId),
				searchFields: ['label', 'value', 'customProperties'],
				itemSelectText: '',
				classNames: {
					containerOuter: `choices ${selectManifest.componentClass}`,
				},
				callbackOnCreateTemplates: function() {
					return {
						// Fake select option.
						option: (...args) => {
							const element = Choices.default.defaults.templates.option.call(this, ...args);
							const properties = args?.[0]?.customProperties;

							if (properties) {
								element.setAttribute(state.getStateAttribute('selectCustomProperties'), JSON.stringify(properties));
							}

							return element;
						},
						// Dropdown items.
						choice: (...args) => {
							const element = Choices.default.defaults.templates.choice.call(this, ...args);
							const properties = !state.getStateElementLoaded(name, formId) ? args?.[1]?.customProperties : this.config?.choices[args?.[1]?.id - 1]?.customProperties;

							if (properties) {
								customProperties.forEach((property) => {
									const check = properties?.[property];
									if (check) {
										element.setAttribute(property, check);
									}
								});
							}

							return element;
						},
						// Selected item.
						item: (...args) => {
							const element = Choices.default.defaults.templates.item.call(this, ...args);
							const properties = args?.[1]?.customProperties;

							if (properties) {
								customProperties.forEach((property) => {
									const check = properties?.[property];
									if (check) {
										element.setAttribute(property, check);
									}
								});
							}

							return element;
						},
					};
				},
			});

			const countryCookie = cookies.getCookie('esForms-country').toLocaleLowerCase();
			if (countryCookie) {
				choices.setChoiceByValue(countryCookie);
			}

			this.state.setStateElementLoaded(name, true, formId);
			this.state.setStateElementCustom(name, choices, formId);

			choices.config.choices.map((item) => {
				setStateConditionalTagsItems(item.customProperties[this.state.getStateAttribute('conditionalTags')], name, item.value, formId);
			});

			choices.containerOuter.element.addEventListener('focus', this.onFocusEvent);
			choices.containerOuter.element.addEventListener('blur', this.onBlurEvent);
			choices.containerOuter.element.addEventListener('change', this.onSelectChangeEvent);
		});
	}

	/**
	 * Setup textarea field.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	setupTextareaField(formId, name) {
		const input = this.state.getStateElementInput(name, formId);

		import('autosize').then((autosize) => {
			input.setAttribute('rows', '1');
			input.setAttribute('cols', '');

			autosize.default(input);
		});

		this.state.setStateElementLoaded(name, true, formId);

		this.utils.setFieldFilledState(formId, name);

		input.addEventListener('keydown', this.onFocusEvent);
		input.addEventListener('focus', this.onFocusEvent);
		input.addEventListener('blur', this.onBlurEvent);
		input.addEventListener('input', this.onInputEvent);
	}

	/**
	 * Setup file field.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	setupFileField(formId, name) {
		const input = this.state.getStateElementInput(name, formId);
		const field = this.state.getStateElementField(name, formId);

		import('dropzone').then((Dropzone) => {
			const dropzone = new Dropzone.default(
				field,
				{
					url: this.state.getRestUrl(ROUTES.FILES),
					addRemoveLinks: true,
					autoDiscover: false,
					parallelUploads: 1,
					maxFiles: !input.multiple ? 1 : null,
					dictRemoveFile: this.state.getStateSettingsFileRemoveLabel(formId),
				}
			);

			// Set data to internal state.
			this.state.setStateElementLoaded(name, true, formId);
			this.state.setStateElementCustom(name, dropzone, formId);

			// On add one file add selectors for UX.
			dropzone.on("addedfile", (file) => {
				setTimeout(() => {
					file.previewTemplate.classList.add(this.state.getStateSelectorsClassActive());
				}, 200);

				setTimeout(() => {
					file.previewTemplate.classList.add(this.state.getStateSelectorsClassFilled());
				}, 1200);

				field.classList.remove(this.state.getStateSelectorsClassActive());

				this.state.setStateElementValue(name, 'true', formId);

				// Remove main filed validation error.
				this.utils.unsetFieldError(formId, name);
			});

			dropzone.on('removedfile', () => {
				const custom = this.state.getStateElementCustom(name, formId);

				if (custom.files.length === 0) {
					field.classList.remove(this.state.getStateSelectorsClassFilled());
				}

				field.classList.remove(this.state.getStateSelectorsClassActive());
				this.state.setStateElementValue(name, '', formId);
			});

			// Add data formData to the api call for the file upload.
			dropzone.on("sending", (file, xhr, formData) => {
				// Add common items like formID and type.
				this.buildFormDataItems([
					{
						name: this.state.getStateParam('formId'),
						value: formId,
					},
					{
						name: this.state.getStateParam('postId'),
						value: this.state.getStateFormPostId(formId),
					},
					{
						name: this.state.getStateParam('type'),
						value: 'fileUpload', // Not connected to anything just here for reference.
					},
					{
						// Add field name to know where whas this file upload to.
						name: this.state.getStateParam('name'),
						value: name,
					},
					{
						// Add file ID to know the file.
						name: this.state.getStateParam('fileId'),
						value: file?.upload?.uuid,
					},
				], formData);
			});

			// Once data is outputed from uplaod.
			dropzone.on("success", (file) => {
				const response = JSON.parse(file.xhr.response);

				// Output errors if ther is any.
				if (response?.data?.validation !== undefined) {
					file.previewTemplate.querySelector('.dz-error-message span').innerHTML = response?.data?.validation?.[file?.upload?.uuid];

					// Remove faulty files.
					setTimeout(() => {
						dropzone.removeFile(file);
					}, 2500);
				}

				field.classList.add(this.state.getStateSelectorsClassFilled());
			});

			// On max file size reached output error and remove files.
			dropzone.on('maxfilesreached', (files) => {
				files.forEach((file) => {
					if (file.status === 'error') {
						setTimeout(() => {
							dropzone.removeFile(file);
						}, 2500);
					}
				});
			});

		// 	// Trigger on wrap click.
			field.addEventListener('click', this.onFileWrapClickEvent);
		});
	}

	////////////////////////////////////////////////////////////////
	// Other
	////////////////////////////////////////////////////////////////

	/**
	 * Remove all event listeners from elements.
	 * 
	 * @returns {vodi}
	 */
	removeEvents() {
		// const elements = document.querySelectorAll(this.data.formSelector);

		// [...elements].forEach((element) => {
		// 	// Regular submit.
		// 	element.removeEventListener('submit', this.onFormSubmitEvent);

		// 	const formId = element.getAttribute(this.state.getStateAttribute('formId'));

		// 	const inputs = element.querySelectorAll(this.data.inputSelector);
		// 	const textareas = element.querySelectorAll(this.data.textareaSelector);
		// 	const selects = element.querySelectorAll(this.data.selectSelector);
		// 	const files = element.querySelectorAll(this.data.fileSelector);

		// 	[...inputs].forEach((input) => {
		// 		switch (input.type) {
		// 			case 'date':
		// 			case 'datetime-local':
		// 				// this.state.deleteState(this.state.DATES, formId);
		// 				break;
		// 		}

		// 		input.removeEventListener('keydown', this.onFocusEvent);
		// 		input.removeEventListener('focus', this.onFocusEvent);
		// 		input.removeEventListener('blur', this.onBlurEvent);
		// 	});

		// 	[...selects].forEach(() => {
		// 		this.state.deleteState(this.state.SELECTS, formId);
		// 	});

		// 	// Setup textarea inputs.
		// 	[...textareas].forEach((textarea) => {
		// 		textarea.removeEventListener('keydown', this.onFocusEvent);
		// 		textarea.removeEventListener('focus', this.onFocusEvent);
		// 		textarea.removeEventListener('blur', this.onBlurEvent);

		// 		// this.state.deleteState(this.state.TEXTAREAS, formId);
		// 	});

		// 	// Setup file single inputs.
		// 	[...files].forEach((file) => {
		// 		this.state.deleteState(this.state.FILES, formId);

		// 		file.nextElementSibling.removeEventListener('click', this.onFileWrapClickEvent);

		// 		const button = file.parentNode.querySelector('a');

		// 		button.removeEventListener('focus', this.onFocusEvent);
		// 		button.removeEventListener('blur', this.onBlurEvent);
		// 	});

		// 	this.state.dispatchFormEvent(element, this.state.getStateEventsAfterFormSubmitReset(), formId);
		// });
	}

	////////////////////////////////////////////////////////////////
	// Events callback
	////////////////////////////////////////////////////////////////

	/**
	 * Handle form submit and all logic.
	 * 
	 * @param {object} event Event callback.
	 *
	 * @returns {void}
	 */
	onFormSubmitEvent = (event) => {
		event.preventDefault();

		const formId = this.state.getFormIdByElement(event.target);

		if (this.state.getStateFormStepsIsUsed(formId)) {
			const button = event.submitter;
			const field = this.state.getFormFieldElementByChild(button);
			// Steps flow.
			let direction = button.getAttribute(this.state.getStateAttribute('submitStepDirection'));

			if (field.classList.contains(this.state.getStateSelectorsClassHidden())) {
				direction = this.steps.STEP_DIRECTION_NEXT;
			}

			switch (direction) {
				case this.steps.STEP_DIRECTION_NEXT:
					this.utils.showLoader(formId);

					const filterNext = { // eslint-disable-line no-case-declarations
						[this.FILTER_SKIP_FIELDS]: [
							...this.conditionalTags.getIgnoreFields(formId),
						],
					};

					debounce(this.formSubmitStep(formId, filterNext), 100);
					break;
				case this.steps.STEP_DIRECTION_PREV:
					this.steps.goToPrevStep(formId);
					break;
				default:
					this.utils.showLoader(formId);

					const filterFinal = { // eslint-disable-line no-case-declarations
						[this.FILTER_SKIP_FIELDS]: [
							...this.steps.getIgnoreFields(formId),
							...this.conditionalTags.getIgnoreFields(formId),
						],
						[this.FILTER_IS_STEPS_FINAL_SUBMIT]: true,
					};

					if (this.state.getStateCaptchaIsUsed()) {
						this.runFormCaptcha(formId, filterFinal);
					} else {
						debounce(this.formSubmit(formId, filterFinal), 100);
					}
					break;
			}
		} else {
			// Normal flow.
			this.utils.showLoader(formId);

			const filterNormal = {
				[this.FILTER_SKIP_FIELDS]: [
					...this.conditionalTags.getIgnoreFields(formId),
				],
			};

			if (this.state.getStateCaptchaIsUsed()) {
				this.runFormCaptcha(formId, filterNormal);
			} else {
				debounce(this.formSubmit(formId, filterNormal), 100);
			}
		}
	};

	/**
	 * On custom file wrapper click event callback.
	 *
	 * @param {object} event Event callback.
	 *
	 * @returns {void}
	 */
	onFileWrapClickEvent = (event) => {
		event.preventDefault();
		event.stopPropagation();

		const field = this.state.getFormFieldElementByChild(event.target);

		this.state.getStateElementCustom(field.getAttribute(this.state.getStateAttribute('fieldName')), this.state.getFormIdByElement(event.target)).hiddenFileInput.click();

		field.classList.add(this.state.getStateSelectorsClassActive());
	};

	/**
	 * On focus event for regular fields.
	 *
	 * @param {object} event Event callback.
	 *
	 * @returns {void}
	 */
	onFocusEvent = (event) => {
		this.utils.setFieldActiveState(
			this.state.getFormIdByElement(event.target),
			this.state.getFormFieldElementByChild(event.target).getAttribute(this.state.getStateAttribute('fieldName'))
		);
	};

	/**
	 * On Select change event.
	 *
	 * @param {object} event Event callback.
	 *
	 * @returns {void}
	 */
	onSelectChangeEvent = (event) => {
		const field = this.state.getFormFieldElementByChild(event.target);
		const formId = this.state.getFormIdByElement(event.target);
		const type = field.getAttribute(this.state.getStateAttribute('fieldType'));
		const name = field.getAttribute(this.state.getStateAttribute('fieldName'));

		setStateValues(event.target, this.state.getFormIdByElement(event.target));

		if (!this.state.getStateFormConfigPhoneDisablePicker(formId) && this.state.getStateFormConfigPhoneUseSync(formId)) {
			if (type === 'country') {
				const country = this.state.getStateElementValueCountry(name, formId);
				[...this.state.getStateElementByTypeInternal('tel', formId)].forEach((tel) => {
					const name = tel[StateEnum.NAME];
					const value = this.state.getStateElementValue(name, formId);

					this.state.getStateElementCustom(name, formId).setChoiceByValue(country.number);
					this.state.setStateElementValueCountry(name, country, formId);
					if (value) {
						this.state.setStateElementValueCombined(name, `${country.number}${value}`, formId);
					}
				});
			}

			if (type === 'phone') {
				const phone = this.state.getStateElementValueCountry(name, formId);
				[...this.state.getStateElementByTypeInternal('country', formId)].forEach((country) => {
					const name = country[StateEnum.NAME];

					this.state.getStateElementCustom(name, formId).setChoiceByValue(phone.label);
					this.state.setStateElementValueCountry(name, phone, formId);
					this.state.setStateElementValue(name, phone.label, formId);
				});
			}
		}

		this.conditionalTags.setField(formId, name);

		if (this.state.getStateConfigIsAdmin() && this.state.getStateElementIsSingleSubmit(name, formId)) {
			debounce(
				this.formSubmit(
					formId, {
						[this.FILTER_USE_ONLY_FIELDS]: [name]
					}
			), 100);
		}
	};

	/**
	 * On input event for regular fields.
	 *
	 * @param {object} event Event callback.
	 *
	 * @returns {void}
	 */
	onInputEvent = (event) => {
		const formId = this.state.getFormIdByElement(event.target);
		const field = this.state.getFormFieldElementByChild(event.target);
		const name = field.getAttribute(this.state.getStateAttribute('fieldName'));

		setStateValues(event.target, this.state.getFormIdByElement(event.target));

		this.conditionalTags.setField(formId, name);

		if (this.state.getStateConfigIsAdmin() && this.state.getStateElementIsSingleSubmit(name, formId)) {
			debounce(
				this.formSubmit(
					formId, {
						[this.FILTER_USE_ONLY_FIELDS]: [name]
					}
			), 100);
		}
	};

	/**
	 * On blur event for regular fields.
	 *
	 * @param {object} event Event callback.
	 *
	 * @returns {void}
	 */
	onBlurEvent = (event) => {
		const field = this.state.getFormFieldElementByChild(event.target);
		const name = field.getAttribute(this.state.getStateAttribute('fieldName'));
		const formId = this.state.getFormIdByElement(event.target);

		this.utils.setFieldFilledState(formId, name);
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
		this.state.publicMethods();

		window[prefix].form = {};
		window[prefix].form = {
			FILTER_IS_STEPS_FINAL_SUBMIT: this.FILTER_IS_STEPS_FINAL_SUBMIT,
			FILTER_SKIP_FIELDS: this.FILTER_SKIP_FIELDS,
			FILTER_USE_ONLY_FIELDS: this.FILTER_USE_ONLY_FIELDS,
			GLOBAL_MSG_TIMEOUT_ID: this.GLOBAL_MSG_TIMEOUT_ID,

			FORM_DATA: this.FORM_DATA,

			init: () => {
				this.init();
			},
			initOnlyForms: () => {
				this.initOnlyForms();
			},
			initOne: (element) => {
				this.initOne(element);
			},
			formSubmit: (formId, filter) => {
				this.formSubmit(formId, filter);
			},
			formSubmitStep: (formId, filter) => {
				this.formSubmitStep(formId, filter);
			},
			formSubmitBefore: (formId, response) => {
				this.formSubmitBefore(formId, response);
			},
			formSubmitSuccess: (formId, response, isFinalStep) => {
				this.formSubmitSuccess(formId, response, isFinalStep);
			},
			formSubmitError: (formId, response, isFinalStep) => {
				this.formSubmitError(formId, response, isFinalStep);
			},
			formSubmitAfter: (formId, response) => {
				this.formSubmitAfter(formId, response);
			},
			runFormCaptcha: (formId, filter) => {
				this.runFormCaptcha(formId, filter);
			},
			setFormData: (formId, filter) => {
				this.setFormData(formId, filter);
			},
			getFormDataGroup: (formId) => {
				return this.getFormDataGroup(formId);
			},
			setFormDataCommon: (formId) => {
				this.setFormDataCommon(formId);
			},
			setFormDataEnrichment: () => {
				this.setFormDataEnrichment();
			},
			setFormDataAdmin: (formId) => {
				this.setFormDataAdmin(formId);
			},
			setFormDataPerType: (formId) => {
				this.setFormDataPerType(formId);
			},
			setFormDataCaptcha: (data) => {
				this.setFormDataCaptcha(data);
			},
			buildFormDataItems: (data, dataSet) => {
				this.buildFormDataItems(data, dataSet);
			},
			setupInputField: (formId, name) => {
				this.setupInputField(formId, name);
			},
			setupTelField: (formId, name) => {
				this.setupTelField(formId, name);
			},
			setupRadioCheckboxField: (formId, value, name) => {
				this.setupRadioCheckboxField(formId, value, name);
			},
			setupDateField: (formId, name) => {
				this.setupDateField(formId, name);
			},
			setupSelectField: (formId, name) => {
				this.setupSelectField(formId, name);
			},
			setupTextareaField: (formId, name) => {
				this.setupTextareaField(formId, name);
			},
			setupFileField: (formId, name) => {
				this.setupFileField(formId, name);
			},
			removeEvents: () => {
				this.removeEvents();
			},
			onFormSubmitEvent: (event) => {
				this.onFormSubmitEvent(event);
			},
			onFileWrapClickEvent: (event) => {
				this.onFileWrapClickEvent(event);
			},
			onFocusEvent: (event) => {
				this.onFocusEvent(event);
			},
			onSelectChangeEvent: (event) => {
				this.onSelectChangeEvent(event);
			},
			onInputEvent: (event) => {
				this.onInputEvent(event);
			},
			onBlurEvent: (event) => {
				this.onBlurEvent(event);
			},
		// 	formSubmitCaptcha: (element, token, payed, action) => {
		// 		this.formSubmitCaptcha(element, token, payed, action);
		// 	},
		// 	formSubmit: (element, singleSubmit = false, isStepSubmit = false) => {
		// 		this.formSubmit(element, singleSubmit, isStepSubmit);
		// 	},
		// 	setFormData: (element, singleSubmit = false) => {
		// 		this.setFormData(element, singleSubmit);
		// 	},
		// 	setupInputField: (input, formId) => {
		// 		this.setupInputField(input, formId);
		// 	},
		// 	setupDateField: (date, formId) => {
		// 		this.setupDateField(date, formId);
		// 	},
		// 	setupSelectField: (select, formId) => {
		// 		this.setupSelectField(select, formId);
		// 	},
		// 	setupTextareaField: (textarea, formId) => {
		// 		this.setupTextareaField(textarea, formId);
		// 	},
		// 	setupFileField: (file, formId, index, element) => {
		// 		this.setupFileField(file, formId, index, element);
		// 	},
		// 	setupPhoneSync: (form, formId) => {
		// 		this.setupPhoneSync(form, formId);
		// 	},
		// 	removeEvents: () => {
		// 		this.removeEvents();
		// 	},
		// 	runFormCaptcha: (element = '') => {
		// 		this.runFormCaptcha(element);
		// 	},
		// 	onFormSubmitEvent: (event) => {
		// 		this.onFormSubmitEvent(event);
		// 	},
		// 	onFileWrapClickEvent: (event) => {
		// 		this.onFileWrapClickEvent(event);
		// 	},
		};
	}
}
