/* global grecaptcha */

import { cookies, debounce } from '@eightshift/frontend-libs/scripts/helpers';
import selectManifest from './../../select/manifest.json';
import { StateEnum, prefix, setStateFormInitial, setStateWindow, removeStateForm } from './state-init';

/**
 * Main Forms class.
 */
export class Form {
	constructor(utils) {
		/** @type {import('./utils').Utils} */
		this.utils = utils;
		/** @type {import('./state').State} */
		this.state = this.utils.getState();
		/** @type {import('./enrichment').Enrichment} */
		this.enrichment = this.utils.getEnrichment();
		/** @type {import('./conditional-tags').ConditionalTags}*/
		this.conditionalTags = this.utils.getConditionalTags();
		/** @type {import('./steps').Steps}*/
		this.steps = this.utils.getSteps();
		/** @type {import('./geolocation').Geolocation}*/
		this.geolocation = this.utils.getGeolocation();

		this.FORM_DATA = new FormData();

		this.FILTER_IS_STEPS_FINAL_SUBMIT = 'isStepsFinalSubmit';
		this.FILTER_SKIP_FIELDS = 'skipFields';
		this.FILTER_USE_ONLY_FIELDS = 'useOnlyFields';
		this.GLOBAL_MSG_TIMEOUT_ID = undefined;

		// Set all public methods.
		this.publicMethods();
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
		// Init all forms.
		this.initOnlyForms();

		// Init enrichment.
		this.enrichment.init();
	}

	/**
	 * Init only forms.
	 *
	 * @returns {void}
	 */
	initOnlyForms() {
		if (this.state.getStateConfigIsAdmin()) {
			// If is admin do normal init.
			this.initOnlyFormsInner(document.querySelector(this.state.getStateSelector('form', true))?.getAttribute(this.state.getStateAttribute('formId')) || '0');
		} else {
			// Find all forms elements.
			const forms = document.querySelectorAll(this.state.getStateSelector('forms', true));

			if (!forms.length) {
				this.utils.removeFormsWithMissingFormsBlock();

				return;
			}

			[...forms].forEach((formsItems) => {
				// Find all forms elements that have have geolocation data attribute.
				if (formsItems?.getAttribute(this.state.getStateAttribute('formGeolocation'))) {
					// If forms element have geolocation data attribute, init geolocation via ajax.
					this.initGolocationForm(formsItems);
				} else {
					const formId = formsItems?.querySelector(this.state.getStateSelector('form', true))?.getAttribute(this.state.getStateAttribute('formId')) || '0';

					// Bailout if 0 as formId === 0 can only be used in admin.
					if (formId === '0') {
						throw new Error(
							`It looks like we can't find formId for your form, please check if you have set the attribute "${this.state.getStateAttribute('formId')}" on the form element.`,
						);
					}

					// If forms element don't have geolocation data attribute, init forms the regular way.
					this.initOnlyFormsInner(formId);
				}
			});
		}
	}

	/**
	 * Init only geolocation forms by ajax.
	 * @param {object} formsElement Forms element.
	 */
	initGolocationForm(formsElement) {
		// If you have geolocation configured on the form but global setting is turned off. Return first form.
		if (!this.state.getStateGeolocationIsUsed()) {
			const formId = formsElement?.querySelector(this.state.getStateSelector('form', true))?.getAttribute(this.state.getStateAttribute('formId')) || '0';

			this.initOnlyFormsInner(formId);

			// Remove geolocation data attribute from forms element.
			formsElement.removeAttribute(this.state.getStateAttribute('formGeolocation'));

			// Remove loading class from forms element.
			formsElement?.classList?.remove(this.state.getStateSelector('isGeoLoading'));

			return;
		}

		const forms = formsElement?.querySelectorAll(this.state.getStateSelector('form', true));

		const formData = new FormData();

		formData.append('data', formsElement?.getAttribute(this.state.getStateAttribute('formGeolocation')));

		const body = {
			method: 'POST',
			mode: 'same-origin',
			headers: {
				Accept: 'application/json',
			},
			body: formData,
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		// Get geolocation data from ajax to detect what we will remove from DOM.
		fetch(this.state.getRestUrl('geolocation'), body)
			.then((response) => {
				this.utils.formSubmitErrorContentType(response, 'geolocation', null);

				return response.text();
			})
			.then((responseData) => {
				const response = this.utils.formSubmitIsJsonString(responseData, 'geolocation', null)?.data;

				// Loop all form elements and remove all except the one we need.
				[...forms].forEach((form) => {
					if (form.getAttribute(this.state.getStateAttribute('formFid')) !== response?.[this.state.getStateResponseOutputKey('geoId')]) {
						// Remove all forms except the one we got from ajax.
						form.remove();
					} else {
						// Init form id that we got from ajax.
						this.initOnlyFormsInner(form.getAttribute(this.state.getStateAttribute('formId')));

						// Remove geolocation data attribute from forms element.
						formsElement.removeAttribute(this.state.getStateAttribute('formGeolocation'));
					}
				});

				// Remove loading class from forms element.
				formsElement?.classList?.remove(this.state.getStateSelector('isGeoLoading'));
			});
	}

	/**
	 * Init only forms - inner items.
	 *
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	initOnlyFormsInner(formId) {
		// Set state initial data for form.
		setStateFormInitial(formId);

		// Init all form elements.
		this.initOne(formId);

		// Order is important here due to logic of prefilling the form!

		// Init geolocation.
		this.geolocation.initOne(formId);

		// Init enrichment prefill.
		this.enrichment.setLocalStorageFormPrefill(formId);
		this.enrichment.setUrlParamsFormPrefill(formId);

		// Init conditional tags.
		this.conditionalTags.initOne(formId);

		// Init steps.
		this.steps.initOne(formId);
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

		// Select.
		[...this.state.getStateElementByTypeField('select', formId), ...this.state.getStateElementByTypeField('country', formId)].forEach((select) => {
			this.setupSelectField(formId, select.name);
		});

		// File.
		[...this.state.getStateElementByTypeField('file', formId)].forEach((file) => {
			this.setupFileField(formId, file.name);
		});

		// Textarea.
		[...this.state.getStateElementByTypeField('textarea', formId)].forEach((textarea) => {
			this.setupTextareaField(formId, textarea.name);
		});

		// Text.
		[...this.state.getStateElementByTypeField('input', formId)].forEach((input) => {
			this.setupInputField(formId, input.name);
		});

		// Range.
		[...this.state.getStateElementByTypeField('range', formId)].forEach((range) => {
			this.setupRangeField(formId, range.name);
		});

		// Date.
		[...this.state.getStateElementByTypeField('date', formId), ...this.state.getStateElementByTypeField('dateTime', formId)].forEach((date) => {
			this.setupDateField(formId, date.name);
		});

		// Phone.
		[...this.state.getStateElementByTypeField('phone', formId)].forEach((phone) => {
			this.setupPhoneField(formId, phone.name);
		});

		// Checkbox.
		[...this.state.getStateElementByTypeField('checkbox', formId)].forEach((checkbox) => {
			[...Object.values(checkbox.items)].forEach((checkboxItem) => {
				this.setupRadioCheckboxField(formId, checkboxItem.value, checkboxItem.name);
			});
		});

		// Radio.
		[...this.state.getStateElementByTypeField('radio', formId)].forEach((radio) => {
			[...Object.values(radio.items)].forEach((radioItem) => {
				this.setupRadioCheckboxField(formId, radioItem.value, radioItem.name);
			});
		});

		// Rating.
		[...this.state.getStateElementByTypeField('rating', formId)].forEach((rating) => {
			[...Object.values(rating.items)].forEach((ratingItem) => {
				this.setupRatingField(formId, ratingItem.value, ratingItem.name);
			});
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
		this.state.setStateFormIsProcessing(true, formId);

		// Dispatch event.
		this.utils.dispatchFormEventForm(this.state.getStateEvent('beforeFormSubmit'), formId);

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
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		// Url for frontend forms.
		let url = this.state.getRestUrlByType('prefixSubmit', formType);

		// For admin settings use different url and add nonce.
		if (this.state.getStateConfigIsAdmin()) {
			url = this.state.getRestUrl('settings');
		}

		// Add nonce for frontend and admin.
		const nonce = this.state.getStateConfigNonce();

		if (nonce) {
			body.headers['X-WP-Nonce'] = nonce;
		}

		fetch(url, body)
			.then((response) => {
				this.utils.formSubmitErrorContentType(response, 'formSubmit', formId);

				return response.text();
			})
			.then((responseData) => {
				const response = this.utils.formSubmitIsJsonString(responseData, 'formSubmit', formId);

				this.formSubmitBefore(formId, response);

				// On success state.
				if (response.status === 'success') {
					this.formSubmitSuccess(formId, response, filter?.[this.FILTER_IS_STEPS_FINAL_SUBMIT]);
				} else {
					this.formSubmitError(formId, response, filter?.[this.FILTER_IS_STEPS_FINAL_SUBMIT]);
				}

				this.formSubmitAfter(formId, response);

				this.state.setStateFormIsProcessing(false, formId);
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
		this.state.setStateFormIsProcessing(true, formId);
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
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		const url = this.state.getRestUrl('validationStep');

		fetch(url, body)
			.then((response) => {
				this.utils.formSubmitErrorContentType(response, 'formSubmitStep', formId);

				return response.text();
			})
			.then((responseData) => {
				const response = this.utils.formSubmitIsJsonString(responseData, 'formSubmitStep', formId);

				this.formSubmitBefore(formId, response);
				this.steps.formStepSubmit(formId, response);
				this.steps.formStepSubmitAfter(formId, response);
				this.state.setStateFormIsProcessing(false, formId);
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
		this.utils.dispatchFormEventForm(this.state.getStateEvent('afterFormSubmit'), formId, response);

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
		const { status, message, data } = response;

		this.utils.dispatchFormEventForm(this.state.getStateEvent('afterFormSubmitSuccess'), formId, response);

		if (this.state.getStateConfigIsAdmin()) {
			// Set global msg.
			this.utils.setGlobalMsg(formId, message, status, data);

			if (this.state.getStateFormIsAdminSingleSubmit(formId)) {
				this.utils.redirectToUrlByReference(formId, window.location.href, true);
			}
		} else {
			// Send GTM.
			this.utils.gtmSubmit(formId, status, data);

			if (data?.[this.state.getStateResponseOutputKey('successRedirectUrl')]) {
				// Remove local storage for prefill.
				if (this.state.getStateEnrichmentIsUsed()) {
					this.enrichment.deleteLocalStorage(this.state.getStateEnrichmentFormPrefillStorageName(formId));
				}

				// Redirect to url and update url params from from data.
				this.utils.redirectToUrlByReference(formId, data?.[this.state.getStateResponseOutputKey('successRedirectUrl')]);
			} else {
				// Clear form values.
				this.utils.resetForm(formId);

				// Set global msg.
				this.utils.setGlobalMsg(formId, message, status, data);

				// Hide form on success.
				if (data?.[this.state.getStateResponseOutputKey('hideFormOnSuccess')]) {
					this.state.getStateFormElement(formId).classList.add(this.state.getStateSelector('isHidden'));
				}

				// This will be changed in the next release.
				if (Boolean(this.state.getStateFormElement(formId)?.getAttribute(this.state.getStateAttribute('formHideOnSuccess')))) {
					this.state.getStateFormElement(formId).classList.add(this.state.getStateSelector('isHidden'));
				}

				// Remove local storage for prefill.
				if (this.state.getStateEnrichmentIsUsed()) {
					this.enrichment.deleteLocalStorage(this.state.getStateEnrichmentFormPrefillStorageName(formId));
				}

				// Return to original first step.
				if (isFinalStep) {
					this.steps.resetSteps(formId);
				}

				// Set output results.
				this.utils.setResultsOutput(formId, data);

				// Process payment gateways and external.
				if (data?.[this.state.getStateResponseOutputKey('processExternally')]) {
					import('./payment-gateways').then(({ PaymentGateways }) => {
						new PaymentGateways({
							utils: this.utils,
							state: this.state,
							response: data?.[this.state.getStateResponseOutputKey('processExternally')],
						}).init(formId);
					});
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
		const { status, message, data } = response;

		this.utils.dispatchFormEventForm(this.state.getStateEvent('afterFormSubmitError'), formId, response);

		const validationOutputKey = this.state.getStateResponseOutputKey('validation');

		this.utils.setGlobalMsg(formId, message, status, data);

		this.utils.gtmSubmit(formId, status, data);

		// Dispatch event.
		if (data?.[validationOutputKey] !== undefined) {
			this.utils.dispatchFormEventForm(this.state.getStateEvent('afterFormSubmitErrorValidation'), formId, response);

			this.utils.outputErrors(formId, data?.[validationOutputKey]);

			if (isFinalStep) {
				this.steps.goToStepWithError(formId, data?.[validationOutputKey]);
			}
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
		if (typeof this.GLOBAL_MSG_TIMEOUT_ID === 'number') {
			clearTimeout(this.GLOBAL_MSG_TIMEOUT_ID);
		}

		// Hide global msg in any case after some time.
		this.GLOBAL_MSG_TIMEOUT_ID = setTimeout(
			() => {
				this.utils.unsetGlobalMsg(formId);
			},
			parseInt(this.state.getStateSettingsHideGlobalMessageTimeout(formId), 10),
		);

		// Dispatch event.
		this.utils.dispatchFormEventForm(this.state.getStateEvent('afterFormSubmitEnd'), formId, response);
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
			grecaptcha?.enterprise?.ready(async () => {
				try {
					const token = await grecaptcha?.enterprise?.execute(siteKey, { action: actionName });

					this.setFormDataCaptcha({
						token,
						isEnterprise: true,
						action: actionName,
					});

					this.formSubmit(formId, filter);
				} catch (error) {
					this.utils.formSubmitErrorFatal(this.state.getStateSettingsFormCaptchaErrorMsg(), 'runFormCaptcha', error, formId);
				}
			});
		} else {
			grecaptcha?.ready(async () => {
				try {
					const token = await grecaptcha?.execute(siteKey, { action: actionName });

					this.setFormDataCaptcha({
						token,
						isEnterprise: false,
						action: actionName,
					});

					this.formSubmit(formId, filter);
				} catch (error) {
					this.utils.formSubmitErrorFatal(this.state.getStateSettingsFormCaptchaErrorMsg(), 'runFormCaptcha', error, formId);
				}
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
		this.state.setStateFormIsAdminSingleSubmit(Boolean(useOnlyFields.length), formId);

		// Used for group submit.
		const skipFields = filter?.[this.FILTER_SKIP_FIELDS] ?? [];

		const fieldsetOtherOutput = [];

		// Iterate all form items.
		for (const [key] of this.state.getStateElements(formId)) {
			const name = key;
			const internalType = this.state.getStateElementTypeField(key, formId);
			const value = this.state.getStateElementValue(key, formId);
			const typeCustom = this.state.getStateElementTypeCustom(key, formId);
			const saveAsJson = this.state.getStateElementSaveAsJson(key, formId);
			const items = this.state.getStateElementItems(key, formId);
			const field = this.state.getStateElementField(key, formId);
			const disabled = this.state.getStateElementIsDisabled(key, formId);

			// Skip select search field.
			if (name === 'search_terms') {
				continue;
			}

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
					let indexCheck = 0;

					for (const [checkName, checkValue] of Object.entries(value)) {
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
				case 'rating':
					let indexRadio = 0;

					for (const [radioName, radioValue] of Object.entries(items)) {
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
				case 'phone':
					if (disabled) {
						break;
					}

					data.value = this.utils.getPhoneCombinedValue(formId, name);

					this.FORM_DATA.append(name, JSON.stringify(data));
					break;
				case 'file':
					if (disabled) {
						break;
					}

					// If custom file use files got from the global object of files uploaded.
					const fileList = this.state.getStateElementCustom(name, formId)?.files ?? [];

					// Loop files and append.
					if (fileList.length) {
						for (const [key, file] of Object.entries(fileList)) {
							data.value = this.utils.getFileNameFromFileObject(file);
							this.FORM_DATA.append(`${name}[${key}]`, JSON.stringify(data));
						}
					} else {
						this.FORM_DATA.append(`${name}[0]`, JSON.stringify(data));
					}
					break;
				case 'input':
					if (disabled) {
						break;
					}

					const fieldset = field.closest('fieldset');

					// If we have input on the checkbox/radio fieldset don't sent the input value but append it to the parent fieldset.
					if (fieldset?.getAttribute(this.state.getStateAttribute('fieldType')) === 'checkbox' || fieldset?.getAttribute(this.state.getStateAttribute('fieldType')) === 'radio') {
						if (value !== '') {
							fieldsetOtherOutput.push({
								name,
								parent: fieldset?.getAttribute(this.state.getStateAttribute('fieldName')),
								value,
							});
						}
						break;
					}

					this.FORM_DATA.append(name, JSON.stringify(data));
					break;
				default:
					if (disabled) {
						break;
					}

					this.FORM_DATA.append(name, JSON.stringify(data));
					break;
			}
		}

		// Used in case we have req fields with conditional tags and multiple steps.
		if (skipFields) {
			const skipFieldsOutput = {};
			skipFields.forEach((skipField) => {
				skipFieldsOutput[skipField] = this.state.getStateElementValue(skipField, formId);
			});

			this.FORM_DATA.append(
				this.state.getStateParam('skippedParams'),
				JSON.stringify({
					name: this.state.getStateParam('skippedParams'),
					value: skipFieldsOutput,
					type: 'hidden',
					typeCustom: 'hidden',
					custom: '',
					innerName: '',
				}),
			);
		}

		// If we have input on the checkbox/radio fieldset don't sent the input value but append it to the parent fieldset.
		if (fieldsetOtherOutput.length) {
			fieldsetOtherOutput.forEach((item, index) => {
				const items = Object.keys(this.state.getStateElementItems(item.parent, formId))?.length;
				const { parent, value } = item;

				this.FORM_DATA.append(
					`${parent}[${items + index}]`,
					JSON.stringify({
						name: parent,
						value: value,
						type: this.state.getStateElementTypeField(parent, formId),
						typeCustom: this.state.getStateElementTypeCustom(parent, formId),
						custom: '',
						innerName: '',
					}),
				);
			});
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
		const groups = this.state.getStateFormElement(formId).querySelectorAll(`${this.state.getStateSelector('group', true)}`);

		// Check if we are saving group items in one key.
		if (!groups.length) {
			return output;
		}

		for (const [key, group] of Object.entries(groups)) {
			const groupSaveAsOneField = Boolean(group.getAttribute(this.state.getStateAttribute('groupSaveAsOneField')));

			if (!groupSaveAsOneField) {
				continue;
			}

			const groupInner = group.querySelectorAll('input, select, textarea');

			if (!groupInner.length) {
				continue;
			}

			const groupInnerItems = {};

			for (const [key, groupInnerItem] of Object.entries(groupInner)) {
				const { name, value, disabled } = groupInnerItem;

				// Skip select search field.
				if (name === 'search_terms') {
					continue;
				}

				if (disabled) {
					continue;
				}

				groupInnerItems[name] = value;
				output.push(name);
			}

			const groupId = group.getAttribute(this.state.getStateAttribute('fieldId'));

			if (groupId) {
				this.FORM_DATA.append(
					groupId,
					JSON.stringify({
						name: groupId,
						value: groupInnerItems,
						type: 'group',
						typeCustom: 'group',
						custom: '',
					}),
				);
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
				value: this.state.getStateFormFid(formId),
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
			{
				name: this.state.getStateParam('secureData'),
				value: this.state.getStateFormSecureData(formId),
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

		const data = this.enrichment.getLocalStorage(this.state.getStateEnrichmentStorageName());

		if (data) {
			this.buildFormDataItems([
				{
					name: this.state.getStateParam('storage'),
					value: data,
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
			const { name, value, type = 'hidden', typeCustom = 'hidden', custom = '' } = item;

			dataSet.append(
				name,
				JSON.stringify({
					name,
					value,
					type,
					typeCustom,
					custom,
				}),
			);
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
		input.addEventListener('keydown', this.onKeyDownEvent);

		if (
			(this.state.getStateConfigIsAdmin() && this.state.getStateElementIsSingleSubmit(name, formId)) ||
			(this.state.getStateFormConfigUseSingleSubmit(formId) && this.state.getStateElementTypeCustom(name, formId) === 'number')
		) {
			input.addEventListener('input', debounce(this.onInputEvent, 300));
		} else {
			input.addEventListener('input', this.onInputEvent);
		}
	}

	/**
	 * Setup range field.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	setupRangeField(formId, name) {
		const input = this.state.getStateElementInput(name, formId);
		const custom = this.state.getStateElementCustom(name, formId);

		this.state.setStateElementLoaded(name, true, formId);

		this.utils.setFieldFilledState(formId, name);

		input.addEventListener('keydown', this.onFocusEvent);
		input.addEventListener('focus', this.onFocusEvent);
		input.addEventListener('blur', this.onBlurEvent);
		input.addEventListener('keydown', this.onKeyDownEvent);

		this.utils.setRangeCurrentValue(formId, name);

		if ((this.state.getStateConfigIsAdmin() && this.state.getStateElementIsSingleSubmit(name, formId)) || this.state.getStateFormConfigUseSingleSubmit(formId)) {
			input.addEventListener('input', debounce(this.onInputEvent, 300));
		} else {
			input.addEventListener('input', this.onInputEvent);
		}

		if (custom) {
			custom.addEventListener('input', this.onRangeCustom);
		}
	}

	/**
	 * Setup rating field.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	setupRatingField(formId, value, name) {
		this.setupRadioCheckboxField(formId, value, name);
	}

	/**
	 * Setup phone field.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	setupPhoneField(formId, name) {
		this.setupInputField(formId, name);

		if (this.state.getStateFormConfigPhoneDisablePicker(formId)) {
			this.state.getStateElementField(name, formId).querySelector('select')?.remove();
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
		const state = this.state;
		const utils = this.utils;

		const input = state.getStateElementInput(name, formId);

		import('flatpickr').then((flatpickr) => {
			flatpickr.default(input, {
				enableTime: state.getStateElementTypeField(name, formId) === 'dateTime',
				dateFormat: input.getAttribute(state.getStateAttribute('dateOutputFormat')),
				altFormat: input.getAttribute(state.getStateAttribute('datePreviewFormat')),
				altInput: true,
				onReady: function (selectedDates, value, instance) {
					const id = instance.element.id;
					instance.element.setAttribute('tabindex', '-1');
					instance.element.setAttribute('role', 'group');
					instance.element.setAttribute('aria-hidden', 'true');
					instance.element.removeAttribute('id');
					instance.altInput.setAttribute('id', id);

					state.setStateElementInitial(name, value, formId);
					state.setStateElementLoaded(name, true, formId);
					state.setStateElementValue(name, value, formId);
					state.setStateElementCustom(name, this, formId);

					utils.setFieldFilledState(formId, name);
				},
				onOpen: function (selectedDates, dateStr, instance) {
					utils.setActiveState(formId, name);
					instance?.altInput?.scrollIntoView({ behavior: 'smooth' });
				},
				onClose: function () {
					utils.unsetActiveState(formId, name);
				},
				onChange: function (selectedDates, dateStr) {
					utils.setManualDateValue(formId, name, dateStr, false, false);
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
		import('choices.js').then((Choices) => {
			let input = this.state.getStateElementInput(name, formId);
			const typeInternal = this.state.getStateElementTypeField(name, formId);
			const labels = this.state.getStateSettingsLabels();

			if (typeInternal === 'phone') {
				input = this.state.getStateElementInputSelect(name, formId);
			}

			const customProperties = [
				this.state.getStateAttribute('selectCountryCode'),
				this.state.getStateAttribute('selectCountryLabel'),
				this.state.getStateAttribute('selectCountryNumber'),
				this.state.getStateAttribute('conditionalTags'),
				this.state.getStateAttribute('selectOptionIsHidden'),
			];

			const choices = new Choices.default(input, {
				searchEnabled: this.state.getStateElementConfig(name, StateEnum.CONFIG_SELECT_USE_SEARCH, formId),
				shouldSort: false,
				position: 'bottom',
				allowHTML: true,
				searchResultLimit: 50,
				removeItemButton: typeInternal !== 'phone', // Phone should not be able to remove prefix!
				duplicateItemsAllowed: false,
				searchFields: [
					'label',
					'value',
					`customProperties.${this.state.getStateAttribute('selectCountryCode')}`,
					`customProperties.${this.state.getStateAttribute('selectCountryLabel')}`,
					`customProperties.${this.state.getStateAttribute('selectCountryNumber')}`,
				],
				itemSelectText: '',
				classNames: {
					containerOuter: ['choices', `${selectManifest.componentClass}`],
				},
				callbackOnCreateTemplates: function () {
					return {
						// Dropdown items.
						choice: (...args) => {
							const element = Choices.default.defaults.templates.choice.call(this, ...args);

							customProperties.forEach((property) => {
								const attr = args?.[1]?.element?.getAttribute(property);

								if (attr) {
									element.setAttribute(property, attr);
								}
							});

							return element;
						},
						// Selected item.
						item: (...args) => {
							const element = Choices.default.defaults.templates.item.call(this, ...args);

							element.setAttribute('aria-label', labels?.selectOptionAria);

							customProperties.forEach((property) => {
								const attr = args?.[1]?.element?.getAttribute(property);

								if (attr) {
									element.setAttribute(property, attr);
								}
							});

							return element;
						},
					};
				},
			});

			this.state.setStateElementLoaded(name, true, formId);
			this.state.setStateElementCustom(name, choices, formId);

			this.utils.setFieldFilledState(formId, name);

			choices?.passedElement?.element.addEventListener('change', this.onSelectChangeEvent);
			choices?.containerOuter?.element.addEventListener('focus', this.onSelectFocusEvent);
			choices?.containerOuter?.element.addEventListener('blur', this.onBlurEvent);
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

			this.state.setStateElementCustom(name, autosize?.default, formId);

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
		import('dropzone').then((Dropzone) => {
			const input = this.state.getStateElementInput(name, formId);
			const field = this.state.getStateElementField(name, formId);
			const button = this.state.getStateElementFileButton(name, formId);
			const labels = this.state.getStateSettingsLabels();

			// Prevent double init.
			if (field.dropzone) {
				return;
			}

			const dropzone = new Dropzone.default(field, {
				url: this.state.getRestUrl('files'),
				addRemoveLinks: true,
				autoDiscover: false,
				parallelUploads: 1,
				maxFiles: !input.multiple ? 1 : null,
				dictMaxFilesExceeded: '',
				dictRemoveFile: labels?.fileRemoveContent,
			});

			// Set data to internal state.
			this.state.setStateElementLoaded(name, true, formId);
			this.state.setStateElementCustom(name, dropzone, formId);

			// On add one file add selectors for UX.
			dropzone.on('addedfile', (file) => {
				file.previewTemplate.querySelector('.dz-remove').setAttribute('aria-label', labels?.fileRemoveAria);

				setTimeout(() => {
					file?.previewTemplate?.classList?.add(this.state.getStateSelector('isActive'));
				}, 200);

				setTimeout(() => {
					file?.previewTemplate?.classList?.add(this.state.getStateSelector('isFilled'));
				}, 1200);

				field?.classList?.remove(this.state.getStateSelector('isActive'));

				// Remove main filed validation error.
				this.utils.unsetFieldError(formId, name);
			});

			dropzone.on('removedfile', () => {
				const custom = this.state.getStateElementCustom(name, formId);

				if (custom.files.length === 0) {
					field?.classList?.remove(this.state.getStateSelector('isFilled'));
				}

				field?.classList?.remove(this.state.getStateSelector('isActive'));

				// Remove main filed validation error.
				this.utils.unsetFieldError(formId, name);

				button.focus();
				this.utils.setOnFocus(button);
			});

			// Add data formData to the api call for the file upload.
			dropzone.on('sending', (file, xhr, formData) => {
				// Add common items like formID and type.
				this.buildFormDataItems(
					[
						{
							name: this.state.getStateParam('formId'),
							value: this.state.getStateFormFid(formId),
						},
						{
							name: this.state.getStateParam('postId'),
							value: this.state.getStateFormPostId(formId),
						},
						{
							name: this.state.getStateParam('type'),
							value: this.state.getStateConfigIsAdmin() ? 'fileUploadAdmin' : 'fileUpload',
						},
						{
							// Add field name to know where was this file uploaded to.
							name: this.state.getStateParam('name'),
							value: name,
						},
						{
							// Add file ID to know the file.
							name: this.state.getStateParam('fileId'),
							value: file?.upload?.uuid,
						},
					],
					formData,
				);
			});

			// Once data is outputed from uplaod.
			dropzone.on('success', (file) => {
				try {
					const response = JSON.parse(file.xhr.response);

					const validationOutputKey = this.state.getStateResponseOutputKey('validation');

					// Output errors if there are any.
					if (typeof response?.data?.[validationOutputKey] !== 'undefined' && Object.keys(response?.data?.[validationOutputKey])?.length > 0) {
						file.previewTemplate.querySelector('.dz-error-message span').innerHTML = response?.data?.[validationOutputKey]?.[file?.upload?.uuid];
					}

					field?.classList?.add(this.state.getStateSelector('isFilled'));

					button.focus();
					this.utils.setOnFocus(button);
				} catch (e) {
					file.previewTemplate.querySelector('.dz-error-message span').innerHTML = this.state.getStateSettingsFormServerErrorMsg();

					throw new Error(`API response returned JSON but it was malformed for this request. Function used: "fileUploadSuccess"`);
				}
			});

			dropzone.on('error', (file) => {
				const { response, status } = file.xhr;

				let msg = 'serverError';

				if (response.includes('wordfence') || response.includes('Wordfence')) {
					msg = 'wordfenceFirewall';
				}

				if (response.includes('cloudflare') || response.includes('Cloudflare')) {
					msg = 'cloudflareFirewall';
				}

				file.previewTemplate.querySelector('.dz-error-message span').innerHTML = this.state.getStateSettingsFormServerErrorMsg();

				button.focus();
				this.utils.setOnFocus(button);

				throw new Error(`API response returned JSON but it was malformed for this request. Function used: "fileUploadError" with code: "${status}" and message: "${msg}"`);
			});

			// Trigger on wrap click.
			button.addEventListener('click', this.onFileWrapClickEvent);
			button.addEventListener('focus', this.onFocusEvent);
			button.addEventListener('blur', this.onBlurEvent);
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
		const formIds = this.state.getStateForms();

		// Bailout if no forms.
		if (!formIds?.length) {
			return;
		}

		// Clear form state only.
		[...formIds].forEach((formId) => {
			this.state.getStateFormElement(formId)?.removeEventListener('submit', this.onFormSubmitEvent);

			// Select.
			[...this.state.getStateElementByTypeField('select', formId), ...this.state.getStateElementByTypeField('country', formId)].forEach((select) => {
				const choices = this.state.getStateElementCustom(select.name, formId);

				choices?.passedElement?.element?.removeEventListener('change', this.onSelectChangeEvent);
				choices?.containerOuter?.element.removeEventListener('focus', this.onFocusEvent);
				choices?.containerOuter?.element.removeEventListener('blur', this.onBlurEvent);
				choices?.destroy();
			});

			// File.
			[...this.state.getStateElementByTypeField('file', formId)].forEach((file) => {
				const button = this.state.getStateElementFileButton(select.name, formId);

				this.state.getStateElementCustom(file.name, formId)?.destroy();
				button?.removeEventListener('click', this.onFileWrapClickEvent);
				button?.removeEventListener('focus', this.onFocusEvent);
				button?.removeEventListener('blur', this.onBlurEvent);
			});

			// Textarea.
			[...this.state.getStateElementByTypeField('textarea', formId)].forEach((textarea) => {
				const input = this.state.getStateElementInput(textarea.name, formId);

				this.state.getStateElementCustom(textarea.name, formId)?.destroy(input);
				input?.removeEventListener('keydown', this.onFocusEvent);
				input?.removeEventListener('focus', this.onFocusEvent);
				input?.removeEventListener('blur', this.onBlurEvent);
				input?.removeEventListener('input', this.onInputEvent);
			});

			// Text.
			[...this.state.getStateElementByTypeField('input', formId)].forEach((text) => {
				const input = this.state.getStateElementInput(text.name, formId);

				input?.removeEventListener('keydown', this.onFocusEvent);
				input?.removeEventListener('focus', this.onFocusEvent);
				input?.removeEventListener('blur', this.onBlurEvent);
				input?.removeEventListener('input', this.onInputEvent);
				input?.removeEventListener('keydown', this.onKeyDownEvent);
			});

			// Range.
			[...this.state.getStateElementByTypeField('range', formId)].forEach((range) => {
				const input = this.state.getStateElementInput(range.name, formId);
				const custom = this.state.getStateElementCustom(range.name, formId);

				input?.removeEventListener('keydown', this.onFocusEvent);
				input?.removeEventListener('focus', this.onFocusEvent);
				input?.removeEventListener('blur', this.onBlurEvent);
				input?.removeEventListener('input', this.onInputEvent);
				input?.removeEventListener('keydown', this.onKeyDownEvent);
				custom?.addEventListener('input', this.onRangeCustom);
			});

			// Date.
			[...this.state.getStateElementByTypeField('date', formId), ...this.state.getStateElementByTypeField('dateTime', formId)].forEach((date) => {
				this.state.getStateElementCustom(date.name, formId)?.destroy();
			});

			// Phone.
			[...this.state.getStateElementByTypeField('phone', formId)].forEach((phone) => {
				this.state.getStateElementCustom(phone.name, formId)?.destroy();

				const input = this.state.getStateElementInput(phone.name, formId);

				input?.removeEventListener('keydown', this.onFocusEvent);
				input?.removeEventListener('focus', this.onFocusEvent);
				input?.removeEventListener('blur', this.onBlurEvent);
				input?.removeEventListener('input', this.onInputEvent);
			});

			// Checkbox.
			[...this.state.getStateElementByTypeField('checkbox', formId)].forEach((checkbox) => {
				[...Object.values(checkbox.items)].forEach((checkboxItem) => {
					const input = this.state.getStateElementItemsInput(checkboxItem.name, checkboxItem.value, formId);

					input?.removeEventListener('keydown', this.onFocusEvent);
					input?.removeEventListener('focus', this.onFocusEvent);
					input?.removeEventListener('blur', this.onBlurEvent);
					input?.removeEventListener('input', this.onInputEvent);
				});
			});

			// Radio.
			[...this.state.getStateElementByTypeField('radio', formId)].forEach((radio) => {
				[...Object.values(radio.items)].forEach((radioItem) => {
					const input = this.state.getStateElementItemsInput(radioItem.name, radioItem.value, formId);

					input?.removeEventListener('keydown', this.onFocusEvent);
					input?.removeEventListener('focus', this.onFocusEvent);
					input?.removeEventListener('blur', this.onBlurEvent);
					input?.removeEventListener('input', this.onInputEvent);
				});
			});

			// Rating.
			[...this.state.getStateElementByTypeField('rating', formId)].forEach((rating) => {
				[...Object.values(rating.items)].forEach((ratingItem) => {
					const input = this.state.getStateElementItemsInput(ratingItem.name, ratingItem.value, formId);

					input?.removeEventListener('keydown', this.onFocusEvent);
					input?.removeEventListener('focus', this.onFocusEvent);
					input?.removeEventListener('blur', this.onBlurEvent);
					input?.removeEventListener('input', this.onInputEvent);
				});
			});

			// Remove Enrichment.
			this.enrichment.removeEvents(formId);

			// Remove conditional tags.
			this.conditionalTags.removeEvents(formId);

			// Remove steps.
			this.steps.removeEvents(formId);

			removeStateForm(formId);
		});
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

		// Prevent multiple submits.
		if (this.state.getStateFormIsProcessing(formId)) {
			return;
		}

		if (this.state.getStateFormStepsIsUsed(formId)) {
			const button = event?.submitter;
			const field = this.state.getFormFieldElementByChild(button);

			// Steps flow.
			let direction = field.getAttribute(this.state.getStateAttribute('submitStepDirection'));

			// If button is hidden prevent submiting the form.
			if (field?.classList?.contains(this.state.getStateSelector('isHidden'))) {
				return;
			}

			switch (direction) {
				case this.steps.STEP_DIRECTION_NEXT:
					this.utils.showLoader(formId);

					const filterNext = {
						[this.FILTER_SKIP_FIELDS]: [...this.conditionalTags.getIgnoreFields(formId)],
					};

					debounce(this.formSubmitStep(formId, filterNext), 100);
					break;
				case this.steps.STEP_DIRECTION_PREV:
					this.steps.goToPrevStep(formId);
					break;
				default:
					this.utils.showLoader(formId);

					const filterFinal = {
						[this.FILTER_SKIP_FIELDS]: [...this.steps.getIgnoreFields(formId), ...this.conditionalTags.getIgnoreFields(formId)],
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
				[this.FILTER_SKIP_FIELDS]: [...this.conditionalTags.getIgnoreFields(formId)],
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
		const formId = this.state.getFormIdByElement(event.target);
		const name = field.getAttribute(this.state.getStateAttribute('fieldName'), formId);
		const custom = this.state.getStateElementCustom(name, formId);

		if (this.state.getStateElementIsDisabled(name, formId)) {
			return;
		}

		if (custom.options.maxFiles !== null && custom.files.length >= custom.options.maxFiles) {
			return;
		}

		const input = this.state.getStateElementCustom(name, formId).hiddenFileInput;

		input.click();
		input.blur();

		field?.classList?.add(this.state.getStateSelector('isActive'));
	};

	/**
	 * On focus event for regular fields.
	 *
	 * @param {object} event Event callback.
	 *
	 * @returns {void}
	 */
	onFocusEvent = (event) => {
		this.utils.setOnFocus(event.target);
	};

	/**
	 * On keypress event for regular fields.
	 *
	 * @param {object} event Event callback.
	 *
	 * @returns {void}
	 */
	onKeyDownEvent = (event) => {
		const formId = this.state.getFormIdByElement(event.target);
		const field = this.state.getFormFieldElementByChild(event.target);
		const name = field.getAttribute(this.state.getStateAttribute('fieldName'));

		if (this.state.getStateElementTypeCustom(name, formId) === 'number') {
			const allowedKeys = ['Backspace', 'Enter', 'ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.', '-', 'Tab', 'Delete'];

			// Prevent the default action if the key is not allowed
			if (!allowedKeys.includes(event.key)) {
				event.preventDefault();
			}
		}
	};

	/**
	 * On Select change event.
	 *
	 * @param {object} event Event callback.
	 *
	 * @returns {void}
	 */
	onSelectChangeEvent = (event) => {
		const formId = this.state.getFormIdByElement(event.target);
		const field = this.state.getFormFieldElementByChild(event.target);
		const name = field.getAttribute(this.state.getStateAttribute('fieldName'));
		const custom = this.state.getStateElementCustom(name, formId);

		this.state.setState([StateEnum.ELEMENTS, name, StateEnum.INPUT_SELECT], event.target, formId);

		const options = [...custom?.passedElement?.element?.selectedOptions].map((option) => option?.value).filter((option) => option !== '');

		switch (this.state.getStateElementTypeField(name, formId)) {
			case 'phone':
				const phoneValue = this.state.getStateElementValue(name, formId);

				this.utils.setManualPhoneValue(
					formId,
					name,
					{
						prefix: options?.[0] || '',
						value: phoneValue?.value || '',
					},
					false,
				);
				break;
			case 'country':
				this.utils.setManualCountryValue(formId, name, options, false);
				break;
			case 'select':
				this.utils.setManualSelectValue(formId, name, options, false);
				break;
		}

		// Used only for admin single submit.
		if (this.state.getStateConfigIsAdmin() && this.state.getStateElementIsSingleSubmit(name, formId)) {
			debounce(
				this.formSubmit(formId, {
					[this.FILTER_USE_ONLY_FIELDS]: [name],
				}),
				100,
			);
		}

		// Used only on frontend for single submit.
		if (!this.state.getStateConfigIsAdmin() && this.state.getStateFormConfigUseSingleSubmit(formId)) {
			debounce(this.formSubmit(formId), 100);
		}
	};

	/**
	 * On Select focus event.
	 *
	 * @param {object} event Event callback.
	 *
	 * @returns {void}
	 */
	onSelectFocusEvent = (event) => {
		const formId = this.state.getFormIdByElement(event.target);
		const field = this.state.getFormFieldElementByChild(event.target);
		const name = field.getAttribute(this.state.getStateAttribute('fieldName'));

		const custom = this.state.getStateElementCustom(name, formId);

		custom?.showDropdown();
		custom?.containerOuter?.element?.scrollIntoView({ behavior: 'smooth' });

		this.utils.setOnFocus(event.target);
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
		const type = this.state.getStateElementTypeField(name, formId);

		const { value, checked } = event?.target;

		switch (type) {
			case 'checkbox':
				this.utils.setManualCheckboxValue(
					formId,
					name,
					{
						[value]: checked ? value : '',
					},
					false,
				);
				break;
			case 'radio':
				this.utils.setManualRadioValue(formId, name, value, false);
				break;
			case 'rating':
				this.utils.setManualRatingValue(formId, name, value, false);
				break;
			case 'phone':
				const phoneValue = this.state.getStateElementValue(name, formId);

				this.utils.setManualPhoneValue(
					formId,
					name,
					{
						prefix: phoneValue?.prefix || '',
						value: value || '',
					},
					false,
				);
				break;
			case 'range':
				this.utils.setManualRangeValue(formId, name, value, false);
				break;
			default:
				this.utils.setManualInputValue(formId, name, value, false);
				break;
		}

		// Used only for admin single submit.
		if (this.state.getStateConfigIsAdmin() && this.state.getStateElementIsSingleSubmit(name, formId)) {
			debounce(
				this.formSubmit(formId, {
					[this.FILTER_USE_ONLY_FIELDS]: [name],
				}),
				100,
			);
		}

		// Used only on frontend for single submit.
		if (
			!this.state.getStateConfigIsAdmin() &&
			this.state.getStateFormConfigUseSingleSubmit(formId) &&
			(type === 'range' || type === 'number' || type === 'checkbox' || type === 'radio')
		) {
			debounce(this.formSubmit(formId), 100);
		}
	};

	/**
	 * On range custom event.
	 *
	 * @param {object} event Event callback.
	 *
	 * @returns {void}
	 */
	onRangeCustom = (event) => {
		const target = event?.target;

		if (!target) {
			return;
		}

		const formId = this.state.getFormIdByElement(target);
		const field = this.state.getFormFieldElementByChild(target);
		const name = field.getAttribute(this.state.getStateAttribute('fieldName'));
		let value = parseInt(target?.value);
		const min = parseInt(target?.min);
		const max = parseInt(target?.max);

		if (isNaN(value)) {
			value = min || 0;
		}

		if (value < min) {
			value = min;
		}

		if (value > max) {
			value = max;
		}

		target.value = value;
		this.utils.setManualRangeValue(formId, name, value.toString());

		// Used only on frontend for single submit.
		if (!this.state.getStateConfigIsAdmin() && this.state.getStateFormConfigUseSingleSubmit(formId)) {
			debounce(this.formSubmit(formId), 100);
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
		this.utils.setOnBlur(event.target);
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

		if (window[prefix].form) {
			return;
		}

		window[prefix].form = {
			FORM_DATA: this.FORM_DATA,

			FILTER_IS_STEPS_FINAL_SUBMIT: this.FILTER_IS_STEPS_FINAL_SUBMIT,
			FILTER_SKIP_FIELDS: this.FILTER_SKIP_FIELDS,
			FILTER_USE_ONLY_FIELDS: this.FILTER_USE_ONLY_FIELDS,
			GLOBAL_MSG_TIMEOUT_ID: this.GLOBAL_MSG_TIMEOUT_ID,

			init: () => {
				this.init();
			},
			initOnlyForms: () => {
				this.initOnlyForms();
			},
			initGolocationForm: (formsElement) => {
				this.initGolocationForm(formsElement);
			},
			initOnlyFormsInner: (formId) => {
				this.initOnlyFormsInner(formId);
			},
			initOne: (formId) => {
				this.initOne(formId);
			},
			formSubmit: (formId, filter = {}) => {
				this.formSubmit(formId, filter);
			},
			formSubmitStep: (formId, filter = {}) => {
				this.formSubmitStep(formId, filter);
			},
			formSubmitBefore: (formId, response) => {
				this.formSubmitBefore(formId, response);
			},
			formSubmitSuccess: (formId, response, isFinalStep = false) => {
				this.formSubmitSuccess(formId, response, isFinalStep);
			},
			formSubmitError: (formId, response, isFinalStep = false) => {
				this.formSubmitError(formId, response, isFinalStep);
			},
			formSubmitAfter: (formId, response) => {
				this.formSubmitAfter(formId, response);
			},
			runFormCaptcha: (formId, filter = {}) => {
				this.runFormCaptcha(formId, filter);
			},
			setFormData: (formId, filter = {}) => {
				this.setFormData(formId, filter);
			},
			setFormDataFields: (formId, filter = {}) => {
				this.setFormDataFields(formId, filter);
			},
			getFormDataGroup: (formId) => {
				return this.getFormDataGroup(formId);
			},
			setFormDataStep: (formId) => {
				return this.setFormDataStep(formId);
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
			buildFormDataItems: (data, dataSet = this.FORM_DATA) => {
				this.buildFormDataItems(data, dataSet);
			},
			setupInputField: (formId, name) => {
				this.setupInputField(formId, name);
			},
			setupRangeField: (formId, name) => {
				this.setupRangeField(formId, name);
			},
			setupRatingField: (formId, name) => {
				this.setupRatingField(formId, name);
			},
			setupPhoneField: (formId, name) => {
				this.setupPhoneField(formId, name);
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
			onKeyDownEvent: (event) => {
				this.onKeyDownEvent(event);
			},
			onSelectChangeEvent: (event) => {
				this.onSelectChangeEvent(event);
			},
			onSelectRemoveEvent: (event) => {
				this.onSelectRemoveEvent(event);
			},
			onInputEvent: (event) => {
				this.onInputEvent(event);
			},
			onBlurEvent: (event) => {
				this.onBlurEvent(event);
			},
		};
	}
}
