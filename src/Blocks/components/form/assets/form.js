/* global grecaptcha, esFormsLocalization */

import { cookies } from '@eightshift/frontend-libs/scripts/helpers';
import { ConditionalTags } from './../../conditional-tags/assets';
import { Enrichment } from './enrichment';
import { Utils } from './utilities';

/**
 * Main Forms class.
 */
export class Form {
	constructor(options = {}) {
		/** @type Utils */
		this.utils = options.utils ?? new Utils();

		/** @type Enrichment */
		this.enrichment = new Enrichment(this.utils);

		/** @type ConditionalTags */
		this.conditionalTags = new ConditionalTags(this.utils);
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

		// Init all forms.
		this.initOnlyForms();

		// Init conditional tags.
		this.conditionalTags.init();

		// Init enrichment.
		this.enrichment.init();

		// Triger event that forms are fully loaded.
		this.utils.dispatchFormEvent(window, this.utils.EVENTS.FORMS_JS_LOADED);
	}

	/**
	 * Init all forms.
	 * 
	 * @public
	 */
	initOnlyForms() {
		const elements = document.querySelectorAll(this.utils.formSelector);

		// Loop all forms on the page.
		[...elements].forEach((element) => {
			this.initOne(element);
		});
	}

	/**
	 * Init one form by element.
	 * 
	 * @param {object} element Form element.
	 *
	 * @public
	 */
	initOne(element) {
		// Regular submit.
		element.addEventListener('submit', this.onFormSubmitEvent);

		// Single submit for admin settings.
		if (this.utils.formIsAdmin) {
			const items = element.querySelectorAll(this.utils.submitSingleSelector);

			// Look all internal items for single submit option.
			[...items].forEach((item) => {
				if (item.type === 'submit') {
					item.addEventListener('click', this.onFormSubmitSingleEvent);
				} else {
					item.addEventListener('change', this.onFormSubmitSingleEvent);
				}
			});
		}

		// Get form ID.
		const formId = element.getAttribute(this.utils.DATA_ATTRIBUTES.formPostId);

		// Form loading started.
		this.utils.FORMS[formId] = false;

		// All fields selectors.
		const inputs = element.querySelectorAll(this.utils.inputSelector);
		const textareas = element.querySelectorAll(this.utils.textareaSelector);
		const selects = element.querySelectorAll(this.utils.selectSelector);
		const files = element.querySelectorAll(this.utils.fileSelector);

		// Setup regular inputs.
		this.utils.CUSTOM_DATES[formId] = [];
		[...inputs].forEach((input) => {
			switch (input.type) {
				case 'date':
				case 'datetime-local':
					this.setupDateField(input, formId);
					break;
			}

			this.setupInputField(input);
		});

		// Setup select inputs.
		this.utils.CUSTOM_SELECTS[formId] = [];
		[...selects].forEach((select) => {
			this.setupSelectField(select, formId);
		});

		// Setup textarea inputs.
		this.utils.CUSTOM_TEXTAREAS[formId] = [];
		[...textareas].forEach((textarea) => {
			this.setupTextareaField(textarea, formId);
		});

		// Setup file single inputs.
		this.utils.CUSTOM_FILES[formId] = [];
		[...files].forEach((file, index) => {
			this.setupFileField(file, formId, index, element);
		});

		// Form loaded.
		this.utils.isFormLoaded(
			formId,
			element,
			selects.length,
			textareas.length,
			files.length
		);

		// Setup phone sync.
		this.setupPhoneSync(element, formId);
	}

	/**
	 *  Handle form submit and all logic in case we have captcha in place.
	 * 
	 * @param {object} element Form element.
	 * @param {string} token Captcha token from api.
	 *
	 * @public
	 */
	formSubmitCaptcha(element, token, payed, action) {
		// Loader show.
		this.utils.showLoader(element);

		// Populate body data.
		const body = {
			method: element.getAttribute('method'),
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

		fetch(`${this.utils.formSubmitRestApiUrl}-captcha`, body)
		.then((response) => {
			return response.json();
		})
		.then((response) => {
			// On success state.
			if (response.status === 'success') {
				this.formSubmit(element);
			} else {
				// Clear all errors.
				this.utils.reset(element);

				// Remove loader.
				this.utils.hideLoader(element);

				// Set global msg.
				this.utils.setGlobalMsg(element, response.message, 'error');

				// Hide global msg in any case after some time.
				setTimeout(() => {
					this.utils.hideGlobalMsg(element);
				}, parseInt(this.utils.SETTINGS.HIDE_GLOBAL_MESSAGE_TIMEOUT, 10));
			}
		});
	}

	/**
	 * Handle form submit and all logic.
	 * 
	 * @param {object} element Form element.
	 * @param {boolean} singleSubmit Is form single submit, used in admin.
	 *
	 * @public
	 */
	formSubmit(element, singleSubmit = false) {
		// Dispatch event.
		this.utils.dispatchFormEvent(element, this.utils.EVENTS.BEFORE_FORM_SUBMIT);

		// Loader show.
		if (!this.utils.isCaptchaUsed()) {
			this.utils.showLoader(element);
		}

		const formData = this.getFormData(element, singleSubmit);

		const formType = element.getAttribute(this.utils.DATA_ATTRIBUTES.formType);

		// Populate body data.
		const body = {
			method: element.getAttribute('method'),
			mode: 'same-origin',
			headers: {
				Accept: 'multipart/form-data',
			},
			body: formData,
			credentials: 'same-origin',
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		// Url for frontend forms.
		let url = `${this.utils.formSubmitRestApiUrl}-${formType}`;

		// For admin settings use different url and add nonce.
		if (this.utils.formIsAdmin) {
			url = this.utils.formSubmitRestApiUrl;
			body.headers['X-WP-Nonce'] = esFormsLocalization.nonce;
		}

		fetch(url, body)
			.then((response) => {
				return response.json();
			})
			.then((response) => {
				// Dispatch event.
				this.utils.dispatchFormEvent(element, this.utils.EVENTS.AFTER_FORM_SUBMIT);

				// Clear all errors.
				this.utils.reset(element);

				// Remove loader.
				this.utils.hideLoader(element);

				// On success state.
				if (response.status === 'success') {
					// Send GTM.
					this.utils.gtmSubmit(element, formData, response.status);

					// Dispatch event.
					this.utils.dispatchFormEvent(element, this.utils.EVENTS.AFTER_FORM_SUBMIT_SUCCESS);

					// Redirect on success.
					if (element.hasAttribute(this.utils.DATA_ATTRIBUTES.successRedirect) || singleSubmit) {

						// Set global msg.
						this.utils.setGlobalMsg(element, response.message, 'success');

						// Redirect to url and update url params from from data.
						if (singleSubmit) {
							this.utils.redirectToUrlByRefference(window.location.href, element, true);
						} else {
							this.utils.redirectToUrl(element, formData);
						}
					} else {
						// Do normal success without redirect.

						// Do the actual redirect after some time for custom form processed externally.
						if (response?.data?.processExternaly) {
							setTimeout(() => {
								element.submit();
							}, parseInt(this.utils.SETTINGS.REDIRECTION_TIMEOUT, 10));
						}

						// Set global msg.
						this.utils.setGlobalMsg(element, response.message, 'success');

						// Clear form values.
						this.utils.resetForm(element);
					}
				} else {
					this.utils.gtmSubmit(element, formData, response.status, response?.data?.validation);
					const isValidationError = response?.data?.validation !== undefined;

					// Dispatch event.
					if (isValidationError) {
						this.utils.dispatchFormEvent(element, this.utils.EVENTS.AFTER_FORM_SUBMIT_ERROR_VALIDATION);
					} else {
						this.utils.dispatchFormEvent(element, this.utils.EVENTS.AFTER_FORM_SUBMIT_ERROR);
					}

					this.utils.outputErrors(element, response?.data?.validation);

					// Set global msg.
					this.utils.setGlobalMsg(element, response.message, 'error');
				}

				// Hide global msg in any case after some time.
				setTimeout(() => {
					this.utils.hideGlobalMsg(element);
				}, parseInt(this.utils.SETTINGS.HIDE_GLOBAL_MESSAGE_TIMEOUT, 10));

				// Dispatch event.
				this.utils.dispatchFormEvent(element, this.utils.EVENTS.AFTER_FORM_SUBMIT_END);
			});
	}

	/**
	 * Build form data object.
	 * 
	 * @param {object} element Form element.
	 * @param {boolean} singleSubmit Is form single submit, used in admin.
	 *
	 * @public
	 */
	getFormData(element, singleSubmit = false) {
		const formData = new FormData();
		const selectors = 'input, select, textarea';

		const groups = element.querySelectorAll(`${this.utils.groupSelector}`);
		const formId = element.getAttribute(this.utils.DATA_ATTRIBUTES.formPostId);
		const formType = element.getAttribute(this.utils.DATA_ATTRIBUTES.formType);
		let items = element.querySelectorAll(selectors);

		// If single submit override items and pass only one item to submit.
		if (singleSubmit) {
			items = [
				singleSubmit
			];
		} else {
			// Check if we are saving group items in one key.
			if (groups.length) {
				for (const [key, group] of Object.entries(groups)) { // eslint-disable-line no-unused-vars
					const groupSaveAsOneField = Boolean(group.getAttribute(this.utils.DATA_ATTRIBUTES.groupSaveAsOneField));

					if (!groupSaveAsOneField) {
						continue;
					}

					const groupInner = group.querySelectorAll(selectors);

					if (groupInner.length) {
						const groupInnerItems = {};

						for (const [key, groupInnerItem] of Object.entries(groupInner)) { // eslint-disable-line no-unused-vars
							const {
								id,
								value,
								disabled,
							} = groupInnerItem;

							if (disabled) {
								continue;
							}

							groupInnerItems[id] = value;
						}

						const groupId = group.getAttribute(this.utils.DATA_ATTRIBUTES.fieldId);

						if (groupId) {
							formData.append(groupId, JSON.stringify({
								value: groupInnerItems,
								type: 'group',
							}));
						}
					}

					// Remove group items from the original items.
					items = Array.prototype.slice.call(items).filter((item) => Array.prototype.slice.call(groupInner).indexOf(item) == -1);
				}
			}
		}

		// Iterate all form items.
		for (const [key, item] of Object.entries(items)) { // eslint-disable-line no-unused-vars
			const {
				type,
				name,
				id,
				disabled,
				checked,
				dataset: {
					objectTypeId, // Used for HubSpot only
				}
			} = item;
			if (disabled) {
				continue;
			}

			let {
				value,
			} = item;

			// Build data object.
			const data = {
				name,
				value,
				type,
			};

			// Adde internal type for additional logic in some integrations.
			data.internalType = item.getAttribute(this.utils.DATA_ATTRIBUTES.fieldTypeInternal);

			if (formType === 'hubspot') {
				data.objectTypeId = objectTypeId ?? '';
			}

			if (data.internalType === 'date' || data.internalType === 'datetime-local') {
				data.type = data.internalType;
			}

			switch (type) {
				case 'checkbox':
				case 'radio':
					// If checkbox/radio on empty change to empty value.
					if (!checked) {
						// If unchecked value attribute is added use that if not send an empty value.
						data.value = item.getAttribute(this.utils.DATA_ATTRIBUTES.fieldUncheckedValue) ?? '';
					}

					formData.append(id, JSON.stringify(data));

					break;
				case 'file':
					// If custom file use files got from the global object of files uploaded.
					const fileList = this.utils.FILES[formId][id] ?? []; // eslint-disable-line no-case-declarations

					// Loop files and append.
					if (fileList.length) {
						for (const [key, file] of Object.entries(fileList)) {
							formData.append(`${id}[${key}]`, file);
						}
					} else {
						formData.append(`${id}[0]`, JSON.stringify({}));
					}
					break;
				case 'select-one':
					// Bailout if select is part of the phone.
					if (item.closest(this.utils.fieldSelector).getAttribute(this.utils.DATA_ATTRIBUTES.fieldType) === 'phone') {
						break;
					}

					formData.append(id, JSON.stringify(data));
					break;
				case 'text':
					// Skip alt text input created by date time picker lib.
					if (item?.previousElementSibling?.classList?.contains('flatpickr-input')) {
						break;
					}

					formData.append(id, JSON.stringify(data));
					break;
				case 'tel':
					const phoneDisablePicker = Boolean(element.getAttribute(this.utils.DATA_ATTRIBUTES.phoneDisablePicker)); // eslint-disable-line no-case-declarations
					if (phoneDisablePicker) {
						formData.append(id, JSON.stringify(data));
						break;
					}

					const prefix = item.previousElementSibling.querySelector('select'); // eslint-disable-line no-case-declarations
					const selectedPrefix = prefix.options[prefix.selectedIndex].value; // eslint-disable-line no-case-declarations

					if (item.value) {
						data.value = `${selectedPrefix}${item.value}`;
					}

					formData.append(id, JSON.stringify(data));

					break;
				case 'textarea':
					const saveAsJson = Boolean(item.getAttribute(this.utils.DATA_ATTRIBUTES.saveAsJson)); // eslint-disable-line no-case-declarations

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

					formData.append(id, JSON.stringify(data));
					break;
				case 'search':
					// Skip search field from dropdown.
					break;
				default:
					formData.append(id, JSON.stringify(data));

					break;
			}
		}

		// Add form ID field.
		formData.append(this.utils.FORM_PARAMS.postId, JSON.stringify({
			name: this.utils.FORM_PARAMS.postId,
			value: formId,
			type: 'hidden',
		}));

		// Add form type field.
		formData.append(this.utils.FORM_PARAMS.type, JSON.stringify({
			name: this.utils.FORM_PARAMS.type,
			value: formType,
			type: 'hidden',
		}));

		// Add form action field.
		formData.append(this.utils.FORM_PARAMS.action, JSON.stringify({
			name: this.utils.FORM_PARAMS.action,
			value: element.getAttribute('action'),
			type: 'hidden',
		}));

		// Add action external field.
		formData.append(this.utils.FORM_PARAMS.actionExternal, JSON.stringify({
			name: this.utils.FORM_PARAMS.actionExternal,
			value: element.getAttribute(this.utils.DATA_ATTRIBUTES.actionExternal),
			type: 'hidden',
		}));

		// Add additional options for HubSpot only.
		if (formType === 'hubspot' && !this.utils.formIsAdmin) {
			formData.append(this.utils.FORM_PARAMS.hubspotCookie, JSON.stringify({
				name: this.utils.FORM_PARAMS.hubspotCookie,
				value: cookies.getCookie('hubspotutk'),
				type: 'hidden',
			}));

			formData.append(this.utils.FORM_PARAMS.hubspotPageName, JSON.stringify({
				name: this.utils.FORM_PARAMS.hubspotPageName,
				value: document.title,
				type: 'hidden',
			}));

			formData.append(this.utils.FORM_PARAMS.hubspotPageUrl, JSON.stringify({
				name: this.utils.FORM_PARAMS.hubspotPageUrl,
				value: window.location.href,
				type: 'hidden',
			}));
		}

		if (this.utils.formIsAdmin) {
			formData.append(this.utils.FORM_PARAMS.settingsType, JSON.stringify({
				name: this.utils.FORM_PARAMS.settingsType,
				value: element.getAttribute(this.utils.DATA_ATTRIBUTES.settingsType),
				type: 'hidden',
			}));

			if (singleSubmit) {
				formData.append(this.utils.FORM_PARAMS.singleSubmit, JSON.stringify({
					name: this.utils.FORM_PARAMS.singleSubmit,
					value: 'true',
					type: 'hidden',
				}));
			}
	
		}

		// Set localStorage to hidden field.
		if (this.enrichment.isEnrichmentUsed()) {
			const storage = this.enrichment.getLocalStorage();

			if (storage) {
				formData.append(this.utils.FORM_PARAMS.storage, JSON.stringify({
					name: this.utils.FORM_PARAMS.storage,
					value: storage,
					type: 'hidden',
				}));
			}
		}

		return formData;
	}

	/**
	 * Setup Regular field.
	 *
	 * @param {object} input Input element.
	 *
	 * @public
	 */
	setupInputField(input) {
		this.utils.preFillOnInit(input, input.type);

		input.addEventListener('keydown', this.utils.onFocusEvent);
		input.addEventListener('focus', this.utils.onFocusEvent);
		input.addEventListener('blur', this.utils.onBlurEvent);
	}

	/**
	 * Setup Date time field.
	 * 
	 * @param {object} date Input element.
	 * @param {string} formId Form Id specific to one form.
	 *
	 * @public
	 */
	setupDateField(date, formId) {
		import('flatpickr').then((flatpickr) => {
			const {type} = date;

			const previewFormat = date.getAttribute(this.utils.DATA_ATTRIBUTES.datePreviewFormat);
			const outputFormat = date.getAttribute(this.utils.DATA_ATTRIBUTES.dateOutputFormat);

			const datePicker = flatpickr.default(date, {
				enableTime: type === 'datetime-local',
				dateFormat: outputFormat,
				altFormat: previewFormat,
				altInput: true,
			});

			this.utils.CUSTOM_DATES[formId].push(datePicker);
		});
	}

	/**
	 * Setup Select field.
	 * 
	 * @param {object} select Input element.
	 * @param {string} formId Form Id specific to one form.
	 *
	 * @public
	 */
	setupSelectField(select, formId) {
		import('choices.js').then((Choices) => {
			const selectShowCountryIcons = select.getAttribute(this.utils.DATA_ATTRIBUTES.selectShowCountryIcons);
			const selectPlaceholder = select.getAttribute(this.utils.DATA_ATTRIBUTES.selectPlaceholder);
			const selectAllowSearch = select.getAttribute(this.utils.DATA_ATTRIBUTES.selectAllowSearch);

			const choices = new Choices.default(select, {
				searchEnabled: Boolean(selectAllowSearch),
				shouldSort: false,
				position: 'bottom',
				allowHTML: true,
				placeholder: Boolean(selectPlaceholder),
				searchFields: ['label', 'value', 'customProperties'],
				itemSelectText: '',
				classNames: {
					containerOuter: `choices ${this.utils.selectClassName}`,
				},
				callbackOnCreateTemplates: function() {
					return {
						choice: (...args) => {
							const element = Choices.default.defaults.templates.choice.call(this, ...args);

							// Implement changes for phone picker.
							if (selectShowCountryIcons) {
								const findItem = this.config.choices.find((item) => item.label === element.innerHTML).customProperties;
								element.dataset.customProperties = findItem;
								return element;
							}

							return element;
						},
					};
				},
			});

			select.setAttribute(this.utils.DATA_ATTRIBUTES.selectInitial, choices.config.choices.find((item) => item.selected === true)?.value);

			Object.assign(choices, {
				esFormsFieldType: select.closest(this.utils.fieldSelector).getAttribute(this.utils.DATA_ATTRIBUTES.fieldType),
			});

			this.utils.preFillOnInit(choices, 'select');

			this.utils.CUSTOM_SELECTS[formId].push(choices);

			select.closest('.choices').addEventListener('focus', this.utils.onFocusEvent);
			select.closest('.choices').addEventListener('blur', this.utils.onBlurEvent);
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
	setupTextareaField(textarea, formId) {
		this.utils.preFillOnInit(textarea, 'textarea');

		textarea.addEventListener('keydown', this.utils.onFocusEvent);
		textarea.addEventListener('focus', this.utils.onFocusEvent);
		textarea.addEventListener('blur', this.utils.onBlurEvent);

		import('autosize').then((autosize) => {
			textarea.setAttribute('rows', '1');
			textarea.setAttribute('cols', '');

			autosize.default(textarea);

			this.utils.CUSTOM_TEXTAREAS[formId].push(autosize.default);
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
	setupFileField(file, formId, index) {
		const fileId = file?.id;

		if (typeof this.utils.FILES[formId] === 'undefined') {
			this.utils.FILES[formId] = {};
		}

		if (typeof this.utils.FILES[formId][fileId] === 'undefined') {
			this.utils.FILES[formId][fileId] = [];
		}

		import('dropzone').then((Dropzone) => {
			// Init dropzone.
			const myDropzone = new Dropzone.default(
				file.closest(this.utils.fieldSelector),
				{
					url: "/",
					addRemoveLinks: true,
					autoProcessQueue: false,
					autoDiscover: false,
					maxFiles: !file.multiple ? 1 : null,
					dictRemoveFile: this.utils.SETTINGS.FILE_CUSTOM_REMOVE_LABEL,
				}
			);

			this.utils.CUSTOM_FILES[formId].push(myDropzone);

			// On add one file.
			myDropzone.on("addedfile", (file) => {
				setTimeout(() => {
					file.previewTemplate.classList.add(this.utils.SELECTORS.CLASS_ACTIVE);
				}, 200);

				setTimeout(() => {
					file.previewTemplate.classList.add(this.utils.SELECTORS.CLASS_FILLED);
				}, 1200);

				this.utils.FILES[formId][fileId].push(file);
			});

			// On max file size reached.
			myDropzone.on('maxfilesreached', () => {
				myDropzone.removeEventListeners();
			});

			// On error while upload.
			myDropzone.on("error", (file) => {
				setTimeout(() => {
					file.previewTemplate.classList.add(this.utils.SELECTORS.CLASS_HAS_ERROR);
				}, 1500);

				const itemsLeft = this.utils.FILES[formId][fileId].filter((item) => item.upload.uuid !== file.upload.uuid);

				this.utils.FILES[formId][fileId] = [...itemsLeft];
			});

			// On remove files.
			myDropzone.on("removedfile", (file) => {
				const itemsLeft = this.utils.FILES[formId][fileId].filter((item) => item.upload.uuid !== file.upload.uuid);

				this.utils.FILES[formId][fileId] = [...itemsLeft];

				myDropzone.setupEventListeners();
			});

			// Trigger on wrap click.
			file.nextElementSibling.setAttribute('dropzone-index', index);
			file.nextElementSibling.setAttribute('dropzone-form-id', formId);
			file.nextElementSibling.addEventListener('click', this.onCustomFileWrapClickEvent);

			// Button inside wrap element.
			const button = file.parentNode.querySelector('a');

			button.addEventListener('focus', this.utils.onFocusEvent);
			button.addEventListener('blur', this.utils.onBlurEvent);
		});
	}

	/**
	 * Sync phone and country fields on change.
	 *
	 * @param {object} form Form element
	 * @param {string} formId Form Id.
	 *
	 * @returns void
	 */
	setupPhoneSync(form, formId) {
		const phoneDisablePicker = Boolean(form.getAttribute(this.utils.DATA_ATTRIBUTES.phoneDisablePicker));
		if (phoneDisablePicker) {
			return;
		}

		const phoneSync = Boolean(form.getAttribute(this.utils.DATA_ATTRIBUTES.phoneSync));

		if (!phoneSync) {
			return;
		}

		// Set interval because of dynamic import of choices.
		const interval = setInterval(() => {
			if (window[this.utils.prefix].utils.FORMS?.[formId]) {
				clearInterval(interval);

				const selects = window[this.utils.prefix].utils.CUSTOM_SELECTS[formId];

				if (selects.length !== 0) {
					// Find all countries.
					const country = selects.find((element) => element.esFormsFieldType === 'country');

					// Find all phones.
					const phones = selects.filter((element) => element.esFormsFieldType === 'phone');

					if (country) {
						// Loop all phones.
						phones.map((element) => {
							// Set phone init value by checking the contry.
							element.setChoiceByValue(country.getValue()?.customProperties);

							// Set contry value on any phone change.
							// TODO: Remove events.
							element.passedElement.element.addEventListener(
								'change',
								function(event) {
									country.setChoiceByValue(country.config.choices.find((item) => item.customProperties === event.srcElement[0].dataset.customProperties).value);
								},
								false,
							);
						});

						// Set phones value on country change.
						// TODO: Remove events.
						country.passedElement.element.addEventListener(
							'change',
							function(event) {
								phones.map((element) => {
									element.setChoiceByValue(element.config.choices.find((item) => item.customProperties === event.srcElement[0].dataset.customProperties).value);
								});
							},
							false,
						);
					}
				}
			}
		}, 100);
	}

	/**
	 * Remove all event listeners from elements.
	 * 
	 * @public
	 */
	removeEvents() {
		const elements = document.querySelectorAll(this.utils.formSelector);

		[...elements].forEach((element) => {
			// Regular submit.
			element.removeEventListener('submit', this.onFormSubmitEvent);

			const formId = element.getAttribute(this.utils.DATA_ATTRIBUTES.formPostId);

			const inputs = element.querySelectorAll(this.utils.inputSelector);
			const textareas = element.querySelectorAll(this.utils.textareaSelector);
			const selects = element.querySelectorAll(this.utils.selectSelector);
			const files = element.querySelectorAll(this.utils.fileSelector);

			[...inputs].forEach((input) => {
				switch (input.type) {
					case 'date':
					case 'datetime-local':
						if (typeof this.utils.CUSTOM_DATES?.[formId] !== 'undefined') {
							delete this.utils.CUSTOM_DATES[formId];
						}
						break;
				}

				input.removeEventListener('keydown', this.utils.onFocusEvent);
				input.removeEventListener('focus', this.utils.onFocusEvent);
				input.removeEventListener('blur', this.utils.onBlurEvent);
			});

			[...selects].forEach(() => {
				if (typeof this.utils.CUSTOM_SELECTS?.[formId] !== 'undefined') {
					delete this.utils.CUSTOM_SELECTS[formId];
				}
			});

			// Setup textarea inputs.
			[...textareas].forEach((textarea) => {
				textarea.removeEventListener('keydown', this.utils.onFocusEvent);
				textarea.removeEventListener('focus', this.utils.onFocusEvent);
				textarea.removeEventListener('blur', this.utils.onBlurEvent);

				if (typeof this.utils.CUSTOM_TEXTAREAS?.[formId] !== 'undefined') {
					delete this.utils.CUSTOM_TEXTAREAS[formId];
				}
			});

			// Setup file single inputs.
			[...files].forEach((file) => {
				if (typeof this.utils.CUSTOM_FILES?.[formId] !== 'undefined') {
					delete this.utils.CUSTOM_FILES[formId];
				}

				file.nextElementSibling.removeEventListener('click', this.onCustomFileWrapClickEvent);

				const button = file.parentNode.querySelector('a');

				button.removeEventListener('focus', this.utils.onFocusEvent);
				button.removeEventListener('blur', this.utils.onBlurEvent);
			});

			this.utils.dispatchFormEvent(element, this.utils.EVENTS.AFTER_FORM_EVENTS_CLEAR);
		});
	}

	runFormCaptcha(element = '') {
		if (!this.utils.isCaptchaUsed()) {
			return;
		}

		const actionName = this.utils.SETTINGS.CAPTCHA['submitAction'];
		const siteKey = this.utils.SETTINGS.CAPTCHA['siteKey'];

		if (this.utils.isCaptchaEnterprise()) {
			grecaptcha.enterprise.ready(async () => {
				await grecaptcha.enterprise.execute(siteKey, {action: actionName}).then((token) => {
					this.formSubmitCaptcha(element, token, 'enterprise', actionName);
				});
			});
		} else {
			grecaptcha.ready(async () => {
				await grecaptcha.execute(siteKey, {action: actionName}).then((token) => {
					this.formSubmitCaptcha(element, token, 'free', actionName);
				});
			});
		}
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

		const element = event.target;

		if (this.utils.isCaptchaUsed()) {
			this.runFormCaptcha(element);
		} else {
			this.formSubmit(element);
		}
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

		const index = event.currentTarget.getAttribute('dropzone-index');
		const formId = event.currentTarget.getAttribute('dropzone-form-id');

		this.utils.CUSTOM_FILES[formId][index].hiddenFileInput.click();
	};

	/**
	 * Handle form submit and all logic for only one field click.
	 *
	 * @param {object} event Event callback.
	 *
	 * @private
	 */
	onFormSubmitSingleEvent = (event) => {
		event.preventDefault();
		const {target} = event;

		this.formSubmit(target.closest(this.utils.formSelector), target);
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
		if (typeof window[this.prefix]?.form === 'undefined') {
			window[this.utils.prefix].form = {
				init: () => {
					this.init();
				},
				initOnlyForms: () => {
					this.initOnlyForms();
				},
				initOne: (element) => {
					this.initOne(element);
				},
				onFormSubmitEvent: (event) => {
					this.onFormSubmitEvent(event);
				},
				formSubmitCaptcha: (element, token, type) => {
					this.formSubmitCaptcha(element, token, type);
				},
				formSubmit: (element) => {
					this.formSubmit(element);
				},
				getFormData: (element) => {
					this.getFormData(element);
				},
				setupInputField: (input) => {
					this.setupInputField(input);
				},
				setupSelectField: (select, formId) => {
					this.setupSelectField(select, formId);
				},
				setupTextareaField: (textarea, formId) => {
					this.setupTextareaField(textarea, formId);
				},
				setupFileField: (file, formId, index) => {
					this.setupFileField(file, formId, index);
				},
				setupPhoneSync: (form, formId) => {
					this.setupPhoneSync(form, formId);
				},
				onCustomFileWrapClickEvent: (event) => {
					this.onCustomFileWrapClickEvent(event);
				},
				removeEvents: () => {
					this.removeEvents();
				},
				phoneSync: () => {
					this.phoneSync();
				},
				runFormCaptcha: (element) => {
					this.runFormCaptcha(element);
				},
			};
		}
	}
}
