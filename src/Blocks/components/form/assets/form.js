/* global grecaptcha, esFormsLocalization */

import { cookies, debounce } from '@eightshift/frontend-libs/scripts/helpers';
import { ConditionalTags } from './../../conditional-tags/assets/conditional-tags';
import { Steps } from '../../step/assets/step';
import { Enrichment } from './enrichment';
import { Captcha } from './captcha';
import { Utils } from './utilities';
import { State, prefix } from './state';

/**
 * Main Forms class.
 */
export class Form {
	constructor(options = {}) {
		this.state = new State(options);
		this.utils = new Utils(options);
		this.enrichment = new Enrichment(options);
		this.captcha = new Captcha(options);
		this.conditionalTags = new ConditionalTags(options);
		// this.steps = new Steps(options);

		this.FORM_DATA = new FormData();
	}

	////////////////////////////////////////////////////////////////
	// Public methods
	////////////////////////////////////////////////////////////////

	/**
	 * Init all actions.
	 * 
	 * @public
	 */
	init() {
		this.state.setStateInitial();

		// Set all public methods.
		this.publicMethods();

		// Init step.
		// this.steps.init();

		// Init all forms.
		this.initOnlyForms();

		// Init conditional tags.
		this.conditionalTags.init();

		// Init captcha.
		this.captcha.init();

		// Init enrichment.
		this.enrichment.init();
	}

	/**
	 * Init all forms.
	 * 
	 * @public
	 */
	initOnlyForms() {
		// Loop all forms on the page.
		[...document.querySelectorAll(this.state.getStateSelectorsForm())].forEach((element) => {
			const formId = element.getAttribute(this.state.getStateAttribute('formPostId')) || 0;

			this.state.setFormStateInitial(formId);

			this.conditionalTags.initOne(formId);

			this.initOne(formId);
		});
	}

	/**
	 * Init one form by element.
	 * 
	 * @param {object} element Form element.
	 *
	 * @public
	 */
	initOne(formId) {
		// Regular submit.
		this.state.getStateFormElement(formId).addEventListener('submit', this.onFormSubmitEvent);

		// Setup select inputs.
		[...this.state.getStateFilteredBykey(this.state.ELEMENTS, this.state.TYPE, 'select', formId)].forEach((select) => {
			this.setupSelectField(select.name, formId);
		});

		// Setup file single inputs.
		[...this.state.getStateFilteredBykey(this.state.ELEMENTS, this.state.TYPE, 'file', formId)].forEach((file) => {
			this.setupFileField(file.name, formId);
		});

		// Setup regular inputs.
		[...this.state.getStateFilteredBykey(this.state.ELEMENTS, this.state.TYPE, 'text', formId)].forEach((input) => {
			this.setupInputField(input.name, formId);
		});

		[...this.state.getStateFilteredBykey(this.state.ELEMENTS, this.state.TYPE, 'number', formId)].forEach((input) => {
			this.setupInputField(input.name, formId);
		});

		// Date.
		[...this.state.getStateFilteredBykey(this.state.ELEMENTS, this.state.TYPE, 'date', formId)].forEach((input) => {
			this.setupDateField(input.name, formId);
		});

		[...this.state.getStateFilteredBykey(this.state.ELEMENTS, this.state.TYPE, 'tel', formId)].forEach((tel) => {
			this.setupTelField(tel.name, formId);
		});

		[...this.state.getStateFilteredBykey(this.state.ELEMENTS, this.state.TYPE, 'checkbox', formId)].forEach((checkbox) => {
			[...Object.values(checkbox.items)].forEach((checkboxItem) => {
				this.setupRadioCheckboxField(checkboxItem.value, checkboxItem.name, formId);
			});
		});

		[...this.state.getStateFilteredBykey(this.state.ELEMENTS, this.state.TYPE, 'radio', formId)].forEach((radio) => {
			[...Object.values(radio.items)].forEach((radioItem) => {
				this.setupRadioCheckboxField(radioItem.value, radioItem.name, formId);
			});
		});

		// Setup textarea inputs.
		[...this.state.getStateFilteredBykey(this.state.ELEMENTS, this.state.TYPE, 'textarea', formId)].forEach((textarea) => {
			this.setupTextareaField(textarea.name, formId);
		});

		// Form loaded.
		this.utils.isFormLoaded(formId);
	}

	/**
	 *  Handle form submit and all logic in case we have captcha in place.
	 * 
	 * @param {object} element Form element.
	 * @param {string} token Captcha token from api.
	 *
	 * @public
	 */
	formSubmitCaptcha(formId, token, payed, action) {
		// Populate body data.
		const body = {
			method: this.state.getStateFormMethod(formId),
			mode: 'same-origin',
			headers: {
				Accept: 'application/json',
			},
			body: JSON.stringify({
				token,
				payed,
				action,
			}),
			credentials: 'same-origin',
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		fetch(this.state.getStateCaptchaSubmitUrl(), body)
		.then((response) => {
			return response.json();
		})
		.then((response) => {
			const {
				status,
				message,
			} = response;

			// On success state.
			if (status === 'success') {
				this.formSubmit(formId);
			} else {
				// Clear all errors.
				this.utils.resetErrors(formId);

				// Remove loader.
				this.utils.hideLoader(formId);

				// Set global msg.
				this.utils.setGlobalMsg(formId, message, status);

				// Hide global msg in any case after some time.
				setTimeout(() => {
					this.utils.unsetGlobalMsg(formId);
				}, parseInt(this.state.getStateSettingsHideGlobalMessageTimeout(formId), 10));
			}
		});
	}

	/**
	 * Handle form submit and all logic.
	 * 
	 * @param {object} element Form element.
	 * @param {boolean|object} singleSubmit Is form single submit, used in admin if yes pass element.
	 * @param {boolean} isStepValidation Is form single submit, used in admin.
	 * @param {boolean} isStepSubmit Check if is step submit.
	 *
	 * @public
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
		let url = this.state.getStateConfigSubmitUrl(`-${formType}`);

		// For admin settings use different url and add nonce.
		if (this.state.getStateConfigIsAdmin()) {
			url = this.state.getStateConfigSubmitUrl();
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
					this.formSubmitSuccess(formId, response);
				} else {
					this.formSubmitError(formId, response);
				}

				this.formSubmitAfter(formId, response);
			});

			this.FORM_DATA = new FormData();
	}

	formSubmitBefore(formId, response) {
		// Dispatch event.
		this.utils.dispatchFormEvent(formId, this.state.getStateEventsAfterFormSubmit(), response);

		// Clear all errors.
		this.utils.resetErrors(formId);

		// Remove loader.
		this.utils.hideLoader(formId);
	}

	formSubmitSuccess(formId, response) {
		const {
			status,
			message,
			data,
		} = response;

		// Redirect on success.
		// if (isStepSubmit) {
		// 	// this.steps.formStepStubmit(element, response);
		// } else {
			// Dispatch event.
			this.utils.dispatchFormEvent(formId, this.state.getStateEventsAfterFormSubmitSuccess(), response);

			if (this.state.getStateConfigIsAdmin()) {
				// Set global msg.
				this.utils.setGlobalMsg(formId, message, status);

				if (this.state.getStateFormIsSingleSubmit(formId)) {
					this.utils.redirectToUrlByRefference(window.location.href, formId, true);
				}
			} else {
				// Send GTM.
				this.utils.gtmSubmit(formId, this.FORM_DATA, status);

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
				}
			}
		// }
	}

	formSubmitError(formId, response) {
		const {
			status,
			message,
			data,
		} = response;

		// Set global msg.
		// if (isStepSubmit) {
		// 	// this.steps.formStepStubmit(element, response);
		// } else {
			this.utils.setGlobalMsg(formId, message, status);

			this.utils.gtmSubmit(formId, this.FORM_DATA, status, data?.validation);

			// Dispatch event.
			if (data?.validation !== undefined) {
				this.utils.dispatchFormEvent(formId, this.state.getStateEventsAfterFormSubmitErrorValidation(), response);

				// this.steps.goBackToFirstValidationErrorStep(formId, data?.validation);
				this.utils.outputErrors(formId, data?.validation);
			} else {
				this.utils.dispatchFormEvent(formId, this.state.getStateEventsAfterFormSubmitError(), response);
			}
		// }
	}

	formSubmitAfter(formId, response) {
		// Hide global msg in any case after some time.
		setTimeout(() => {
			this.utils.unsetGlobalMsg(formId);
		}, parseInt(this.state.getStateSettingsHideGlobalMessageTimeout(formId), 10));

		// Dispatch event.
		this.utils.dispatchFormEvent(formId, this.state.getStateEventsAfterFormSubmitEnd(), response);
	}

	runFormCaptcha(formId) {
		if (!this.state.getStateCaptchaIsUsed()) {
			return;
		}

		const actionName = this.state.getStateCaptchaSubmitAction();
		const siteKey = this.state.getStateCaptchaSiteKey();

		if (this.state.getStateCaptchaIsEnterprise()) {
			grecaptcha.enterprise.ready(async () => {
				await grecaptcha.enterprise.execute(siteKey, {action: actionName}).then((token) => {
					this.formSubmitCaptcha(formId, token, 'enterprise', actionName);
				});
			});
		} else {
			grecaptcha.ready(async () => {
				await grecaptcha.execute(siteKey, {action: actionName}).then((token) => {
					this.formSubmitCaptcha(formId, token, 'free', actionName);
				});
			});
		}
	}

	////////////////////////////////////////////////////////////////
	// Form Data
	////////////////////////////////////////////////////////////////

	setFormData(formId, filter = {}) {
		if (this.state.getStateConfigIsAdmin()) {
			this.setFormDataAdmin(formId);
			filter = {
				...filter,
				skipFields: this.setFormDataGroup(formId) ?? [],
			};
		} else {
			this.setFormDataPerType(formId);
			this.setFormDataEnrichment();
		}

		this.setFormDataFields(formId, filter);
		this.setFormDataCommon(formId);
	}

	/**
 * Build form data object.
 * 
 * @param {object} element Form element.
 * @param {boolean} singleSubmit Is form single submit, used in admin.
 *
 * @public
 */
	setFormDataFields(formId, filter = {}, isStepSubmit = false) {
		const formType = this.state.getStateFormType(formId);

		// Used for single submit.
		const useOnlyFields = filter.useOnlyFields ?? [];
		this.state.setState([this.state.FORM, this.state.IS_SINGLE_SUBMIT], Boolean(useOnlyFields.length), formId);

		// Used for group submit.
		const skipFields = filter.skipFields ?? [];

		// Iterate all form items.
		for (const [key, item] of this.state.getStateElements(formId)) { // eslint-disable-line no-unused-vars

			const name = key;
			const type = this.state.getStateElementType(key, formId);
			const input = this.state.getStateElementInput(key, formId);
			const value = this.state.getStateElementValue(key, formId);
			const internalType = this.state.getStateElementInternalType(key, formId);
			const saveAsJson = this.state.getStateElementSaveAsJson(key, formId);
			const items = this.state.getStateElementItems(key, formId);
			const valueCountry = this.state.getStateElementValueCountry(key, formId);

			// Used for single submit.
			if (useOnlyFields.length && !useOnlyFields.includes(name)) {
				continue;
			}

			// Used for group submit.
			if (skipFields.length && skipFields.includes(name)) {
				continue;
			}

			if (input.disabled) {
				continue;
			}

			// Build data object.
			const data = {
				name,
				value,
				type,
				internalType,
				custom: '',
			};

			switch (formType) {
				case 'hubspost':
						data.custom = item.objectTypeId ?? '';
					break;
			}

			switch (type) {
				case 'checkbox':
					Object.values(value).forEach((item, index) => {
						data.value = item;

						this.FORM_DATA.append(`${name}[${index}]`, JSON.stringify(data));
					});
					break;
				case 'radio':
					Object.values(items).forEach((item, index) => {
						data.value = item.input.checked ? item.value : '';

						this.FORM_DATA.append(`${name}[${index}]`, JSON.stringify(data));
					});
					break;
				case 'textarea':
					// Convert textarea to json format with : as delimiter.
					if (saveAsJson) {
						data.value = this.utils.getSaveAsJsonFormatOutput(name, formId);
					}

					this.FORM_DATA.append(name, JSON.stringify(data));
					break;
				case 'tel':
					if (!this.state.getStateFormConfigPhoneDisablePicker(formId) && value) {
						data.value = `${valueCountry.number}${value}`;
					}

					this.FORM_DATA.append(name, JSON.stringify(data));
					break;
				case 'file':
					// If custom file use files got from the global object of files uploaded.
					const fileList = this.state.getStateElementCustom(name, formId)?.files ?? []; // eslint-disable-line no-case-declarations

					// Loop files and append.
					if (fileList.length) {
						for (const [key, file] of Object.entries(fileList)) {
							const status = file?.xhr?.response ? JSON.parse(file.xhr.response)?.status : 'error';

							// Check if the file is ok.
							if (status === 'success') {
								data.value = file.upload.uuid;
								this.FORM_DATA.append(`${name}[${key}]`, JSON.stringify(data));
							}
						}
					} else {
						this.FORM_DATA.append(`${name}[0]`, JSON.stringify({}));
					}
					break;
				default:
					this.FORM_DATA.append(name, JSON.stringify(data));
					break;
			}
		}
	}

	setFormDataGroup(formId) {
		const output = [];
		const groups = this.state.getStateFormElement(formId).querySelectorAll(`${this.state.getStateSelectorsGroup()}`);

		// Check if we are saving group items in one key.
		if (!groups.length) {
			return;
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
					value: groupInnerItems,
					type: 'group',
				}));
			}
		}

		return output;
	}

	setFormDataCommon(formId) {
		this.buildFormDataItems([
			{
				name: this.state.getStateParam('postId'),
				value: formId,
				type: 'hidden',
			},
			{
				name: this.state.getStateParam('type'),
				value: this.state.getStateFormType(formId),
				type: 'hidden',
			},
			{
				name: this.state.getStateParam('action'),
				value: this.state.getStateFormAction(formId),
				type: 'hidden',
			},
			{
				name: this.state.getStateParam('actionExternal'),
				value: this.state.getStateFormActionExternal(formId),
				type: 'hidden',
			},
		]);
	}

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
					type: 'hidden',
				},
			]);
		}
	}

	setFormDataStep() {
		// Output fields to validate if we are validating steps only.
		// const stepId = this.steps.getCurrentStep(element);
		// if (stepId && isStepSubmit) {
		// 	// Find all fields by name in the step.
		// 	const fieldsInStep = this.steps.getAllFieldsInStep(element, stepId);

		// 	if (fieldsInStep) {
		// 		// Find all field names and remove null ones (submit).
		// 		const outputStepFields = Array.from(fieldsInStep, (stepField) => stepField.getAttribute(this.state.getStateAttribute('fieldName'))).filter(n => n);

		// 		// Append the data as a custom field.
		// 		if (outputStepFields) {
		// 			this.FORM_DATA.append(this.data.FORM_PARAMS.stepFields, JSON.stringify({
		// 				name: this.data.FORM_PARAMS.stepFields,
		// 				value: outputStepFields,
		// 				type: 'hidden',
		// 			}));
		// 		}
		// 	}
		// }
	}

	setFormDataAdmin(formId) {
		this.buildFormDataItems([
			{
				name: this.state.getStateParam('settingsType'),
				value: this.state.getStateFormTypeSettings(formId),
				type: 'hidden',
			},
		]);
	}

	setFormDataPerType(formId) {
		const output = [];

		switch (this.state.getStateFormType(formId)) {
			case 'hubspot':
				output = [
					...output,
					{
						name: this.state.getStateParam('hubspotCookie'),
						value: cookies.getCookie('hubspotutk'),
						type: 'hidden',
					},
					{
						name: this.state.getStateParam('hubspotPageName'),
						value: document.title,
						type: 'hidden',
					},
					{
						name: this.state.getStateParam('hubspotPageUrl'),
						value: window.location.href,
						type: 'hidden',
					},
				];
				break;
		}

		this.buildFormDataItems(output);
	}

	buildFormDataItems(data, dataSet = this.FORM_DATA) {
		data.forEach((item) => {
			const {
				name,
				value,
				type,
			} = item;

			dataSet.append(name, JSON.stringify({
				name: name,
				value: value,
				type: type,
			}));
		});
	}

	////////////////////////////////////////////////////////////////
	// Fields
	////////////////////////////////////////////////////////////////

	/**
	 * Setup text field.
	 *
	 * @param {object} input Input element.
	 * @param {string} formId Form Id specific to one form.
	 *
	 * @public
	 */
	setupInputField(name, formId) {
		const input = this.state.getStateElementInput(name, formId);

		this.state.setState([this.state.ELEMENTS, name, this.state.LOADED], true, formId);

		this.utils.setFieldFilledState(name, formId);

		input.addEventListener('keydown', this.onFocusEvent);
		input.addEventListener('focus', this.onFocusEvent);
		input.addEventListener('blur', this.onBlurEvent);
		input.addEventListener('input', debounce(this.onInputEvent, 100));
	}

	/**
	 * Setup text field.
	 *
	 * @param {object} input Input element.
	 * @param {string} formId Form Id specific to one form.
	 *
	 * @public
	 */
	setupTelField(name, formId) {
		this.setupInputField(name, formId);

		if (this.state.getStateFormConfigPhoneDisablePicker(formId)) {
			this.state.getStateElementField(name, formId).querySelector('select').remove();
		} else {
			this.setupSelectField(name, formId);
		}
	}

	/**
	 * Setup radio field.
	 *
	 * @param {object} input Input element.
	 * @param {string} formId Form Id specific to one form.
	 *
	 * @public
	 */
	setupRadioCheckboxField(value, name, formId) {
		const input = this.state.getStateElementItemsInput(name, value, formId);

		this.state.setState([this.state.ELEMENTS, name, this.state.LOADED], true, formId);

		this.utils.setFieldFilledState(name, formId);

		input.addEventListener('keydown', this.onFocusEvent);
		input.addEventListener('focus', this.onFocusEvent);
		input.addEventListener('blur', this.onBlurEvent);
		input.addEventListener('input', this.onInputEvent);
	}

	/**
	 * Setup Date time field.
	 * 
	 * @param {object} date Input element.
	 * @param {string} formId Form Id specific to one form.
	 *
	 * @public
	 */
	setupDateField(name, formId) {
		const input = this.state.getStateElementInput(name, formId);

		import('flatpickr').then((flatpickr) => {
			const state = this.state;
			const utils = this.utils;
			const conditionalTags = this.conditionalTags;

			flatpickr.default(input, {
				enableTime: this.state.getStateElementInternalType(name, formId) === 'datetime',
				dateFormat: input.getAttribute(this.state.getStateAttribute('dateOutputFormat')),
				altFormat: input.getAttribute(this.state.getStateAttribute('datePreviewFormat')),
				altInput: true,
				onReady: function(selectedDates, value) {
					state.setState([state.ELEMENTS, name, state.LOADED], true, formId);
					state.setState([state.ELEMENTS, name, state.INITIAL], value, formId);
					state.setState([state.ELEMENTS, name, state.VALUE], value, formId);
					state.setState([state.ELEMENTS, name, state.CUSTOM], this, formId);

					utils.setFieldFilledState(name, formId);
				},
				onOpen: function () {
					utils.setFieldActiveState(name, formId);
				},
				onChange: function (selectedDates, value) {
					state.setState([state.ELEMENTS, name, state.VALUE], value, formId);

					utils.setFieldFilledState(name, formId);
					conditionalTags.setField(name, formId);
				},
			});
		});
	}

	/**
	 * Setup Select field.
	 * 
	 * @param {object} select Input element.
	 *
	 * @public
	 */
	setupSelectField(name, formId) {
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
				searchEnabled: this.state.getStateElementConfig(name, this.state.CONFIG_SELECT_USE_SEARCH, formId),
				shouldSort: false,
				position: 'bottom',
				allowHTML: true,
				duplicateItemsAllowed: false,
				placeholder: this.state.getStateElementConfig(name, this.state.CONFIG_SELECT_USE_PLACEHOLDER, formId),
				searchFields: ['label', 'value', 'customProperties'],
				itemSelectText: '',
				classNames: {
					containerOuter: `choices ${this.state.selectClassName}`,
				},
				callbackOnCreateTemplates: function() {
					return {
						option: (...args) => {
							const element = Choices.default.defaults.templates.option.call(this, ...args);
							const properties = args?.[0]?.customProperties;

							if (properties) {
								element.setAttribute(state.getStateAttribute('selectCustomProperties'), JSON.stringify(properties));
							}

							return element;
						},
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
					};
				},
			});

			const countryCookie = cookies.getCookie('esForms-country').toLocaleLowerCase();
			if (countryCookie) {
				choices.setChoiceByValue(countryCookie);
			}

			this.state.setState([this.state.ELEMENTS, name, this.state.LOADED], true, formId);
			this.state.setState([this.state.ELEMENTS, name, this.state.CUSTOM], choices, formId);

			choices.config.choices.map((item) => {
				this.state.setStateConditionalTagsItems(item.customProperties[this.state.getStateAttribute('conditionalTags')], name, item.value, formId);
			});

			choices.containerOuter.element.addEventListener('focus', this.onFocusEvent);
			choices.containerOuter.element.addEventListener('blur', this.onBlurEvent);
			choices.containerOuter.element.addEventListener('change', this.onSelectChangeEvent);
		});
	}

	/**
	 * Setup Textarea field.
	 * 
	 * @param {object} textarea Input element.
	 * @param {string} formId Form Id specific to one form.
	 *
	 * @public
	 */
	setupTextareaField(name, formId) {
		const input = this.state.getStateElementInput(name, formId);

		import('autosize').then((autosize) => {
			input.setAttribute('rows', '1');
			input.setAttribute('cols', '');

			autosize.default(input);
		});

		this.state.setState([this.state.ELEMENTS, name, this.state.LOADED], true, formId);

		this.utils.setFieldFilledState(name, formId);

		input.addEventListener('keydown', this.onFocusEvent);
		input.addEventListener('focus', this.onFocusEvent);
		input.addEventListener('blur', this.onBlurEvent);
		input.addEventListener('input', debounce(this.onInputEvent, 250));
	}

	/**
	 * Setup file single field.
	 * 
	 * @param {object} file Input element.
	 * @param {string} formId Form Id specific to one form.
	 * @param {number} index Loop index.
	 *
	 * @public
	 */
	setupFileField(name, formId) {
		const input = this.state.getStateElementInput(name, formId);
		const field = this.state.getStateElementField(name, formId);

		import('dropzone').then((Dropzone) => {
			const dropzone = new Dropzone.default(
				field,
				{
					url: this.state.getStateConfigSubmitUrl('-files'),
					addRemoveLinks: true,
					autoDiscover: false,
					parallelUploads: 1,
					maxFiles: !input.multiple ? 1 : null,
					dictRemoveFile: this.state.getStateSettingsFileRemoveLabel(formId),
				}
			);

			// Set data to internal state.
			this.state.setState([this.state.ELEMENTS, name, this.state.LOADED], true, formId);
			this.state.setState([this.state.ELEMENTS, name, this.state.CUSTOM], dropzone, formId);

			// On add one file add selectors for UX.
			dropzone.on("addedfile", (file) => {
				setTimeout(() => {
					file.previewTemplate.classList.add(this.state.getStateSelectorsClassActive());
				}, 200);

				setTimeout(() => {
					file.previewTemplate.classList.add(this.state.getStateSelectorsClassFilled());
				}, 1200);

				field.classList.remove(this.state.getStateSelectorsClassActive());

				this.state.setState([this.state.ELEMENTS, name, this.state.VALUE], 'true', formId);

				// Remove main filed validation error.
				this.utils.unsetFieldError(name, formId);
			});

			dropzone.on('removedfile', () => {
				const custom = this.state.getStateElementCustom(name, formId);

				if (custom.files.length === 0) {
					field.classList.remove(this.state.getStateSelectorsClassFilled());
				}

				field.classList.remove(this.state.getStateSelectorsClassActive());
				this.state.setState([this.state.ELEMENTS, name, this.state.VALUE], '', formId);
			})

			// Add data formData to the api call for the file upload.
			dropzone.on("sending", (file, xhr, formData) => {
				// Add common items like formID and type.
				this.buildFormDataItems([
					{
						name: this.state.getStateParam('postId'),
						value: formId,
						type: 'hidden',
					},
					{
						name: this.state.getStateParam('type'),
						value: 'fileUpload',
						type: 'hidden',
					},
					{
						// Add field name to know where whas this file upload to.
						name: this.state.getStateParam('name'),
						value: name,
						type: 'hidden',
					},
					{
						// Add file ID to know the file.
						name: this.state.getStateParam('fileId'),
						value: file?.upload?.uuid,
						type: 'hidden',
					},
				], formData);
			});

			// Once data is outputed from uplaod.
			dropzone.on("success", (file) => {
				const response = JSON.parse(file.xhr.response);

				// Output errors if ther is any.
				if (response?.data?.validation !== undefined) {
					const errorMsgOutput = file.previewTemplate.querySelector('.dz-error-message span');
					errorMsgOutput.innerHTML = response?.data?.validation?.[file?.upload?.uuid];

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
	 * @public
	 */
	removeEvents() {
		// const elements = document.querySelectorAll(this.data.formSelector);

		// [...elements].forEach((element) => {
		// 	// Regular submit.
		// 	element.removeEventListener('submit', this.onFormSubmitEvent);

		// 	const formId = element.getAttribute(this.state.getStateAttribute('formPostId'));

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

		// 	this.state.dispatchFormEvent(element, this.state.getStateEventsAfterFormsEventsClear());
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
	 * @public
	 */
	onFormSubmitEvent = (event) => {
		event.preventDefault();

		const formId = this.state.getFormIdByElement(event.target);

		this.utils.showLoader(formId);

		if (this.state.getStateCaptchaIsUsed()) {
			this.runFormCaptcha(formId);
		} else {
			debounce(this.formSubmit(formId), 100);
		}

		// const stepButton = event.submitter;

		// if (!this.steps.isMultiStepForm(formId)) {
		// if (false) {
		// 	// Loader show.
		// 	this.utils.showLoader(formId);

		// 	if (this.utils.isCaptchaUsed()) {
		// 		// Use captcha.
		// 		this.runFormCaptcha(formId);
		// 	} else {
		// 		// No captcha.
		// 		this.formSubmit(formId);
		// 	}
		// } else {
		// 	// if (this.steps.isStepTrigger(stepButton)) {
		// 	if (true) {
		// 		if (stepButton.getAttribute(this.data.DATA_ATTRIBUTES.submitdStepDirection) === this.steps.STEP_DIRECTION_PREV) {
		// 			// Just go back a step.
		// 			this.steps.goBackAStep(formId);
		// 		} else {
		// 			// Loader show.
		// 			this.utils.showLoader(formId);
	
		// 			// Submit for next.
		// 			this.formSubmit(formId, false, true);
		// 		}
		// 	} else {
		// 		// Loader show.
		// 		this.utils.showLoader(formId);

		// 		if (this.utils.isCaptchaUsed()) {
		// 			// Use captcha.
		// 			this.runFormCaptcha(formId);
		// 		} else {
		// 			// No captcha.
		// 			this.formSubmit(formId);
		// 		}
		// 	}
		// }
	};

	/**
	 * On custom file wrapper click event callback.
	 *
	 * @param {object} event Event callback.
	 *
	 * @public
	 */
	onFileWrapClickEvent = (event) => {
		event.preventDefault();
		event.stopPropagation();

		const field = this.state.getFormFieldElementByChild(event.target);

		this.state.getStateElementCustom(field.getAttribute(this.state.getStateAttribute('fieldName')), this.state.getFormIdByElement(event.target)).hiddenFileInput.click()

		field.classList.add(this.state.getStateSelectorsClassActive());
	};

	// On Focus event for regular fields.
	onFocusEvent = (event) => {
		this.utils.setFieldActiveState(
			this.state.getFormFieldElementByChild(event.target).getAttribute(this.state.getStateAttribute('fieldName')),
			this.state.getFormIdByElement(event.target)
		);
	};

	onSelectChangeEvent = (event) => {
		const field = this.state.getFormFieldElementByChild(event.target);
		const formId = this.state.getFormIdByElement(event.target);
		const type = field.getAttribute(this.state.getStateAttribute('fieldType'));
		const name = field.getAttribute(this.state.getStateAttribute('fieldName'));

		this.state.setValues(event.target, this.state.getFormIdByElement(event.target));

		if (!this.state.getStateFormConfigPhoneDisablePicker(formId) && this.state.getStateFormConfigPhoneUseSync(formId)) {
			if (type === 'country') {
				const country = this.state.getStateElementValueCountry(name, formId);
				[...this.state.getStateFilteredBykey(this.state.ELEMENTS, this.state.INTERNAL_TYPE, 'tel', formId)].forEach((tel) => {
					tel[this.state.CUSTOM].setChoiceByValue(country.number);
				});
			}

			if (type === 'phone') {
				const phone = this.state.getStateElementValueCountry(name, formId);
				[...this.state.getStateFilteredBykey(this.state.ELEMENTS, this.state.INTERNAL_TYPE, 'country', formId)].forEach((country) => {
					country[this.state.CUSTOM].setChoiceByValue(phone.label);
				});
			}
		}

		this.conditionalTags.setField(name, formId);

		if (this.state.getStateConfigIsAdmin() && this.state.getStateElementIsSingleSubmit(name, formId)) {
			debounce(
				this.formSubmit(
					formId, {
						useOnlyFields: [name]
					}
			), 100);
		}
	}

	onInputEvent = (event) => {
		const formId = this.state.getFormIdByElement(event.target);
		const field = this.state.getFormFieldElementByChild(event.target);
		const name = field.getAttribute(this.state.getStateAttribute('fieldName'));

		this.state.setValues(event.target, this.state.getFormIdByElement(event.target));

		this.conditionalTags.setField(name, formId);

		if (this.state.getStateConfigIsAdmin() && this.state.getStateElementIsSingleSubmit(name, formId)) {
			debounce(
				this.formSubmit(
					formId, {
						useOnlyFields: [name]
					}
			), 100);
		}
	}

	// On Blur generic method. Check for length of value.
	onBlurEvent = (event) => {
		const field = this.state.getFormFieldElementByChild(event.target);
		const name = field.getAttribute(this.state.getStateAttribute('fieldName'));
		const formId = this.state.getFormIdByElement(event.target);

		this.utils.setFieldFilledState(name, formId);
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
		this.state.setStateWindow();

		window[prefix].form = {}
		// window[prefix].form = {
		// 	initOnlyForms: () => {
		// 		this.initOnlyForms();
		// 	},
		// 	initOne: (element) => {
		// 		this.initOne(element);
		// 	},
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
		// };
	}
}
