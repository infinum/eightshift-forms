/* global grecaptcha */

import { cookies } from '@eightshift/frontend-libs/scripts/helpers';
import { ConditionalTags } from './conditional-tags';
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

			// All fields selectors.
			const inputs = element.querySelectorAll(this.utils.inputSelector);
			const textareas = element.querySelectorAll(this.utils.textareaSelector);
			const selects = element.querySelectorAll(this.utils.selectSelector);
			const files = element.querySelectorAll(this.utils.fileSelector);

			// Setup regular inputs.
			[...inputs].forEach((input) => {
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

			// Triger event that form is fully loaded.
			this.utils.dispatchFormEvent(element, this.utils.EVENTS.FORM_JS_LOADED);
	}

	/**
	 *  Handle form submit and all logic in case we have captcha in place.
	 * 
	 * @param {object} element Form element.
	 * @param {string} token Captcha token from api.
	 *
	 * @public
	 */
	formSubmitCaptcha(element, token) {
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
				formId: element.getAttribute(this.utils.DATA_ATTRIBUTES.formPostId),
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
			if (response.code >= 200 && response.code <= 299) {
				this.formSubmit(element);
			}

			// Normal errors.
			if (response.status === 'error') {
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
		if (!this.utils.SETTINGS.CAPTCHA) {
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

		fetch(!this.utils.formIsAdmin ? `${this.utils.formSubmitRestApiUrl}-${formType}` : this.utils.formSubmitRestApiUrl, body)
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
				if (response.code >= 200 && response.code <= 299) {
					// Send GTM.
					this.utils.gtmSubmit(element);

					// Redirect on success.
					if (element.hasAttribute(this.utils.DATA_ATTRIBUTES.successRedirect) || singleSubmit) {
						// Dispatch event.
						this.utils.dispatchFormEvent(element, this.utils.EVENTS.AFTER_FORM_SUBMIT_SUCCESS_REDIRECT);

						// Set global msg.
						this.utils.setGlobalMsg(element, response.message, 'success');

						// Redirect to url and update url params from from data.
						this.utils.redirectToUrl(element, formData);
					} else {
						// Do normal success without redirect.
						// Dispatch event.
						this.utils.dispatchFormEvent(element, this.utils.EVENTS.AFTER_FORM_SUBMIT_SUCCESS);

						// Set global msg.
						this.utils.setGlobalMsg(element, response.message, 'success');

						// Clear form values.
						this.utils.resetForm(element);
					}
				}

				// On redirect with custom action state.
				if (response.code >= 300 && response.code <= 399) {
					// Send GTM.
					this.utils.gtmSubmit(element);

					// Set global msg.
					this.utils.setGlobalMsg(element, response.message, 'success');

					// Do the actual redirect after some time.
					setTimeout(() => {
						element.submit();
					}, parseInt(this.utils.SETTINGS.REDIRECTION_TIMEOUT, 10));
				}

				// Normal errors.
				if (response.status === 'error') {
					// Dispatch event.
					this.utils.dispatchFormEvent(element, this.utils.EVENTS.AFTER_FORM_SUBMIT_ERROR);

					// Set global msg.
					this.utils.setGlobalMsg(element, response.message, 'error');
				}

				// Validate fields error.
				if (response.status === 'error_validation') {
					// Dispatch event.
					this.utils.dispatchFormEvent(element, this.utils.EVENTS.AFTER_FORM_SUBMIT_ERROR_VALIDATION);

					// Output field errors.
					this.utils.outputErrors(element, response.validation);
				}

				// Hide global msg in any case after some time.
				setTimeout(() => {
					this.utils.hideGlobalMsg(element);
				}, parseInt(this.utils.SETTINGS.HIDE_GLOBAL_MESSAGE_TIMEOUT, 10));

				// Dispatch event.
				this.utils.dispatchFormEvent(element, this.utils.EVENTS.AFTER_FORM_SUBMIT_END);
			})
			.catch(() => {
				this.utils.setGlobalMsg(element, this.utils.SETTINGS.FORM_SERVER_ERROR_MSG, 'error');

				// Remove loader.
				this.utils.hideLoader(element);

				// Hide global msg in any case after some time.
				setTimeout(() => {
					this.utils.hideGlobalMsg(element);
				}, parseInt(this.utils.SETTINGS.HIDE_GLOBAL_MESSAGE_TIMEOUT, 10));
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

		const groups = element.querySelectorAll(`${this.utils.groupSelector}`);

		const formId = element.getAttribute(this.utils.DATA_ATTRIBUTES.formPostId);

		// Check if we are saving group items in one key.
		if (groups.length && !singleSubmit) {
			for (const [key, group] of Object.entries(groups)) { // eslint-disable-line no-unused-vars
				const groupId = group.getAttribute(this.utils.DATA_ATTRIBUTES.fieldId);
				const groupInner = group.querySelectorAll(`
					${this.utils.groupInnerSelector} input,
					${this.utils.groupInnerSelector} select,
					${this.utils.groupInnerSelector} textarea
				`);

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

					formData.append(groupId, JSON.stringify({
						value: groupInnerItems,
						type: 'group',
					}));
				}
			}
		}

		let items = element.querySelectorAll(`
			input:not(${this.utils.groupInnerSelector} input),
			select:not(${this.utils.groupInnerSelector} select),
			textarea:not(${this.utils.groupInnerSelector} textarea)
		`);

		const formType = element.getAttribute(this.utils.DATA_ATTRIBUTES.formType);

		// If single submit override items and pass only one item to submit.
		if (singleSubmit) {
			items = [
				singleSubmit
			];
		}

		// Iterate all form items.
		for (const [key, item] of Object.entries(items)) { // eslint-disable-line no-unused-vars
			const {
				type,
				name,
				id,
				files,
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

			// If checkbox/radio on empty change to empty value.
			if ((type === 'checkbox' || type === 'radio') && !checked) {
				// If unchecked value attribute is added use that if not send an empty value.
				data.value = item.getAttribute(this.utils.DATA_ATTRIBUTES.fieldUncheckedValue) ?? '';
			}

			// Append files field.
			if (type === 'file') {
				// Default use normal files form input.
				let fileList = files;

				// If custom file use files got from the global object of files uploaded.
				if (this.utils.isCustom(item)) {
					fileList = this.utils.FILES[formId][id] ?? [];
				}

				// Loop files and append.
				if (fileList.length) {
					for (const [key, file] of Object.entries(fileList)) {
						formData.append(`${id}[${key}]`, file);
					}
				} else {
					formData.append(`${id}[0]`, JSON.stringify({}));
				}
			} else {
				// Output/append all fields.
				formData.append(id, JSON.stringify(data));
			}
		}

		// Add form ID field.
		formData.append(this.utils.FORM_PARAMS.postId, JSON.stringify({
			value: formId,
			type: 'hidden',
		}));

		// Add form type field.
		formData.append(this.utils.FORM_PARAMS.type, JSON.stringify({
			value: formType,
			type: 'hidden',
		}));

		// Add form action field.
		formData.append(this.utils.FORM_PARAMS.action, JSON.stringify({
			value: element.getAttribute('action'),
			type: 'hidden',
		}));

		// Add action external field.
		formData.append(this.utils.FORM_PARAMS.actionExternal, JSON.stringify({
			value: element.getAttribute(this.utils.DATA_ATTRIBUTES.actionExternal),
			type: 'hidden',
		}));

		// Add additional options for HubSpot only.
		if (formType === 'hubspot' && !this.utils.formIsAdmin) {
			formData.append(this.utils.FORM_PARAMS.hubspotCookie, JSON.stringify({
				value: cookies.getCookie('hubspotutk'),
				type: 'hidden',
			}));

			formData.append(this.utils.FORM_PARAMS.hubspotPageName, JSON.stringify({
				value: document.title,
				type: 'hidden',
			}));

			formData.append(this.utils.FORM_PARAMS.hubspotPageUrl, JSON.stringify({
				value: window.location.href,
				type: 'hidden',
			}));
		}

		if (singleSubmit && this.utils.formIsAdmin) {
			formData.append(this.utils.FORM_PARAMS.singleSubmit, JSON.stringify({
				value: 'true',
				type: 'hidden',
			}));
		}

		// Set localStorage to hidden field.
		if (this.enrichment.isEnrichmentUsed()) {
			const storage = this.enrichment.getLocalStorage();
			if (storage) {
			 formData.append(this.utils.FORM_PARAMS.storage, JSON.stringify({
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
	 * Setup Select field.
	 * 
	 * @param {object} select Input element.
	 * @param {string} formId Form Id specific to one form.
	 *
	 * @public
	 */
	setupSelectField(select, formId) {
		const option = select.querySelector('option');

		if (this.utils.isCustom(select)) {
			import('choices.js').then((Choices) => {
				const choices = new Choices.default(select, {
					searchEnabled: false,
					shouldSort: false,
					position: 'bottom',
					allowHTML: true,
				});

				this.utils.preFillOnInit(choices, 'select-custom');

				this.utils.CUSTOM_SELECTS[formId].push(choices);

				select.closest('.choices').addEventListener('focus', this.utils.onFocusEvent);
				select.closest('.choices').addEventListener('blur', this.utils.onBlurEvent);
			});
		} else {
			this.utils.preFillOnInit(option, 'select');

			select.addEventListener('focus', this.utils.onFocusEvent);
			select.addEventListener('blur', this.utils.onBlurEvent);
		}
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

		if (this.utils.isCustom(textarea)) {
			import('autosize').then((autosize) => {
				textarea.setAttribute('rows', '1');
				textarea.setAttribute('cols', '');

				autosize.default(textarea);

				this.utils.CUSTOM_TEXTAREAS[formId].push(autosize.default);
			});
		}
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
		if (this.utils.isCustom(file)) {

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
				input.removeEventListener('keydown', this.utils.onFocusEvent);
				input.removeEventListener('focus', this.utils.onFocusEvent);
				input.removeEventListener('blur', this.utils.onBlurEvent);
			});

			[...selects].forEach((select) => {
				if (this.utils.isCustom(select)) {
					if (typeof this.utils.CUSTOM_SELECTS?.[formId] !== 'undefined') {
						delete this.utils.CUSTOM_SELECTS[formId];
					}
				} else {
					select.removeEventListener('focus', this.utils.onFocusEvent);
					select.removeEventListener('blur', this.utils.onBlurEvent);
				}
			});

			// Setup textarea inputs.
			[...textareas].forEach((textarea) => {
				textarea.removeEventListener('keydown', this.utils.onFocusEvent);
				textarea.removeEventListener('focus', this.utils.onFocusEvent);
				textarea.removeEventListener('blur', this.utils.onBlurEvent);

				if (this.utils.isCustom(textarea)) {
					if (typeof this.utils.CUSTOM_TEXTAREAS?.[formId] !== 'undefined') {
						delete this.utils.CUSTOM_TEXTAREAS[formId];
					}
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
			grecaptcha.ready(() => {
				grecaptcha.execute(this.utils.SETTINGS.CAPTCHA, {action: 'submit'}).then((token) => {
					this.formSubmitCaptcha(element, token);
				});
			});
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
				init() {
					this.init();
				},
				initOnlyForms() {
					this.initOnlyForms();
				},
				initOne(element) {
					this.initOne(element);
				},
				onFormSubmitEvent(event) {
					this.onFormSubmitEvent(event);
				},
				formSubmitCaptcha(element, token) {
					this.formSubmitCaptcha(element, token);
				},
				formSubmit(element) {
					this.formSubmit(element);
				},
				getFormData(element) {
					this.getFormData(element);
				},
				setupInputField(input) {
					this.setupInputField(input);
				},
				setupSelectField(select, formId) {
					this.setupSelectField(select, formId);
				},
				setupTextareaField(textarea, formId) {
					this.setupTextareaField(textarea, formId);
				},
				setupFileField(file, formId, index) {
					this.setupFileField(file, formId, index);
				},
				onCustomFileWrapClickEvent(event) {
					this.onCustomFileWrapClickEvent(event);
				},
				removeEvents() {
					this.removeEvents();
				},
			};
		}
	}
}
