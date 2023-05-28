/* global esFormsLocalization */

import { Data } from "./data";

export class State {
	constructor(options = {}) {
		this.data = new Data(options);

		// State names.
		this.SELECTS = 'selects';
		this.FILES = 'files';
		this.CHECKBOXES = 'checkboxes';
		this.RADIOS = 'radios';
		this.ISLOADED = 'isloaded';
		this.FIELDS = 'cfFields';
		this.VALUES = 'cfValues';
		this.DEFAULTS = 'cfDefaults';
		this.EVENTS = 'cfEvents';
		this.REFERENCE = 'cfReference';
		this.ELEMENTS = 'elements';
		this.FORM = 'form';

		this.METHOD = 'method';
		this.ACTION = 'action';
		this.ACTION_EXTERNAL = 'actionExternal';
		this.FIELD = 'field';
		this.VALUE = 'value';
		this.VALUE_COUNTRY = 'valueCountry';
		this.INPUT = 'input';
		this.INPUT_SELECT = 'inputSelect';
		this.ITEMS = 'items';
		this.CUSTOM = 'custom';
		this.TYPE = 'type';
		this.TYPE_SETTINGS = 'typeSettings';
		this.INTERNAL_TYPE = 'internalType';
		this.NAME = 'name';
		this.ERROR = 'error';
		this.ERROR_GLOBAL = 'errorGlobal';
		this.STATUS = 'status';
		this.MSG = 'msg';
		this.HASERROR = 'hasError';
		this.LOADED = 'loaded';
		this.CONFIG = 'config';
		this.LOADER = 'loader';
		this.ELEMENT = 'element';
		this.HEADING_SUCCESS = 'headingSuccess';
		this.HEADING_ERROR = 'headingError';
		this.IS_SINGLE_SUBMIT = 'isSingleSubmit';
		this.IS_ADMIN = 'isAdmin';
		this.SUBMIT_URL = 'submitUrl';
		this.NONCE = 'nonce';

		this.CONFIG_SELECT_USE_PLACEHOLDER = 'usePlaceholder';
		this.CONFIG_SELECT_USE_SEARCH = 'useSearch';
		this.CONFIG_PHONE_DISABLE_PICKER = 'disablePicker';
		this.CONFIG_PHONE_USE_PHONE_SYNC = 'usePhoneSync';
		this.CONFIG_DISABLE_SCROLL_TO_GLOBAL_MSG_ON_SUCCESS = 'disableScrollToGlobalMsgOnSuccess';
		this.CONFIG_DISABLE_SCROLL_TO_FIELD_ON_ERROR = 'disableScrollToFieldOnError';
		this.CONFIG_FORM_RESET_ON_SUCCESS= 'formResetOnSuccess';
		this.CONFIG_SUCCESS_REDIRECT= 'successRedirect';
		this.CONFIG_SUCCESS_REDIRECT_VARIATION= 'successRedirectVariation';
		this.CONFIG_DOWNLOADS= 'downloads';
		this.CONFIG_FORM_DISABLE_NATIVE_REDIRECT_ON_SUCCESS = 'formDisableNativeRedirectOnSuccess'
		this.CONFIG_REDIRECTION_TIMEOUT = 'redirectionTimeout'
		this.CONFIG_HIDE_GLOBAL_MESSAGE_TIMEOUT = 'hideGlobalMessageTimeout'

		this.CAPTCHA = 'captcha';
		this.CAPTCHA_IS_USED = 'isUsed';
		this.CAPTCHA_SITE_KEY = 'site_key';
		this.CAPTCHA_IS_ENTERPRISE = 'isEnterprise';
		this.CAPTCHA_SUBMIT_ACTION = 'submitAction';
		this.CAPTCHA_INIT_ACTION = 'initAction';
		this.CAPTCHA_LOAD_ON_INIT = 'loadOnInit';
		this.CAPTCHA_HIDE_BADGE = 'hideBadge';
	}
	// Set state initial.
	setFormStateInitial(formId) {
		if (!window[this.data.prefix]?.state?.[`form_${formId}`]) {
			window[this.data.prefix].state = {
				...window[this.data.prefix].state,
				[`form_${formId}`]: {
					[this.SELECTS]: {},
					[this.FILES]: {},
					[this.CHECKBOXES]: {},
					[this.RADIOS]: {},
					[this.ISLOADED]: false,

					[this.FIELDS]: {},
					[this.VALUES]: {},
					[this.DEFAULTS]: {},
					[this.EVENTS]: {},
					[this.REFERENCE]: {},
					[this.ELEMENTS]: {},
				}
			}
		}

		let formElement = '';

		if (formId === 0) {
			formElement = document.querySelector(this.data.formSelector);
		} else {
			formElement = document.querySelector(`${this.data.formSelector}[${this.data.DATA_ATTRIBUTES.formPostId}="${formId}"]`);
		}

		this.setState([this.FORM, this.SUBMIT_URL], this.data.formSubmitRestApiUrl, formId);
		this.setState([this.FORM, this.IS_ADMIN], this.data.formIsAdmin, formId);
		this.setState([this.FORM, this.ELEMENT], formElement, formId);
		this.setState([this.FORM, this.TYPE], formElement.getAttribute(this.data.DATA_ATTRIBUTES.formType), formId);
		this.setState([this.FORM, this.METHOD], formElement.getAttribute('method'), formId);
		this.setState([this.FORM, this.ACTION], formElement.getAttribute('action'), formId);
		this.setState([this.FORM, this.ACTION_EXTERNAL], formElement.getAttribute(this.data.DATA_ATTRIBUTES.actionExternal), formId);
		this.setState([this.FORM, this.TYPE_SETTINGS], formElement.getAttribute(this.data.DATA_ATTRIBUTES.settingsType), formId);
		this.setState([this.FORM, this.LOADER], formElement.querySelector(this.data.loaderSelector), formId);
		this.setState([this.FORM, this.NONCE], esFormsLocalization.nonce, formId);

		this.setState([this.FORM, this.CONFIG, this.CONFIG_PHONE_DISABLE_PICKER], Boolean(formElement.getAttribute(this.data.DATA_ATTRIBUTES.phoneDisablePicker)), formId);
		this.setState([this.FORM, this.CONFIG, this.CONFIG_PHONE_USE_PHONE_SYNC], Boolean(formElement.getAttribute(this.data.DATA_ATTRIBUTES.phoneSync)), formId);
		this.setState([this.FORM, this.CONFIG, this.CONFIG_DISABLE_SCROLL_TO_GLOBAL_MSG_ON_SUCCESS], Boolean(esFormsLocalization.formDisableScrollToGlobalMessageOnSuccess), formId);
		this.setState([this.FORM, this.CONFIG, this.CONFIG_DISABLE_SCROLL_TO_FIELD_ON_ERROR], Boolean(esFormsLocalization.formDisableScrollToFieldOnError), formId);
		this.setState([this.FORM, this.CONFIG, this.CONFIG_FORM_RESET_ON_SUCCESS], Boolean(esFormsLocalization.formResetOnSuccess), formId);
		this.setState([this.FORM, this.CONFIG, this.CONFIG_SUCCESS_REDIRECT], formElement.getAttribute(this.data.DATA_ATTRIBUTES.successRedirect), formId);
		this.setState([this.FORM, this.CONFIG, this.CONFIG_SUCCESS_REDIRECT_VARIATION], formElement.getAttribute(this.data.DATA_ATTRIBUTES.successRedirectVariation), formId);
		this.setState([this.FORM, this.CONFIG, this.CONFIG_DOWNLOADS], formElement.getAttribute(this.data.DATA_ATTRIBUTES.downloads), formId);
		this.setState([this.FORM, this.CONFIG, this.CONFIG_FORM_DISABLE_NATIVE_REDIRECT_ON_SUCCESS], Boolean(esFormsLocalization.formDisableNativeRedirectOnSuccess), formId);
		this.setState([this.FORM, this.CONFIG, this.CONFIG_REDIRECTION_TIMEOUT], esFormsLocalization.redirectionTimeout ?? 600, formId);
		this.setState([this.FORM, this.CONFIG, this.CONFIG_HIDE_GLOBAL_MESSAGE_TIMEOUT], esFormsLocalization.hideGlobalMessageTimeout ?? 6000, formId);

		const errorGlobal = formElement.querySelector(this.data.globalMsgSelector);

		this.setState([this.FORM, this.ERROR_GLOBAL, this.ELEMENT], errorGlobal, formId);
		this.setState([this.FORM, this.ERROR_GLOBAL, this.STATUS], '', formId);
		this.setState([this.FORM, this.ERROR_GLOBAL, this.MSG], '', formId);
		this.setState([this.FORM, this.ERROR_GLOBAL, this.HEADING_SUCCESS], errorGlobal.getAttribute(this.data.DATA_ATTRIBUTES.globalMsgHeadingSuccess), formId);
		this.setState([this.FORM, this.ERROR_GLOBAL, this.HEADING_ERROR], errorGlobal.getAttribute(this.data.DATA_ATTRIBUTES.globalMsgHeadingError), formId);

		const captcha = esFormsLocalization.captcha ?? [];

		this.setState([this.CAPTCHA, this.CAPTCHA_IS_USED], !captcha, formId);

		if (captcha) {
			this.setState([this.CAPTCHA, this.CAPTCHA_SITE_KEY], captcha.siteKey, formId);
			this.setState([this.CAPTCHA, this.CAPTCHA_IS_ENTERPRISE], captcha.isEnterprise, formId);
			this.setState([this.CAPTCHA, this.CAPTCHA_SUBMIT_ACTION], captcha.submitAction, formId);
			this.setState([this.CAPTCHA, this.CAPTCHA_INIT_ACTION], captcha.initAction, formId);
			this.setState([this.CAPTCHA, this.CAPTCHA_LOAD_ON_INIT], captcha.loadOnInit, formId);
			this.setState([this.CAPTCHA, this.CAPTCHA_HIDE_BADGE], captcha.hideBadge, formId);
		}

		// Find all fields.
		let items = formElement.querySelectorAll('input, select, textarea');

		// Loop all fields.
		for (const [key, item] of Object.entries(items)) {
			const {
				value,
				name,
				type,
			} = item;

			if (name === 'search_terms') {
				continue;
			}

			const field = formElement.querySelector(`${this.data.fieldSelector}[${this.data.DATA_ATTRIBUTES.fieldName}="${name}"]`);

			// Make changes depending on the field type.
			switch (type) {
				case 'radio':
				case 'checkbox':
					this.setState([this.ELEMENTS, name, this.VALUE, value], item.checked ? value : '', formId);
					this.setState([this.ELEMENTS, name, this.TYPE], type, formId);
					this.setState([this.ELEMENTS, name, this.INTERNAL_TYPE], type, formId);
					this.setState([this.ELEMENTS, name, this.INPUT], '', formId);
					this.setState([this.ELEMENTS, name, this.ITEMS, value, this.VALUE], value, formId);
					this.setState([this.ELEMENTS, name, this.ITEMS, value, this.FIELD], item.parentNode.parentNode, formId);
					this.setState([this.ELEMENTS, name, this.ITEMS, value, this.INPUT], item, formId);
					this.setState([this.ELEMENTS, name, this.ITEMS, value, this.NAME], name, formId);
					break;
				case 'select-one':
					// Combined fields like phone can have field null.
					const customField = this.getFormFieldElementByChild(item);
					const customType = customField.getAttribute(this.data.DATA_ATTRIBUTES.fieldType);
					const customData = JSON.parse(item.options[item.options.selectedIndex].getAttribute(this.data.DATA_ATTRIBUTES.selectCustomProperties));

					switch (customType) {
						case 'phone':
						case 'country':
							this.setState([this.ELEMENTS, name, this.VALUE_COUNTRY, 'code'], customData[this.data.DATA_ATTRIBUTES.selectCountryCode], formId);
							this.setState([this.ELEMENTS, name, this.VALUE_COUNTRY, 'label'], customData[this.data.DATA_ATTRIBUTES.selectCountryLabel], formId);
							this.setState([this.ELEMENTS, name, this.VALUE_COUNTRY, 'number'], customData[this.data.DATA_ATTRIBUTES.selectCountryNumber], formId);
							break;
						}

					if (customType !== 'phone') {
						this.setState([this.ELEMENTS, name, this.VALUE], value, formId);
					}
					this.setState([this.ELEMENTS, name, this.INTERNAL_TYPE], customType, formId);

					
					this.setState([this.ELEMENTS, name, this.TYPE], 'select', formId);
					this.setState([this.ELEMENTS, name, this.INPUT], item, formId);
					this.setState([this.ELEMENTS, name, this.CONFIG, this.CONFIG_SELECT_USE_PLACEHOLDER], Boolean(item.getAttribute(this.data.DATA_ATTRIBUTES.selectPlaceholder)), formId);
					this.setState([this.ELEMENTS, name, this.CONFIG, this.CONFIG_SELECT_USE_SEARCH], Boolean(item.getAttribute(this.data.DATA_ATTRIBUTES.selectAllowSearch)), formId);
					break;
				case 'tel':
					this.setState([this.ELEMENTS, name, this.VALUE], value, formId);
					this.setState([this.ELEMENTS, name, this.TYPE], type, formId);
					this.setState([this.ELEMENTS, name, this.INTERNAL_TYPE], 'tel', formId);
					this.setState([this.ELEMENTS, name, this.INPUT], item, formId);
					this.setState([this.ELEMENTS, name, this.INPUT_SELECT], field.querySelector('select'), formId);
					break;
				default:
					this.setState([this.ELEMENTS, name, this.VALUE], value, formId);
					this.setState([this.ELEMENTS, name, this.TYPE], type, formId);
					this.setState([this.ELEMENTS, name, this.INTERNAL_TYPE], type, formId);
					this.setState([this.ELEMENTS, name, this.INPUT], item, formId);
					break;
			}

			this.setState([this.ELEMENTS, name, this.ERRORMSG], '', formId);
			this.setState([this.ELEMENTS, name, this.HASERROR], false, formId);
			this.setState([this.ELEMENTS, name, this.LOADED], false, formId);
			this.setState([this.ELEMENTS, name, this.NAME], name, formId);
			this.setState([this.ELEMENTS, name, this.FIELD], field, formId);
			this.setState([this.ELEMENTS, name, this.ERROR], field.querySelector(this.data.errorSelector), formId);
			this.setState([this.ELEMENTS, name, this.IS_SINGLE_SUBMIT], item.classList.contains(this.data.submitSingleSelector.substring(1)), formId);
		}
	}

	setState(keyArray, value, formId) {
		const formKey = `form_${formId}`;
		let stateObject = window[this.data.prefix].state[formKey];

		keyArray.forEach((key, index) => {
			if (index === keyArray.length - 1) {
				stateObject[key] = value;
			} else {
				stateObject[key] = stateObject[key] || {};
				stateObject = stateObject[key];
			}
		});

		window[this.data.prefix].state[formKey] = {
			...window[this.data.prefix].state[formKey],
			...stateObject[keyArray[0]]
		};
	}

	// Set state array by key.
	setStateArray(keyArray, value, formId) {
		const formKey = `form_${formId}`;
		let stateObject = window[this.data.prefix].state[formKey];
	
		keyArray.forEach((key, index) => {
			if (index === keyArray.length - 1) {
				stateObject[key] = stateObject[key] || [];
				stateObject[key].push(value);
			} else {
				stateObject[key] = stateObject[key] || {};
				stateObject = stateObject[key];
			}
		});
	
		if (keyArray.length === 1) {
			window[this.data.prefix].state[formKey] = {
				...window[this.data.prefix].state[formKey],
				...stateObject,
			};
		} else {
			window[this.data.prefix].state[formKey] = {
				...window[this.data.prefix].state[formKey],
				[keyArray[0]]: stateObject,
			};
		}
	}

	// Delete state item by key
	deleteState(key, formId) {
		// this.setFormStateByKey(key, [], formId);
	}

	// Get state by keys.
	getState(keys, formId) {
		let stateObject = window?.[this.data.prefix]?.state?.[`form_${formId}`];
	
		if (!stateObject) {
			return undefined;
		}
	
		keys.forEach((key) => {
			stateObject = stateObject[key];
			if (!stateObject) {
				return undefined;
			}
		});
	
		return stateObject;
	}

	/**
	 * Init fields initial values data.
	 *
	 * @param {object} element Form element.
	 * @param {bool} isInit If this method is used in initial state or on every change.
	 *
	 * @returns void
	 */
	setValues(item, formId) {
		const {
			name,
			value,
			checked,
			type,
		} = item;

		switch (type) {
			case 'checkbox':
				this.setState([this.ELEMENTS, name, this.VALUE, value], checked ? value : '', formId);
				break;
			case 'select-one':
				const customField = this.getFormFieldElementByChild(item);
				const customType = customField.getAttribute(this.data.DATA_ATTRIBUTES.fieldType);
				const customData = JSON.parse(item.options[item.options.selectedIndex].getAttribute(this.data.DATA_ATTRIBUTES.selectCustomProperties));

				switch (customType) {
					case 'phone':
					case 'country':
						this.setState([this.ELEMENTS, name, this.VALUE_COUNTRY, 'code'], customData[this.data.DATA_ATTRIBUTES.selectCountryCode], formId);
						this.setState([this.ELEMENTS, name, this.VALUE_COUNTRY, 'label'], customData[this.data.DATA_ATTRIBUTES.selectCountryLabel], formId);
						this.setState([this.ELEMENTS, name, this.VALUE_COUNTRY, 'number'], customData[this.data.DATA_ATTRIBUTES.selectCountryNumber], formId);
						break;
				}

				if (customType !== 'phone') {
					this.setState([this.ELEMENTS, name, this.VALUE], value, formId);
				}
				break;
			default:
				this.setState([this.ELEMENTS, name, this.VALUE], value, formId);
				break;
		}
	}

	getStateFilteredBykey(obj, targetKey, findItem, formId) {
		return Object.values(Object.fromEntries(Object.entries(this.getState([obj], formId)).filter(([key, value]) => value[targetKey] === findItem)));
	}

	// ----------------------------------------
	// Form
	getStateForm(formId) {
		return this.getState([this.FORM], formId);
	}

	getStateFormSubmitUrl(formId, sufix) {
		return `${this.getState([this.FORM, this.SUBMIT_URL], formId)}${sufix}` ;
	}

	getStateFormIsAdmin(formId) {
		return this.getState([this.FORM, this.IS_ADMIN], formId);
	}

	getStateFormType(formId) {
		return this.getState([this.FORM, this.TYPE], formId);
	}

	getStateFormMethod(formId) {
		return this.getState([this.FORM, this.METHOD], formId);
	}

	getStateFormAction(formId) {
		return this.getState([this.FORM, this.ACTION], formId);
	}

	getStateFormActionExternal(formId) {
		return this.getState([this.FORM, this.ACTION_EXTERNAL], formId);
	}

	getStateFormTypeSettings(formId) {
		return this.getState([this.FORM, this.TYPE_SETTINGS], formId);
	}

	getStateFormNonce(formId) {
		return this.getState([this.FORM, this.NONCE], formId);
	}

	getStateFormLoader(formId) {
		return this.getState([this.FORM, this.LOADER], formId);
	}

	getStateFormErrorGlobalElement(formId) {
		return this.getState([this.FORM, this.ERROR_GLOBAL, this.ELEMENT], formId);
	}

	getStateFormErrorGlobalStatus(formId) {
		return this.getState([this.FORM, this.ERROR_GLOBAL, this.STATUS], formId);
	}

	getStateFormErrorGlobalMsg(formId) {
		return this.getState([this.FORM, this.ERROR_GLOBAL, this.MSG], formId);
	}

	getStateFormErrorGlobalStatus(formId) {
		return this.getState([this.FORM, this.ERROR_GLOBAL, this.STATUS], formId);
	}

	getStateFormErrorGlobalHeadingSuccess(formId) {
		return this.getState([this.FORM, this.ERROR_GLOBAL, this.HEADING_SUCCESS], formId);
	}

	getStateFormErrorGlobalHeadingError(formId) {
		return this.getState([this.FORM, this.ERROR_GLOBAL, this.HEADING_ERROR], formId);
	}

	getStateFormConfigPhoneDisablePicker(formId) {
		return this.getState([this.FORM, this.CONFIG, this.CONFIG_PHONE_DISABLE_PICKER], formId);
	}

	getStateFormConfigPhoneUseSync(formId) {
		return this.getState([this.FORM, this.CONFIG, this.CONFIG_PHONE_USE_PHONE_SYNC], formId);
	}

	getStateFormConfigDisableScrollToGlobalMsgOnSuccess(formId) {
		return this.getState([this.FORM, this.CONFIG, this.CONFIG_DISABLE_SCROLL_TO_GLOBAL_MSG_ON_SUCCESS], formId);
	}

	getStateFormConfigDisableScrollToFieldOnError(formId) {
		return this.getState([this.FORM, this.CONFIG, this.CONFIG_DISABLE_SCROLL_TO_FIELD_ON_ERROR], formId);
	}

	getStateFormConfigFormResetOnSuccess(formId) {
		return this.getState([this.FORM, this.CONFIG, this.CONFIG_FORM_RESET_ON_SUCCESS], formId);
	}

	getStateFormConfigSuccessRedirect(formId) {
		return this.getState([this.FORM, this.CONFIG, this.CONFIG_SUCCESS_REDIRECT], formId);
	}

	getStateFormConfigSuccessRedirectVariation(formId) {
		return this.getState([this.FORM, this.CONFIG, this.CONFIG_SUCCESS_REDIRECT_VARIATION], formId);
	}

	getStateFormConfigDownloads(formId) {
		return this.getState([this.FORM, this.CONFIG, this.CONFIG_DOWNLOADS], formId);
	}

	getStateFormConfigFormDisableNativeRedirectOnSuccess(formId) {
		return this.getState([this.FORM, this.CONFIG, this.CONFIG_FORM_DISABLE_NATIVE_REDIRECT_ON_SUCCESS], formId);
	}

	getStateFormConfigRedirectionTimeout(formId) {
		return this.getState([this.FORM, this.CONFIG, this.CONFIG_REDIRECTION_TIMEOUT], formId);
	}

	getStateFormConfigHideGlobalMessageTimeout(formId) {
		return this.getState([this.FORM, this.CONFIG, this.CONFIG_HIDE_GLOBAL_MESSAGE_TIMEOUT], formId);
	}

	// ----------------------------------------
	// Element

	getStateFormElement(formId) {
		return this.getState([this.FORM, this.ELEMENT], formId);
	}

	getStateElements(formId) {
		return Object.entries(this.getState([this.ELEMENTS], formId));
	}

	getStateElement(name, formId) {
		return this.getState([this.ELEMENTS, name], formId);
	}

	getStateElementConfig(name, type, formId) {
		return this.getState([this.ELEMENTS, name, this.CONFIG, type], formId);
	}

	getStateElementField(name, formId) {
		return this.getState([this.ELEMENTS, name, this.FIELD], formId);
	}

	getStateElementCustom(name, formId) {
		return this.getState([this.ELEMENTS, name, this.CUSTOM], formId);
	}

	getStateElementValueCountry(name, formId) {
		return this.getState([this.ELEMENTS, name, this.VALUE_COUNTRY], formId);
	}

	getStateElementItems(name, formId) {
		return this.getState([this.ELEMENTS, name, this.ITEMS], formId);
	}

	getStateElementItem(name, value, formId) {
		return this.getState([this.ELEMENTS, name, this.ITEMS, value], formId);
	}

	getStateElementInput(name, formId) {
		return this.getState([this.ELEMENTS, name, this.INPUT], formId);
	}

	getStateElementItemsInput(name, nameItem, formId) {
		return this.getState([this.ELEMENTS, name, this.ITEMS, nameItem, this.INPUT], formId);
	}

	getStateElementValue(name, formId) {
		return this.getState([this.ELEMENTS, name, this.VALUE], formId);
	}

	getStateElementError(name, formId) {
		return this.getState([this.ELEMENTS, name, this.ERROR], formId);
	}

	getStateElementHasError(name, formId) {
		return this.getState([this.ELEMENTS, name, this.HASERROR], formId);
	}

	getStateElementErrorMsg(name, formId) {
		return this.getState([this.ELEMENTS, name, this.ERRORMSG], formId);
	}

	getStateElementType(name, formId) {
		return this.getState([this.ELEMENTS, name, this.TYPE], formId);
	}

	getStateElementInputSelect(name, formId) {
		return this.getState([this.ELEMENTS, name, this.INPUT_SELECT], formId);
	}

	// ----------------------------------------
	// Captcha
	getStateCaptchaIsUsed(formId) {
		return this.getState([this.CAPTCHA, this.CAPTCHA_IS_USED], formId);
	}
	getStateCaptchaSiteKey(formId) {
		return this.getState([this.CAPTCHA, this.CAPTCHA_SITE_KEY], formId);
	}
	getStateCaptchaIsEnterprise(formId) {
		return this.getState([this.CAPTCHA, this.CAPTCHA_IS_ENTERPRISE], formId);
	}
	getStateCaptchaSubmitAction(formId) {
		return this.getState([this.CAPTCHA, this.CAPTCHA_SUBMIT_ACTION], formId);
	}
	getStateCaptchaInitAction(formId) {
		return this.getState([this.CAPTCHA, this.CAPTCHA_INIT_ACTION], formId);
	}
	getStateCaptchaLoadOnInit(formId) {
		return this.getState([this.CAPTCHA, this.CAPTCHA_LOAD_ON_INIT], formId);
	}
	getStateCaptchaHideBadge(formId) {
		return this.getState([this.CAPTCHA, this.CAPTCHA_HIDE_BADGE], formId);
	}

	// ----------------------------------------
	// Other

	getFormElementByChild(element) {
		return element.closest(`${this.data.formSelector}`);
	}

	getFormFieldElementByChild(element) {
		return element.closest(`${this.data.fieldSelector}`);
	}

	getFormIdByElement(element) {
		return this.getFormElementByChild(element).getAttribute(this.data.DATA_ATTRIBUTES.formPostId);
	}
}
