/* global grecaptcha, esFormsLocalization */

import { cookies, debounce } from '@eightshift/frontend-libs/scripts/helpers';
import { ConditionalTags } from './../../conditional-tags/assets';
import { Steps } from './../../step/assets';
import { Enrichment } from './enrichment';
import { Utils } from './utilities';
import { State } from './state';
import { Data } from './data';

/**
 * Main Forms class.
 */
export class Form {
	constructor(options = {}) {
		this.data = new Data(options);
		this.state = new State(options);
		this.utils = new Utils(options);
		this.enrichment = new Enrichment(options);
		this.conditionalTags = new ConditionalTags(options);
		this.steps = new Steps(options);

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
		// Set all public methods.
		this.publicMethods();

		// Init step.
		// this.steps.init();

		// Init all forms.
		this.initOnlyForms();

		// Init conditional tags.
		// this.conditionalTags.init();

		// Init enrichment.
		// this.enrichment.init();
	}

	/**
	 * Init all forms.
	 * 
	 * @public
	 */
	initOnlyForms() {
		// Loop all forms on the page.
		[...document.querySelectorAll(this.data.formSelector)].forEach((element) => {
			const formId = element.getAttribute(this.data.DATA_ATTRIBUTES.formPostId) || 0;

			this.state.setFormStateInitial(formId);

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

		// this.setSingleSubmitEvents();

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

		fetch(this.state.getStateFormSubmitUrl(formId, '-captcha'), body)
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
				this.utils.setErrorGlobal(formId, message, status);

				// Hide global msg in any case after some time.
				setTimeout(() => {
					this.utils.unsetErrorGlobal(formId);
				}, parseInt(this.state.getStateFormConfigHideGlobalMessageTimeout(formId), 10));
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
	formSubmit(formId, filter = [], isStepSubmit = false) {
		// Dispatch event.
		this.utils.dispatchFormEvent(formId, this.data.EVENTS.BEFORE_FORM_SUBMIT);

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
		let url = this.state.getStateFormSubmitUrl(formId, `-${formType}`);

		// For admin settings use different url and add nonce.
		if (this.state.getStateFormIsAdmin(formId)) {
			url = this.state.getStateFormSubmitUrl(formId);
			body.headers['X-WP-Nonce'] = this.state.getStateFormNonce(formId);
		}

		fetch(url, body)
			.then((response) => {
				return response.json();
			})
			.then((response) => {
				// Dispatch event.
				this.utils.dispatchFormEvent(formId, this.data.EVENTS.AFTER_FORM_SUBMIT, response);

				// Clear all errors.
				this.utils.resetErrors(formId);

				// Remove loader.
				this.utils.hideLoader(formId);

				const {
					status,
					message,
					data,
				} = response;

				// On success state.
				if (status === 'success') {
					// Redirect on success.
					if (isStepSubmit) {
						// this.steps.formStepStubmit(element, response);
					} else {
						// Send GTM.
						this.utils.gtmSubmit(formId, this.FORM_DATA, status);

						// Dispatch event.
						this.utils.dispatchFormEvent(formId, this.data.EVENTS.AFTER_FORM_SUBMIT_SUCCESS, response);

						if (this.state.getStateFormConfigSuccessRedirect(formId)) {
							// Set global msg.
							this.utils.setErrorGlobal(formId, message, status);

							// Redirect to url and update url params from from data.
							if (this.state.getStateFormIsAdmin(formId)) {
								this.utils.redirectToUrlByRefference(window.location.href, formId, true);
							} else {
								this.utils.redirectToUrl(formId, this.FORM_DATA);
							}
						} else {
							// Do normal success without redirect.
							// Do the actual redirect after some time for custom form processed externally.
							if (data?.processExternaly) {
								setTimeout(() => {
									this.state.getStateFormElement().submit();
								}, parseInt(this.state.getStateFormConfigRedirectionTimeout(formId), 10));
							}

							// Set global msg.
							this.utils.setErrorGlobal(formId, message, status);

							// Clear form values.
							this.utils.resetForm(formId);
						}
					}
				} else {
					// Set global msg.
					if (isStepSubmit) {
						// this.steps.formStepStubmit(element, response);
					} else {
						this.utils.gtmSubmit(formId, this.FORM_DATA, status, data?.validation);

						// Dispatch event.
						if (data?.validation !== undefined) {
							this.utils.dispatchFormEvent(formId, this.data.EVENTS.AFTER_FORM_SUBMIT_ERROR_VALIDATION, response);

							// this.steps.goBackToFirstValidationErrorStep(formId, data?.validation);
							this.utils.outputErrors(formId, data?.validation);
						} else {
							this.utils.dispatchFormEvent(formId, this.data.EVENTS.AFTER_FORM_SUBMIT_ERROR, response);
						}

						this.utils.setErrorGlobal(formId, message, status);
					}
				}

				// Hide global msg in any case after some time.
				setTimeout(() => {
					this.utils.unsetErrorGlobal(formId);
				}, parseInt(this.state.getStateFormConfigHideGlobalMessageTimeout(formId), 10));

				// Dispatch event.
				this.utils.dispatchFormEvent(formId, this.data.EVENTS.AFTER_FORM_SUBMIT_END, response);
			});

			this.clearFormData();
	}

	runFormCaptcha(formId) {
		if (!this.state.getStateCaptchaIsUsed(formId)) {
			return;
		}

		const actionName = this.state.getStateCaptchaSubmitAction(formId);
		const siteKey = this.state.getStateCaptchaSiteKey(formId);

		if (this.state.getStateCaptchaIsEnterprise(formId)) {
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

	setFormData(formId, filter = []) {
		this.setFormDataFields(formId, filter);
		this.setFormDataCommon(formId);

		if (this.state.getStateFormIsAdmin(formId)) {
			this.setFormDataAdmin(formId);
		} else {
			this.setFormDataPerType(formId);
		}
	}

	/**
 * Build form data object.
 * 
 * @param {object} element Form element.
 * @param {boolean} singleSubmit Is form single submit, used in admin.
 *
 * @public
 */
	setFormDataFields(formId, filter = [], isStepSubmit = false) {
		const formType = this.state.getStateFormType(formId);
		// const selectors = 'input, select, textarea';

		// const groups = element.querySelectorAll(`${this.data.groupSelector}`);
		// let items = element.querySelectorAll(selectors);

		// If single submit override items and pass only one item to submit.
		// if (singleSubmit) {
		// 	items = [
		// 		singleSubmit
		// 	];
		// } else {
		// 	// Check if we are saving group items in one key.
		// 	if (groups.length) {
		// 		for (const [key, group] of Object.entries(groups)) { // eslint-disable-line no-unused-vars
		// 			const groupSaveAsOneField = Boolean(group.getAttribute(this.data.DATA_ATTRIBUTES.groupSaveAsOneField));

		// 			if (!groupSaveAsOneField) {
		// 				continue;
		// 			}

		// 			const groupInner = group.querySelectorAll(selectors);

		// 			if (groupInner.length) {
		// 				const groupInnerItems = {};

		// 				for (const [key, groupInnerItem] of Object.entries(groupInner)) { // eslint-disable-line no-unused-vars
		// 					const {
		// 						id,
		// 						value,
		// 						disabled,
		// 					} = groupInnerItem;

		// 					if (disabled) {
		// 						continue;
		// 					}

		// 					groupInnerItems[id] = value;
		// 				}

		// 				const groupId = group.getAttribute(this.data.DATA_ATTRIBUTES.fieldId);

		// 				if (groupId) {
		// 					formData.append(groupId, JSON.stringify({
		// 						value: groupInnerItems,
		// 						type: 'group',
		// 					}));
		// 				}
		// 			}

		// 			// Remove group items from the original items.
		// 			items = Array.prototype.slice.call(items).filter((item) => Array.prototype.slice.call(groupInner).indexOf(item) == -1);
		// 		}
		// 	}
		// }

		// Iterate all form items.
		for (const [key, item] of this.state.getStateElements(formId)) { // eslint-disable-line no-unused-vars

			const type = item[this.state.TYPE];
			const input = item[this.state.INPUT];
			const name = item[this.state.NAME];
			const value = item[this.state.VALUE];
			const values = item[this.state.VALUES];
			const internalType = item[this.state.INTERNAL_TYPE];
			const saveAsJson = item[this.state.SAVE_AS_JSON];

			const {
				disabled,
				checked,
			} = input;

			if (filter.length && !filter.includes(name)) {
				continue;
			}

			// const {
			// 	type,
			// 	name,
			// 	id,
			// 	disabled,
			// 	checked,
			// 	dataset: {
			// 		objectTypeId, // Used for HubSpot only
			// 	}
			// } = item;

			if (disabled) {
				continue;
			}

			// let {
			// 	value,
			// } = item;

			// Build data object.
			const data = {
				name,
				value,
				type,
				internalType,
				custom: '',
			};

			// console.log(item);
			// console.log(data);
			// console.log('-------------------');

			switch (formType) {
				case 'hubspost':
						data.custom = item.objectTypeId ?? '';
					break;
			}

			switch (type) {
				case 'checkbox':
				case 'radio':
					Object.values(values).forEach((item, index) => {
						data.value = item;

						this.FORM_DATA.append(`${name}[${index}]`, JSON.stringify(data));
					});
					break;
				case 'textarea':
					// Convert textarea to json format with : as delimiter.
					if (saveAsJson) {
						const textareaOutput = [];
						const regexItems = data.value.split(/\r\n|\r|\n/);

						if (regexItems.length) {
							regexItems.forEach((element) => {
								if (!element) {
									return;
								}

								const innerItem = element.split(':');
								const innerOutput = [];

								if (innerItem) {
									innerItem.forEach((inner) => {
										const innerItem = inner.trim();

										if (!innerItem) {
											return;
										}

										innerOutput.push(innerItem.trim());
									});
								}

								textareaOutput.push(innerOutput);
							});
						}

						data.value = textareaOutput;
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
								console.log(`${name}[${key}]`);
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

			// switch (type) {
			// 	case 'checkbox':
			// 	case 'radio':
			// 		// If checkbox/radio on empty change to empty value.
			// 		if (!checked) {
			// 			// If unchecked value attribute is added use that if not send an empty value.
			// 			data.value = item.getAttribute(this.data.DATA_ATTRIBUTES.fieldUncheckedValue) ?? '';
			// 		}

			// 		this.FORM_DATA.append(id, JSON.stringify(data));

			// 		break;
			// 	case 'file':
			// 		// If custom file use files got from the global object of files uploaded.
			// 		const fileList = this.state.getState([this.state.FILES, name], formId)?.files ?? []; // eslint-disable-line no-case-declarations

			// 		// Loop files and append.
			// 		if (fileList.length) {
			// 			for (const [key, file] of Object.entries(fileList)) {
			// 				const status = file?.xhr?.response ? JSON.parse(file.xhr.response) : 'error';

			// 				// Check if the file is ok.
			// 				if (status === 'success') {
			// 					data.value = file.upload.uuid;
			// 					this.FORM_DATA.append(`${id}[${key}]`, JSON.stringify(data));
			// 				}
			// 			}
			// 		} else {
			// 			this.FORM_DATA.append(`${id}[0]`, JSON.stringify({}));
			// 		}
			// 		break;
			// 	case 'select-one':
			// 		// Bailout if select is part of the phone.
			// 		if (item.closest(this.data.fieldSelector).getAttribute(this.data.DATA_ATTRIBUTES.fieldType) === 'phone') {
			// 			break;
			// 		}

			// 		this.FORM_DATA.append(id, JSON.stringify(data));
			// 		break;
			// 	case 'text':
			// 		// Skip alt text input created by date time picker lib.
			// 		if (item?.previousElementSibling?.classList?.contains('flatpickr-input')) {
			// 			break;
			// 		}

			// 		this.FORM_DATA.append(id, JSON.stringify(data));
			// 		break;
			// 	case 'tel':
			// 		const phoneDisablePicker = Boolean(element.getAttribute(this.data.DATA_ATTRIBUTES.phoneDisablePicker)); // eslint-disable-line no-case-declarations
			// 		if (phoneDisablePicker) {
			// 			this.FORM_DATA.append(id, JSON.stringify(data));
			// 			break;
			// 		}

			// 		const prefix = item.previousElementSibling.querySelector('select'); // eslint-disable-line no-case-declarations
			// 		const selectedPrefix = prefix.options[prefix.selectedIndex].value; // eslint-disable-line no-case-declarations

			// 		if (item.value) {
			// 			data.value = `${selectedPrefix}${item.value}`;
			// 		}

			// 		this.FORM_DATA.append(id, JSON.stringify(data));

			// 		break;
			// 	case 'textarea':
			// 		const saveAsJson = Boolean(item.getAttribute(this.data.DATA_ATTRIBUTES.saveAsJson)); // eslint-disable-line no-case-declarations

			// 		// Convert textarea to json format with : as delimiter.
			// 		if (saveAsJson) {
			// 			const textareaOutput = [];
			// 			const regexItems = data.value.split(/\r\n|\r|\n/);

			// 			if (regexItems.length) {
			// 				regexItems.forEach((element) => {
			// 					if (!element) {
			// 						return;
			// 					}

			// 					const innerItem = element.split(':');
			// 					const innerOutput = [];

			// 					if (innerItem) {
			// 						innerItem.forEach((inner) => {
			// 							const innerItem = inner.trim();

			// 							if (!innerItem) {
			// 								return;
			// 							}

			// 							innerOutput.push(innerItem.trim());
			// 						});
			// 					}

			// 					textareaOutput.push(innerOutput);
			// 				});
			// 			}

			// 			data.value = textareaOutput;
			// 		}

			// 		this.FORM_DATA.append(id, JSON.stringify(data));
			// 		break;
			// 	case 'search':
			// 		// Skip search field from dropdown.
			// 		break;
			// 	default:
			// 		this.FORM_DATA.append(id, JSON.stringify(data));

			// 		break;
			// }
		}

		// Set localStorage to hidden field.
		// if (this.enrichment.isEnrichmentUsed()) {
		// 	const storage = this.enrichment.getLocalStorage();

		// 	if (storage) {
		// 		this.FORM_DATA.append(this.data.FORM_PARAMS.storage, JSON.stringify({
		// 			name: this.data.FORM_PARAMS.storage,
		// 			value: storage,
		// 			type: 'hidden',
		// 		}));
		// 	}
		// }

		// Output fields to validate if we are validating steps only.
		// const stepId = this.steps.getCurrentStep(element);
		// if (stepId && isStepSubmit) {
		// 	// Find all fields by name in the step.
		// 	const fieldsInStep = this.steps.getAllFieldsInStep(element, stepId);

		// 	if (fieldsInStep) {
		// 		// Find all field names and remove null ones (submit).
		// 		const outputStepFields = Array.from(fieldsInStep, (stepField) => stepField.getAttribute(this.data.DATA_ATTRIBUTES.fieldName)).filter(n => n);

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

	setFormDataCommon(formId) {
		const {
			postId,
			type,
			action,
			actionExternal,
		} = this.data.FORM_PARAMS;

		this.buildFormDataItems([
			{
				name: postId,
				value: formId,
				type: 'hidden',
			},
			{
				name: type,
				value: this.state.getStateFormType(formId),
				type: 'hidden',
			},
			{
				name: action,
				value: this.state.getStateFormAction(formId),
				type: 'hidden',
			},
			{
				name: actionExternal,
				value: this.state.getStateFormActionExternal(formId),
				type: 'hidden',
			},
		]);
	}

	setFormDataAdmin(formId) {
		const {
			settingsType,
		} = this.data.FORM_PARAMS;

		this.buildFormDataItems([
			{
				name: settingsType,
				value: this.state.getStateFormTypeSettings(formId),
				type: 'hidden',
			},
		]);

		// this.FORM_DATA.append(this.data.FORM_PARAMS.singleSubmit, JSON.stringify({
		// 	name: this.data.FORM_PARAMS.singleSubmit,
		// 	value: 'true',
		// 	type: 'hidden',
		// }));
	}

	setFormDataPerType(formId) {
		const output = [];

		switch (this.state.getStateFormType(formId)) {
			case 'hubspot':
				const {
					hubspotCookie,
					hubspotPageName,
					hubspotPageUrl,
				} = this.data.FORM_PARAMS;

				output = [
					...output,
					{
						name: hubspotCookie,
						value: cookies.getCookie('hubspotutk'),
						type: 'hidden',
					},
					{
						name: hubspotPageName,
						value: document.title,
						type: 'hidden',
					},
					{
						name: hubspotPageUrl,
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

	clearFormData() {
		this.FORM_DATA = new FormData();
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

		this.utils.setFieldVisualState(name, formId);

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
		this.setupSelectField(name, formId);
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

		this.utils.setFieldVisualState(name, formId);

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
		const {
			type,
			input,
		} = this.state.getStateElement(name, formId);

		import('flatpickr').then((flatpickr) => {
			const previewFormat = input.getAttribute(this.data.DATA_ATTRIBUTES.datePreviewFormat);
			const outputFormat = input.getAttribute(this.data.DATA_ATTRIBUTES.dateOutputFormat);

			const datePicker = flatpickr.default(input, {
				enableTime: type === 'datetime-local',
				dateFormat: outputFormat,
				altFormat: previewFormat,
				altInput: true,
			});

			this.state.setState([this.state.ELEMENTS, name, this.state.LOADED], true, formId);
			this.state.setState([this.state.ELEMENTS, name, this.state.CUSTOM], datePicker, formId);

			this.setupInputField(name, formId);

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
			const dataObject = this.data;

			const customProperties = [
				this.data.DATA_ATTRIBUTES.selectCountryCode,
				this.data.DATA_ATTRIBUTES.selectCountryLabel,
				this.data.DATA_ATTRIBUTES.selectCountryNumber,
			]
 
			const choices = new Choices.default(input, {
				searchEnabled: this.state.getStateElementConfig(name, this.state.CONFIG_SELECT_USE_SEARCH, formId),
				shouldSort: false,
				position: 'bottom',
				allowHTML: true,
				placeholder: this.state.getStateElementConfig(name, this.state.CONFIG_SELECT_USE_PLACEHOLDER, formId),
				searchFields: ['label', 'value', 'customProperties'],
				itemSelectText: '',
				classNames: {
					containerOuter: `choices ${this.data.selectClassName}`,
				},
				callbackOnCreateTemplates: function() {
					return {
						option: (...args) => {
							const element = Choices.default.defaults.templates.option.call(this, ...args);
							const properties = args?.[0]?.customProperties;

							if (properties) {
								element.setAttribute(dataObject.DATA_ATTRIBUTES.selectCustomProperties, JSON.stringify(properties));
							}

							return element;
						},
						choice: (...args) => {
							const element = Choices.default.defaults.templates.choice.call(this, ...args);
							const properties = args?.[1]?.customProperties;

							if (properties) {
								customProperties.forEach((property) => {
									const check = properties?.[property];
									if (check) {
										element.setAttribute(property, check);
									}
								});
							}

							if (properties) {
								// Conditional tags
								// const conditionalTags = properties?.[dataObject.DATA_ATTRIBUTES.conditionalTags];

								// if (conditionalTags) {
								// 	element.setAttribute(dataObject.DATA_ATTRIBUTES.conditionalTags, conditionalTags);
								// }

							// 	const fieldName = properties?.[dataObject.DATA_ATTRIBUTES.fieldName];
							// 	if (fieldName) {
							// 		element.setAttribute(dataObject.DATA_ATTRIBUTES.fieldName, fieldName);
							// 	}

							// 	const fieldType = properties?.[dataObject.DATA_ATTRIBUTES.fieldType];
							// 	if (fieldType) {
							// 		element.setAttribute(dataObject.DATA_ATTRIBUTES.fieldType, fieldType);
							// 	}
							// }

							// Set state for choices conditional tags.
							// const state = dataObject.getState([state.SELECTS, this.passedElement.element.name], formId);
							// if (state) {
							// 	if (state.config.choices.find((item) => item.value === args?.[1]?.value).esFormsIsHidden) {
							// 		element.classList.add(dataObject.SELECTORS.CLASS_HIDDEN);
							// 	}
							}

							return element;
						},
						item: (...args) => {
							const element = Choices.default.defaults.templates.choice.call(this, ...args);
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

			this.state.setState([this.state.ELEMENTS, name, this.state.LOADED], true, formId);
			this.state.setState([this.state.ELEMENTS, name, this.state.CUSTOM], choices, formId);

			choices.containerOuter.element.addEventListener('focus', this.onFocusEvent);
			choices.containerOuter.element.addEventListener('blur', this.onBlurEvent);
			choices.containerOuter.element.addEventListener('change', this.onChangeEvent);
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

		this.state.setState([this.state.ELEMENTS, name, this.state.LOADED], true, formId);

		this.utils.setFieldVisualState(name, formId);

		input.addEventListener('keydown', this.onFocusEvent);
		input.addEventListener('focus', this.onFocusEvent);
		input.addEventListener('blur', this.onBlurEvent);
		input.addEventListener('input', debounce(this.onInputEvent, 250));

		import('autosize').then((autosize) => {
			input.setAttribute('rows', '1');
			input.setAttribute('cols', '');

			autosize.default(input);
		});
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
		const {
			input,
			field,
		} = this.state.getStateElement(name, formId);

		import('dropzone').then((Dropzone) => {
			const dropzone = new Dropzone.default(
				field,
				{
					url: `${this.data.formSubmitRestApiUrl}-files`,
					addRemoveLinks: true,
					autoDiscover: false,
					parallelUploads: 1,
					maxFiles: !input.multiple ? 1 : null,
					dictRemoveFile: this.data.SETTINGS.FILE_CUSTOM_REMOVE_LABEL,
				}
			);

			// Set data to internal state.
			this.state.setState([this.state.ELEMENTS, name, this.state.LOADED], true, formId);
			this.state.setState([this.state.ELEMENTS, name, this.state.CUSTOM], dropzone, formId);

			// On add one file add selectors for UX.
			dropzone.on("addedfile", (file) => {
				setTimeout(() => {
					file.previewTemplate.classList.add(this.data.SELECTORS.CLASS_ACTIVE);
				}, 200);

				setTimeout(() => {
					file.previewTemplate.classList.add(this.data.SELECTORS.CLASS_FILLED);
				}, 1200);

				field.classList.remove(this.data.SELECTORS.CLASS_ACTIVE);

				this.state.setState([this.state.ELEMENTS, name, this.state.VALUE], 'true', formId);

				// Remove main filed validation error.
				this.utils.unsetFieldError(name, formId);
			});

			dropzone.on('removedfile', () => {
				const custom = this.state.getStateElementCustom(name, formId);

				if (custom.files.length === 0) {
					field.classList.remove(this.data.SELECTORS.CLASS_FILLED);
				}

				field.classList.remove(this.data.SELECTORS.CLASS_ACTIVE);
				this.state.setState([this.state.ELEMENTS, name, this.state.VALUE], '', formId);
			})

			// Add data formData to the api call for the file upload.
			dropzone.on("sending", (file, xhr, formData) => {
				const paramName = this.data.FORM_PARAMS;

				// Add common items like formID and type.
				this.buildFormDataItems([
					{
						name: paramName.postId,
						value: formId,
						type: 'hidden',
					},
					{
						name: paramName.type,
						value: 'fileUpload',
						type: 'hidden',
					},
					{
						// Add field name to know where whas this file upload to.
						name: paramName.name,
						value: name,
						type: 'hidden',
					},
					{
						// Add file ID to know the file.
						name: paramName.fileId,
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

				field.classList.add(this.data.SELECTORS.CLASS_FILLED);
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
			field.addEventListener('click', this.onCustomFileWrapClickEvent);
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
		const elements = document.querySelectorAll(this.data.formSelector);

		[...elements].forEach((element) => {
			// Regular submit.
			element.removeEventListener('submit', this.onFormSubmitEvent);

			const formId = element.getAttribute(this.data.DATA_ATTRIBUTES.formPostId);

			const inputs = element.querySelectorAll(this.data.inputSelector);
			const textareas = element.querySelectorAll(this.data.textareaSelector);
			const selects = element.querySelectorAll(this.data.selectSelector);
			const files = element.querySelectorAll(this.data.fileSelector);

			[...inputs].forEach((input) => {
				switch (input.type) {
					case 'date':
					case 'datetime-local':
						// this.state.deleteState(this.state.DATES, formId);
						break;
				}

				input.removeEventListener('keydown', this.onFocusEvent);
				input.removeEventListener('focus', this.onFocusEvent);
				input.removeEventListener('blur', this.onBlurEvent);
			});

			[...selects].forEach(() => {
				this.state.deleteState(this.state.SELECTS, formId);
			});

			// Setup textarea inputs.
			[...textareas].forEach((textarea) => {
				textarea.removeEventListener('keydown', this.onFocusEvent);
				textarea.removeEventListener('focus', this.onFocusEvent);
				textarea.removeEventListener('blur', this.onBlurEvent);

				// this.state.deleteState(this.state.TEXTAREAS, formId);
			});

			// Setup file single inputs.
			[...files].forEach((file) => {
				this.state.deleteState(this.state.FILES, formId);

				file.nextElementSibling.removeEventListener('click', this.onCustomFileWrapClickEvent);

				const button = file.parentNode.querySelector('a');

				button.removeEventListener('focus', this.onFocusEvent);
				button.removeEventListener('blur', this.onBlurEvent);
			});

			this.state.dispatchFormEvent(element, this.data.EVENTS.AFTER_FORM_EVENTS_CLEAR);
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
	 * @public
	 */
	onFormSubmitEvent = (event) => {
		event.preventDefault();

		const formId = this.state.getFormIdByElement(event.target);

		// Single submit for admin settings.
		// if (this.utils.isFormAdmin()) {
		// 	// Look all internal items for single submit option.
		// 	[...this.state.getStateFilteredBykey(this.state.ELEMENTS, this.state.TYPE, this.state.IS_SINGLE_SUBMIT, formId)].forEach((item) => {
		// 		if (item[this.state.TYPE] === 'submit') {
		// 			item.addEventListener('click', this.onFormSubmitSingleEvent);
		// 		} else {
		// 			item.addEventListener('change', this.onFormSubmitSingleEvent);
		// 		}
		// 	});
		// }

		this.utils.showLoader(formId);
		this.runFormCaptcha(formId);

		debounce(this.formSubmit(formId), 100);

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
	onCustomFileWrapClickEvent = (event) => {
		event.preventDefault();
		event.stopPropagation();

		const field = event.target.closest(this.data.fieldSelector);

		this.state.getStateElementCustom(field.getAttribute(this.data.DATA_ATTRIBUTES.fieldName), this.state.getFormIdByElement(event.target)).hiddenFileInput.click()

		field.classList.add(this.data.SELECTORS.CLASS_ACTIVE);
	};

	// On Focus event for regular fields.
	onFocusEvent = (event) => {
		this.state.getStateElementField(this.state.getFormFieldElementByChild(event.target).getAttribute(this.data.DATA_ATTRIBUTES.fieldName), this.state.getFormIdByElement(event.target)).classList.add(this.data.SELECTORS.CLASS_ACTIVE);
	};

	onChangeEvent = (event) => {
		const field = this.state.getFormFieldElementByChild(event.target);
		const formId = this.state.getFormIdByElement(event.target);
		const type = field.getAttribute(this.data.DATA_ATTRIBUTES.fieldType);
		const name = field.getAttribute(this.data.DATA_ATTRIBUTES.fieldName);

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

		if (this.state.getStateFormIsAdmin(formId) && this.state.getStateElementIsSingleSubmit(name, formId)) {
			debounce(this.formSubmit(formId, [name]), 100);
		}
	}

	onInputEvent = (event) => {
		const formId = this.state.getFormIdByElement(event.target);
		const field = this.state.getFormFieldElementByChild(event.target);
		const name = field.getAttribute(this.data.DATA_ATTRIBUTES.fieldName);

		this.state.setValues(event.target, this.state.getFormIdByElement(event.target));

		if (this.state.getStateFormIsAdmin(formId) && this.state.getStateElementIsSingleSubmit(name, formId)) {
			debounce(this.formSubmit(formId, [name]), 100);
		}
	}

	// On Blur generic method. Check for length of value.
	onBlurEvent = (event) => {
		const field = this.state.getFormFieldElementByChild(event.target);
		const name = field.getAttribute(this.data.DATA_ATTRIBUTES.fieldName);
		const formId = this.state.getFormIdByElement(event.target);

		this.utils.setFieldVisualState(name, formId);
	};

	/**
	 * Handle form submit and all logic for only one field click.
	 *
	 * @param {object} event Event callback.
	 *
	 * @private
	 */
	// onFormSubmitSingleEvent = (event) => {
	// 	event.preventDefault();
	// 	const {target} = event;

	// 	this.formSubmit(target.closest(this.data.formSelector), target);
	// };

	////////////////////////////////////////////////////////////////
	// Private methods - not shared to the public window object.
	////////////////////////////////////////////////////////////////

	/**
	 * Set all public methods.
	 * 
	 * @private
	 */
	publicMethods() {
		if (typeof window?.[this.data.prefix]?.form === 'undefined') {
			window[this.data.prefix].form = {
				init: () => {
					this.init();
				},
				initOnlyForms: () => {
					this.initOnlyForms();
				},
				initOne: (element) => {
					this.initOne(element);
				},
				formSubmitCaptcha: (element, token, payed, action) => {
					this.formSubmitCaptcha(element, token, payed, action);
				},
				formSubmit: (element, singleSubmit = false, isStepSubmit = false) => {
					this.formSubmit(element, singleSubmit, isStepSubmit);
				},
				setFormData: (element, singleSubmit = false) => {
					this.setFormData(element, singleSubmit);
				},
				setupInputField: (input, formId) => {
					this.setupInputField(input, formId);
				},
				setupDateField: (date, formId) => {
					this.setupDateField(date, formId);
				},
				setupSelectField: (select, formId) => {
					this.setupSelectField(select, formId);
				},
				setupTextareaField: (textarea, formId) => {
					this.setupTextareaField(textarea, formId);
				},
				setupFileField: (file, formId, index, element) => {
					this.setupFileField(file, formId, index, element);
				},
				setupPhoneSync: (form, formId) => {
					this.setupPhoneSync(form, formId);
				},
				removeEvents: () => {
					this.removeEvents();
				},
				runFormCaptcha: (element = '') => {
					this.runFormCaptcha(element);
				},
				onFormSubmitEvent: (event) => {
					this.onFormSubmitEvent(event);
				},
				onCustomFileWrapClickEvent: (event) => {
					this.onCustomFileWrapClickEvent(event);
				},
			};
		}
	}
}
