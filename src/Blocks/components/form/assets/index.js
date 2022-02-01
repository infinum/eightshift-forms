/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import { componentJsClass } from './../manifest.json';

domReady(() => {
	const selector = `.${componentJsClass}`;
	const elements = document.querySelectorAll(selector);

	if (elements.length && typeof esFormsLocalization !== 'undefined') {
		import('./form').then(({ Form, FORM_EVENTS, FORM_SELECTORS, FORM_DATA_ATTRIBUTES }) => {
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
			});

			form.init();

			window['esForms'] = {
				events: FORM_EVENTS,
				selectors: FORM_SELECTORS,
				dataAttributes: FORM_DATA_ATTRIBUTES,
				redirectionTimeout: form.redirectionTimeout,
				hideGlobalMessageTimeout: form.hideGlobalMessageTimeout,
				formSelector: form.formSelector,
				captchaSiteKey: esFormsLocalization.captcha,
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
				setupSelectField: (select) => {
					form.setupSelectField(select);
				},
				setupTextareaField: (textarea) => {
					form.setupTextareaField(textarea);
				},
				setupFileField: (file) => {
					form.setupFileField(file);
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
			};
		});
	}
});
