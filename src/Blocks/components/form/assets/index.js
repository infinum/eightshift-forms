/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import { componentJsClass } from './../manifest.json';
import { FORM_EVENTS, FORM_SELECTORS, FORM_DATA_ATTRIBUTES } from './form';

if (typeof esFormsLocalization === 'undefined') {
	throw 'Your project is missing global variable esFormsLocalization called from the enqueue script in the forms.';
}

const selector = `.${componentJsClass}`;

// You can disable auto init from the admin.
const disableAutoInit = Boolean(esFormsLocalization.formDisableAutoInit) ?? false;

// Load window form no matter what the option is set.
window['esForms'] = {
	events: FORM_EVENTS,
	selectors: FORM_SELECTORS,
	dataAttributes: FORM_DATA_ATTRIBUTES,
	formSelector: selector,
	initAll: () => {
		initAll(true);
	},
};

// Load add data required for the forms to work.
function initAll(manual) {
	import('./form').then(({ Form }) => {
		const form = new Form({
			formSelector: selector,
			formSubmitRestApiUrl: esFormsLocalization.formSubmitRestApiUrl,
			redirectionTimeout: esFormsLocalization.redirectionTimeout,
			hideGlobalMessageTimeout: esFormsLocalization.hideGlobalMessageTimeout,
			hideLoadingStateTimeout: esFormsLocalization.hideLoadingStateTimeout,
			formDisableScrollToFieldOnError: esFormsLocalization.formDisableScrollToFieldOnError,
			formDisableScrollToGlobalMessageOnSuccess: esFormsLocalization.formDisableScrollToGlobalMessageOnSuccess,
			formResetOnSuccess: esFormsLocalization.formResetOnSuccess,
			fileCustomRemoveLabel: esFormsLocalization.fileCustomRemoveLabel,
			captcha: esFormsLocalization.captcha,
			storageConfig: esFormsLocalization.storageConfig,
		});

		// Bailout if form is loaded but you want to init form again.
		if (manual && !disableAutoInit) {
			throw 'You are trying to re-init form class that all-ready exists. Please review your code or disable auto-initialize scripts in the forms global settings.';
		}

		// Run forms.
		form.init();

		// Populate window object with the rest of the functions.
		window['esForms'] = {
			...window['esForms'],
			redirectionTimeout: form.redirectionTimeout,
			hideGlobalMessageTimeout: form.hideGlobalMessageTimeout,
			captchaSiteKey: esFormsLocalization.captcha,
			files: form.files,
			customSelects: form.customSelects,
			customFiles: form.customFiles,
			customTextareas: form.customTextareas,
			storageConfig: form.storageConfig,
			init: () => {
				form.init();
			},
			onFormSubmit: (event) => {
				form.onFormSubmit(event);
			},
			formSubmitCaptcha: (element, token) => {
				form.formSubmitCaptcha(element, token);
			},
			formSubmit: (element) => {
				form.formSubmit(element);
			},
			getFormData: (element) => {
				form.getFormData(element);
			},
			outputErrors: (element, fields) => {
				form.outputErrors(element, fields);
			},
			resetForm: (element) => {
				form.resetForm(element);
			},
			reset: (element) => {
				form.reset(element);
			},
			showLoader: (element) => {
				form.showLoader(element);
			},
			hideLoader: (element) => {
				form.hideLoader(element);
			},
			setGlobalMsg: (element, msg, status) => {
				form.setGlobalMsg(element, msg, status);
			},
			unsetGlobalMsg: (element) => {
				form.unsetGlobalMsg(element);
			},
			hideGlobalMsg: (element) => {
				form.hideGlobalMsg(element);
			},
			gtmSubmit: (element) => {
				form.gtmSubmit(element);
			},
			getGtmData: (element, eventName) => {
				form.getGtmData(element, eventName);
			},
			scrollToElement: (event) => {
				form.scrollToElement(event);
			},
			dispatchFormEvent: (event, name) => {
				form.dispatchFormEvent(event, name);
			},
			setupInputField: (input) => {
				form.setupInputField(input);
			},
			setupSelectField: (select, formId) => {
				form.setupSelectField(select, formId);
			},
			setupTextareaField: (textarea, formId) => {
				form.setupTextareaField(textarea, formId);
			},
			setupFileField: (file, formId, index) => {
				form.setupFileField(file, formId, index);
			},
			onCustomFileWrapClickEvent: (event) => {
				form.onCustomFileWrapClickEvent(event);
			},
			preFillOnInit: (input) => {
				form.preFillOnInit(input);
			},
			onFocusEvent: (event) => {
				form.onFocusEvent(event);
			},
			onBlurEvent: (event) => {
				form.onBlurEvent(event);
			},
			isCustom: (item) => {
				form.isCustom(item);
			},
			removeEvents: () => {
				form.removeEvents();
			},
			setLocalStorage: () => {
				form.setLocalStorage();
			},
			getLocalStorage: () => {
				form.getLocalStorage();
			},
		};
	});
}

// Load normal forms on dom ready event otherwise use manual trigger from the window object.
if (!disableAutoInit) {
	domReady(() => {
		const elements = document.querySelectorAll(selector);

		if (elements.length) {
			initAll(false);
		}
	});
}

