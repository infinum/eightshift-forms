import { Data } from "./data";

export class State {
	constructor() {
		this.data = new Data();

		// State names.
		this.SELECTS = 'selects';
		this.TEXTAREAS = 'textareas';
		this.DATES = 'dates';
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

		this.FIELD = 'field';
		this.VALUE = 'value';
		this.VALUECOUNTRY = 'valueCountry';
		this.INPUT = 'input';
		this.INPUTSELECT = 'inputSelect';
		this.ITEMS = 'items';
		this.TYPE = 'type';
		this.INTERNALTYPE = 'internalType';
		this.NAME = 'name';
		this.CHOICE = 'choice';
		this.LOADED = 'loaded';
		this.CONFIG = 'config';
		this.CONFIG_SELECT_USE_PLACEHOLDER = 'usePlaceholder';
		this.CONFIG_SELECT_USE_SEARCH = 'useSearch';
		this.CONFIG_PHONE_DISABLE_PICKER = 'disablePicker';
		this.CONFIG_PHONE_USE_PHONE_SYNC = 'usePhoneSync';
		this.FORMTYPE = 'type';
		this.FORMELEMENT = 'element';
	}
	// Set state initial.
	setFormStateInitial(formId) {
		if (!window[this.data.prefix]?.state?.[`form_${formId}`]) {
			window[this.data.prefix].state = {
				...window[this.data.prefix].state,
				[`form_${formId}`]: {
					[this.SELECTS]: {},
					[this.TEXTAREAS]: {},
					[this.DATES]: {},
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

		const formElement = document.querySelector(`${this.data.formSelector}[${this.data.DATA_ATTRIBUTES.formPostId}="${formId}"]`);

		this.setState([this.FORM, this.FORMELEMENT], formElement, formId);
		this.setState([this.FORM, this.FORMTYPE], formElement.getAttribute(this.data.DATA_ATTRIBUTES.formType), formId);
		this.setState([this.FORM, this.CONFIG_PHONE_DISABLE_PICKER], Boolean(formElement.getAttribute(this.data.DATA_ATTRIBUTES.phoneDisablePicker)), formId);
		this.setState([this.FORM, this.CONFIG_PHONE_USE_PHONE_SYNC], Boolean(formElement.getAttribute(this.data.DATA_ATTRIBUTES.phoneSync)), formId);

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
					this.setState([this.ELEMENTS, name, this.NAME], name, formId);
					this.setState([this.ELEMENTS, name, this.VALUE, value], item.checked ? value : '', formId);
					this.setState([this.ELEMENTS, name, this.FIELD], field, formId);
					this.setState([this.ELEMENTS, name, this.TYPE], type, formId);
					this.setState([this.ELEMENTS, name, this.INTERNALTYPE], type, formId);
					this.setState([this.ELEMENTS, name, this.LOADED], false, formId);

					this.setState([this.ELEMENTS, name, this.ITEMS, value, this.VALUE], value, formId);
					this.setState([this.ELEMENTS, name, this.ITEMS, value, this.FIELD], item.parentNode.parentNode, formId);
					this.setState([this.ELEMENTS, name, this.ITEMS, value, this.INPUT], item, formId);
					this.setState([this.ELEMENTS, name, this.ITEMS, value, this.NAME], name, formId);
					break;
				case 'select-one':
					// Combined fields like phone can have field null.
					const customField = this.getFormFieldElementByChild(item);
					const customType = customField.getAttribute(this.data.DATA_ATTRIBUTES.fieldType);
					const customName = customField.getAttribute(this.data.DATA_ATTRIBUTES.fieldName);
					const customData = JSON.parse(item.options[item.options.selectedIndex].getAttribute(this.data.DATA_ATTRIBUTES.selectCustomProperties));

					switch (customType) {
						case 'phone':
							this.setState([this.ELEMENTS, customName, this.VALUECOUNTRY, 'code'], customData[this.data.DATA_ATTRIBUTES.selectCountryCode], formId);
							this.setState([this.ELEMENTS, customName, this.VALUECOUNTRY, 'label'], customData[this.data.DATA_ATTRIBUTES.selectCountryLabel], formId);
							this.setState([this.ELEMENTS, customName, this.VALUECOUNTRY, 'number'], customData[this.data.DATA_ATTRIBUTES.selectCountryNumber], formId);
							this.setState([this.ELEMENTS, customName, this.INTERNALTYPE], 'phone', formId);
							this.setState([this.ELEMENTS, customName, this.VALUE], value, formId);
							break;
						case 'country':
							this.setState([this.ELEMENTS, name, this.VALUECOUNTRY, 'code'], customData[this.data.DATA_ATTRIBUTES.selectCountryCode], formId);
							this.setState([this.ELEMENTS, name, this.VALUECOUNTRY, 'label'], customData[this.data.DATA_ATTRIBUTES.selectCountryLabel], formId);
							this.setState([this.ELEMENTS, name, this.VALUECOUNTRY, 'number'], customData[this.data.DATA_ATTRIBUTES.selectCountryNumber], formId);
							this.setState([this.ELEMENTS, name, this.INTERNALTYPE], 'country', formId);
							this.setState([this.ELEMENTS, name, this.VALUE], value, formId);
							break;
						default:
							this.setState([this.ELEMENTS, name, this.INTERNALTYPE], 'select', formId);
							this.setState([this.ELEMENTS, name, this.VALUE], value, formId);
							break;
					}

					this.setState([this.ELEMENTS, name, this.NAME], name, formId);
					
					this.setState([this.ELEMENTS, name, this.FIELD], field, formId);
					this.setState([this.ELEMENTS, name, this.TYPE], 'select', formId);
					this.setState([this.ELEMENTS, name, this.LOADED], false, formId);
					this.setState([this.ELEMENTS, name, this.INPUT], item, formId);

					this.setState([this.ELEMENTS, name, this.CONFIG, this.CONFIG_SELECT_USE_PLACEHOLDER], Boolean(item.getAttribute(this.data.DATA_ATTRIBUTES.selectPlaceholder)), formId);
					this.setState([this.ELEMENTS, name, this.CONFIG, this.CONFIG_SELECT_USE_SEARCH], Boolean(item.getAttribute(this.data.DATA_ATTRIBUTES.selectAllowSearch)), formId);
					break;
				case 'tel':
					this.setState([this.ELEMENTS, name, this.NAME], name, formId);
					this.setState([this.ELEMENTS, name, this.VALUE], value, formId);
					this.setState([this.ELEMENTS, name, this.FIELD], field, formId);
					this.setState([this.ELEMENTS, name, this.TYPE], type, formId);
					this.setState([this.ELEMENTS, name, this.INTERNALTYPE], 'tel', formId);
					this.setState([this.ELEMENTS, name, this.LOADED], false, formId);
					this.setState([this.ELEMENTS, name, this.INPUT], item, formId);
					this.setState([this.ELEMENTS, name, this.INPUTSELECT], field.querySelector('select'), formId);
					break;
				default:
					this.setState([this.ELEMENTS, name, this.NAME], name, formId);
					this.setState([this.ELEMENTS, name, this.VALUE], value, formId);
					this.setState([this.ELEMENTS, name, this.FIELD], field, formId);
					this.setState([this.ELEMENTS, name, this.TYPE], type, formId);
					this.setState([this.ELEMENTS, name, this.INTERNALTYPE], type, formId);
					this.setState([this.ELEMENTS, name, this.LOADED], false, formId);
					this.setState([this.ELEMENTS, name, this.INPUT], item, formId);
					break;
			}
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
				const customName = customField.getAttribute(this.data.DATA_ATTRIBUTES.fieldName);
				const customData = JSON.parse(item.options[item.options.selectedIndex].getAttribute(this.data.DATA_ATTRIBUTES.selectCustomProperties));

				switch (customType) {
					case 'phone':
						this.setState([this.ELEMENTS, customName, this.VALUECOUNTRY, 'code'], customData[this.data.DATA_ATTRIBUTES.selectCountryCode], formId);
						this.setState([this.ELEMENTS, customName, this.VALUECOUNTRY, 'label'], customData[this.data.DATA_ATTRIBUTES.selectCountryLabel], formId);
						this.setState([this.ELEMENTS, customName, this.VALUECOUNTRY, 'number'], customData[this.data.DATA_ATTRIBUTES.selectCountryNumber], formId);
						this.setState([this.ELEMENTS, customName, this.VALUE], value, formId);
						break;
					case 'country':
						this.setState([this.ELEMENTS, customName, this.VALUECOUNTRY, 'code'], customData[this.data.DATA_ATTRIBUTES.selectCountryCode], formId);
						this.setState([this.ELEMENTS, customName, this.VALUECOUNTRY, 'label'], customData[this.data.DATA_ATTRIBUTES.selectCountryLabel], formId);
						this.setState([this.ELEMENTS, customName, this.VALUECOUNTRY, 'number'], customData[this.data.DATA_ATTRIBUTES.selectCountryNumber], formId);
						this.setState([this.ELEMENTS, name, this.VALUE], value, formId);
						break;
					default:
						this.setState([this.ELEMENTS, name, this.VALUE], value, formId);
						break;
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

	getStateForm(formId) {
		return this.getState([this.FORM], formId);
	}

	getStateFormType(formId) {
		return this.getState([this.FORM, this.FORMTYPE], formId);
	}

	getStateFormPhoneDisablePicker(formId) {
		return this.getState([this.FORM, this.CONFIG_PHONE_DISABLE_PICKER], formId);
	}

	getStateFormPhoneUseSync(formId) {
		return this.getState([this.FORM, this.CONFIG_PHONE_USE_PHONE_SYNC], formId);
	}

	getStateFormElement(formId) {
		return this.getState([this.FORM, this.FORMELEMENT], formId);
	}

	getStateElements(formId) {
		return this.getState([this.ELEMENTS], formId);
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

	getStateElementInput(name, formId) {
		return this.getState([this.ELEMENTS, name, this.INPUT], formId);
	}

	getStateElementChoice(name, formId) {
		return this.getState([this.ELEMENTS, name, this.CHOICE], formId);
	}

	getStateElementType(name, formId) {
		return this.getState([this.ELEMENTS, name, this.TYPE], formId);
	}

	getStateElementName(name, formId) {
		return this.getState([this.ELEMENTS, name, this.NAME], formId);
	}

	getStateElementItems(name, formId) {
		return this.getState([this.ELEMENTS, name, this.ITEMS], formId);
	}

	getStateElementValueCountry(name, formId) {
		return this.getState([this.ELEMENTS, name, this.VALUECOUNTRY], formId);
	}
	getStateElementItems(name, formId) {
		return this.getState([this.ELEMENTS, name, this.ITEMS], formId);
	}

	getStateElementItem(name, value, formId) {
		return this.getState([this.ELEMENTS, name, this.ITEMS, value], formId);
	}

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
