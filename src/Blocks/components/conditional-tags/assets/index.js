import { debounce } from '@eightshift/frontend-libs/scripts/helpers';
import { Utils } from './../../form/assets/utilities';
import { State, prefix } from '../../form/assets/state';
import {
	CONDITIONAL_TAGS_OPERATORS,
	CONDITIONAL_TAGS_ACTIONS,
	CONDITIONAL_TAGS_LOGIC,
} from './utils';


/**
 * Main conditon tags class.
 */
export class ConditionalTags {
	constructor(options = {}) {
		this.state = new State(options);
		this.utils = new Utils(options);

		// Simplify usage of constants
		this.SHOW = CONDITIONAL_TAGS_ACTIONS.SHOW;
		this.HIDE = CONDITIONAL_TAGS_ACTIONS.HIDE;
		this.OR = CONDITIONAL_TAGS_LOGIC.OR;
		this.AND = CONDITIONAL_TAGS_LOGIC.AND;

		// Map all conditional logic as a object.
		this.OPERATORS = {
			[CONDITIONAL_TAGS_OPERATORS.IS]: (input, value) => value === input,
			[CONDITIONAL_TAGS_OPERATORS.ISN]: (input, value) => value !== input,
			[CONDITIONAL_TAGS_OPERATORS.GT]: (input, value) => parseFloat(String(input)) > parseFloat(String(value)),
			[CONDITIONAL_TAGS_OPERATORS.GTE]: (input, value) => parseFloat(String(input)) >= parseFloat(String(value)),
			[CONDITIONAL_TAGS_OPERATORS.LT]: (input, value) => parseFloat(String(input)) < parseFloat(String(value)),
			[CONDITIONAL_TAGS_OPERATORS.LTE]: (input, value) => parseFloat(String(input)) <= parseFloat(String(value)),
			[CONDITIONAL_TAGS_OPERATORS.C]: (input, value) => input.includes(value),
			[CONDITIONAL_TAGS_OPERATORS.SW]: (input, value) => input.startsWith(value),
			[CONDITIONAL_TAGS_OPERATORS.EW]: (input, value) => input.endsWith(value),
		};
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
	}

	/**
	 * Init one form by element.
	 * 
	 * @param {object} element Form element.
	 *
	 * @public
	 */
	initOne(formId) {
		// Set forms logic.
		// this.initForms(formId);

		// Set fields logic.
		this.initFields(formId);
	}

	/**
	 * Init forms conditional logic.
	 *
	 * @param {object} element Form element.
	 *
	 * @returns void
	 */
	initForms(formId) {
		let tags = this.state.getStateFormConfigConditionalTags(formId);

		if (!tags) {
			return;
		}

		JSON.parse(tags).forEach((tag) => {
			const item = element.querySelector(`${this.data.fieldSelector}[data-field-name='${tag[0]}']`);

			if (!item) {
				return;
			}

			const type = item.getAttribute(this.state.getStateAttribute('fieldType'));
			let innerItem = '';

			if (tag[2]) {
				if (type === 'select') {
					// select.
					innerItem = item.querySelector(`.choices__item--choice[data-value="${tag[2]}"]`);
				} else {
					// checkbox/radio.
					innerItem = item.querySelector(`[value="${tag[2]}"]`).parentNode.parentNode;
				}
			} else {
				// input/textarea.
				innerItem = item;
			}

			if (innerItem) {
				if (tag[1] === this.data.CONDITIONAL_TAGS_ACTIONS.HIDE) {
					innerItem.classList.add(this.state.getStateSelectorsClassHidden());
				} else {
					innerItem.classList.add(this.state.getStateSelectorsClassVisible());
				}
			}
		});
	}

	/**
	 * Init fields conditional logic.
	 *
	 * @param {object} element Form element.
	 *
	 * @returns void
	 */
	initFields(formId) {
		// Set internal preset data.
		// this.setInternalData(formId);

		// Loop all fields and set data.
		// this.setFields(Object.keys(this.state.getState([this.state.FIELDS], this.FORM_ID)));

		// // Set event listeners.
		// this.setListeners(formId);
	}

	setField(name, formId) {
		const value = this.state.getStateElementValue(name, formId);
		const events = this.state.getStateConditionalTagsEvents(formId);

		events?.[name]?.forEach((eventName) => {
			this.setFieldsRules(eventName, value, formId);
		});
	}

	/**
	 * Set master logic for conditional tags.
	 *
	 * @param {string} name Field name.
	 *
	 * @returns void
	 */
	setFields(fields) {
		fields.forEach((field) => {
			// Set reference data based on the condtions.
			this.setFieldsRules(field);

			// Find defaults to know what direction to use.
			const defaults = this.state.getState([this.state.DEFAULTS, field], this.FORM_ID);

			// Check if conditions are valid or not. This is where the magic happens.
			const isValid = this.state.getState([this.state.REFERENCE, field], this.FORM_ID).map((validItem) => validItem.every(Boolean)).some(Boolean);

			// Find field or inner item to toggle.
			const fieldDetails = field.split('---');
			let fieldElement = this.state.getState([this.state.ELEMENTS, field], this.FORM_ID);

			if (fieldDetails.length > 1 && typeof fieldElement === 'string' && fieldElement === 'select') {

				const getSelect = this.state.getState([this.state.SELECTS, fieldDetails[0]], this.FORM_ID);

				fieldElement = Array.from(getSelect.choiceList.element.childNodes).find((item) => item.getAttribute('data-value') === fieldDetails[1]);
				const constChoiceItem = getSelect.config.choices.find((item) => item.value === fieldDetails[1]);

				constChoiceItem.esFormsIsHidden = false;
				if (isValid) {
					constChoiceItem.esFormsIsHidden = true;
				}
			}

			if (!fieldElement) {
				return;
			}

			// Reset to original state.
			(defaults !== this.HIDE) ? fieldElement.classList.remove(this.state.getStateSelectorsClassHidden()) : fieldElement.classList.add(this.state.getStateSelectorsClassHidden());

			if (isValid) {
				// Change state.
				(defaults !== this.HIDE) ? fieldElement.classList.add(this.state.getStateSelectorsClassHidden()) : fieldElement.classList.remove(this.state.getStateSelectorsClassHidden());
			}
		});
	}

	/**
	 * Init event listeners.
	 *
	 * @param {object} element Form element.
	 *
	 * @returns void
	 */
	setListeners(formId) {
		// Find all fields that need listeners.
		Object.keys(this.state.getStateConditionalTagsEvents(formId)).forEach((item) => {
			console.log(item);
			// if (item.localName === 'select') {
			// 	item.addEventListener('change', this.onChangeEvent);
			// } else {
				item.addEventListener('input', debounce(this.onFieldChangeEvent, 250));
			// }
		});
	}

	/**
	 * Init fields initial values data.
	 *
	 * @param {object} element Form element.
	 * @param {bool} isInit If this method is used in initial state or on every change.
	 *
	 * @returns void
	 */
	setValues(element, isInit=false) {
		// Find all fields.
		let items = element.querySelectorAll('input, select, textarea');

		// Loop all fields.
		for (const [key, item] of Object.entries(items)) {
			const itemValue = item.value;
			const itemName = item.name;
			const itemType = item.type;

			if (itemName === 'search_terms') {
				continue;
			}

			// Make changes depending on the field type.
			switch (itemType) {
				case 'radio':
					if (item.checked) {
						this.state.setState([this.state.VALUES, itemName], itemValue, this.FORM_ID);
					}

					if (isInit) {
						this.state.setState([this.state.VALUES, itemName], '', this.FORM_ID);

						this.state.setState([this.state.ELEMENTS, itemName], this.utils.getFieldByName(element, itemName), this.FORM_ID);
						this.state.setState([this.state.ELEMENTS, `${itemName}---${itemValue}`], item.parentNode.parentNode, this.FORM_ID);
					}
					break;
				case 'checkbox':
					this.state.setState([this.state.VALUES, `${itemName}---${itemValue}`], item.checked ? itemValue : '', this.FORM_ID);

					if (isInit) {
						this.state.setState([this.state.ELEMENTS, `${itemName}---${itemValue}`], item.parentNode.parentNode, this.FORM_ID);
					}
					break;
				case 'select-one':
					// Set values based on the input name depeneding on the value.
					this.state.setState([this.state.VALUES, itemName], itemValue, this.FORM_ID);

					if (isInit) {
						this.state.setState([this.state.ELEMENTS, itemName], this.utils.getFieldByName(element, itemName), this.FORM_ID);

						item.parentNode.parentNode.querySelectorAll('.choices__list--dropdown .choices__item').forEach((option) => {
							const optionValue = option.getAttribute('data-value');
							if (optionValue) {
								this.state.setState([this.state.ELEMENTS, `${itemName}---${option.getAttribute('data-value')}`], 'select', this.FORM_ID);
							}
						})
					}
					break;
				default:
					// Set values based on the input name depeneding on the value.
					this.state.setState([this.state.VALUES, itemName], itemValue, this.FORM_ID);

					if (isInit) {
						this.state.setState([this.state.ELEMENTS, itemName], this.utils.getFieldByName(element, itemName), this.FORM_ID);
					}
					break;
			}
		}
	}

	/**
	 * Do the actual logic of checking conditions for rule.
	 *
	 * @param {string} name Field name.
	 *
	 * @returns void
	 */
	setFieldsRules(name, value, formId) {
		// Loop all fields.
		const output = [
			...this.state.getStateElementConditionalTagsRef(name, formId),
		];

		this.state.getStateElementConditionalTagsTags(name, formId).forEach((items, parent) => {
			// Loop all inner fields.
			items.forEach((inner, index) => {
				// Do the check based on the operator and set reference data with the correct state.
				const condition = this.OPERATORS[inner[1]](value, inner[2]);

				if (condition) {
					
				}
				output[parent] = [
					...output[parent],
					
					[index]: this.OPERATORS[inner[1]](value, inner[2]),
				];

				console.log(output);

				// this.state.setState(
				// 	[this.state.ELEMENTS, name, this.state.CONDITIONAL_TAGS, this.state.TAGS_REF],
				// 	output,
				// 	formId
				// );
			});
		});
	}

	////////////////////////////////////////////////////////////////
	// Events callback
	////////////////////////////////////////////////////////////////

	/**
	 * Handle custom select event change.
	 *
	 * @param {object} event Event callback.
	 *
	 * @public
	 */
	onChangeEvent = (event) => {
		this.onFieldChangeEvent(event);
	};

	/**
	 * Handle action on input change.
	 *
	 * @param {object} event Event callback.
	 *
	 * @public
	 */
	onFieldChangeEvent = (event) => {
		const inputElement = event.target;

		let {name} = inputElement;

		if (inputElement.type === 'checkbox') {
			name = `${inputElement.name}---${inputElement.value}`;
		}

		this.setValues(inputElement.closest(this.utils.formSelector));

		const fields = this.state.getState([this.state.EVENTS, name], this.FORM_ID);

		if (fields) {
			this.setFields(fields);
		}
	}

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

		window[prefix].conditionalTags = {}
	}
}
