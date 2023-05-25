import { debounce } from '@eightshift/frontend-libs/scripts/helpers';
import { Utils } from './../../form/assets/utilities';
import { State } from '../../form/assets/state';
import { Data } from '../../form/assets/data';

/**
 * Main conditon tags class.
 */
export class ConditionalTags {
	constructor() {
		this.data = new Data();
		this.state = new State();
		this.utils = new Utils();

		// Simplify usage of constants
		this.SHOW = this.data.CONDITIONAL_TAGS_ACTIONS.SHOW;
		this.HIDE = this.data.CONDITIONAL_TAGS_ACTIONS.HIDE;
		this.OR = this.data.CONDITIONAL_TAGS_LOGIC.OR;
		this.AND = this.data.CONDITIONAL_TAGS_LOGIC.AND;

		// Define this forms ID.
		this.FORM_ID = '';

		// Map all conditional logic as a object.
		this.CONDITIONAL_TAGS_OPERATORS = {
			[this.data.CONDITIONAL_TAGS_OPERATORS.IS]: (input, value) => value === input,
			[this.data.CONDITIONAL_TAGS_OPERATORS.ISN]: (input, value) => value !== input,
			[this.data.CONDITIONAL_TAGS_OPERATORS.GT]: (input, value) => parseFloat(String(input)) > parseFloat(String(value)),
			[this.data.CONDITIONAL_TAGS_OPERATORS.GTE]: (input, value) => parseFloat(String(input)) >= parseFloat(String(value)),
			[this.data.CONDITIONAL_TAGS_OPERATORS.LT]: (input, value) => parseFloat(String(input)) < parseFloat(String(value)),
			[this.data.CONDITIONAL_TAGS_OPERATORS.LTE]: (input, value) => parseFloat(String(input)) <= parseFloat(String(value)),
			[this.data.CONDITIONAL_TAGS_OPERATORS.C]: (input, value) => input.includes(value),
			[this.data.CONDITIONAL_TAGS_OPERATORS.SW]: (input, value) => input.startsWith(value),
			[this.data.CONDITIONAL_TAGS_OPERATORS.EW]: (input, value) => input.endsWith(value),
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

		// Init all forms.
		this.initOnlyForms();
	}

	/**
	 * Init all forms.
	 * 
	 * @public
	 */
	initOnlyForms() {
		const elements = document.querySelectorAll(this.data.formSelector);

		// Loop all forms on the page.
		[...elements].forEach((element) => {
			// this.initOne(element);
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
		this.FORM_ID = element.getAttribute(this.data.DATA_ATTRIBUTES.formPostId);

		console.log(this.state.getState([this.data.STATE_NAMES.ISLOADED], this.FORM_ID));

		const interval = setInterval(() => {
			// Wait until everything is fully loaded.
			if (this.state.getState([this.data.STATE_NAMES.ISLOADED], this.FORM_ID)) {
				clearInterval(interval);

				// Set forms logic.
				this.initForms(element);

				// Set fields logic.
				this.initFields(element);
			}
		}, 100);
	}

	/**
	 * Init forms conditional logic.
	 *
	 * @param {object} element Form element.
	 *
	 * @returns void
	 */
	initForms(element) {
		let tags = element.getAttribute(this.data.DATA_ATTRIBUTES.conditionalTags);

		if (!tags) {
			return;
		}

		JSON.parse(tags).forEach((tag) => {
			const item = element.querySelector(`${this.data.fieldSelector}[data-field-name='${tag[0]}']`);

			if (!item) {
				return;
			}

			const type = item.getAttribute(this.data.DATA_ATTRIBUTES.fieldType);
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
					innerItem.classList.add(this.data.SELECTORS.CLASS_HIDDEN);
				} else {
					innerItem.classList.add(this.data.SELECTORS.CLASS_VISIBLE);
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
	initFields(element) {
		// Set internal preset data.
		this.setInternalData(element);

		// Loop all fields and set data.
		this.setFields(Object.keys(this.state.getState([this.state.FIELDS], this.FORM_ID)));

		// Set event listeners.
		this.setListeners(element);
	}

	/**
	 * Init fields internal data logic.
	 *
	 * @param {object} element Form element.
	 *
	 * @returns void
	 */
	setInternalData(element) {
		// Find all fields and all conditional logic data.
		const fields = element.querySelectorAll(`[${this.data.DATA_ATTRIBUTES.conditionalTags}]`);

		// Loop all items with conditional tags.
		[...fields].forEach((field) => {
			// Get field name and tags.
			let name = field.getAttribute(this.data.DATA_ATTRIBUTES.fieldName);
			const tags = field.getAttribute(this.data.DATA_ATTRIBUTES.conditionalTags);
			const type = field.getAttribute(this.data.DATA_ATTRIBUTES.fieldType);

			switch (type) {
				case 'checkbox':
				case 'radio':
				case 'option':
					name = `${field.closest(`${this.data.fieldSelector}`).getAttribute(this.data.DATA_ATTRIBUTES.fieldName)}---${name}`;
					break;
			}

			// Bailout if missing data.
			if (tags && name) {
				// Set default object.
				this.state.setState([this.state.DEFAULTS, name], this.HIDE, this.FORM_ID);
				this.state.setState([this.state.FIELDS, name], [], this.FORM_ID);
				this.state.setState([this.state.REFERENCE, name], [], this.FORM_ID);

				// Decode json data.
				const tag = JSON.parse(tags);

				const dataItem = tag?.[0];

				// Bailout if there is no logic.
				if (dataItem.length > 0) {
					this.state.setState([this.state.DEFAULTS, name], dataItem[0], this.FORM_ID);
					this.state.setState([this.state.FIELDS, name], dataItem[1], this.FORM_ID);

					// Loop fields.
					dataItem[1].forEach((item) => {
						// Create initial state for logic for or/and.
						this.state.setStateArray([this.state.REFERENCE, name], Array(item.length).fill(false), this.FORM_ID);

						// Loop inner fields.
						item.forEach((inner) => {

							console.log(inner);
							this.state.setStateArray([this.state.EVENTS, inner[0]], name, this.FORM_ID);
						});
					});
				}
			}
		});

		const selects = this.state.getState([this.state.SELECTS], this.FORM_ID);

		if (selects) {
			selects.forEach((select) => {
				select.config.choices.forEach((choice) => {
					choice.esFormsIsHidden = false;
				});
			});
		}

		// Set initial values of fields.
		this.setValues(element, true);
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
			(defaults !== this.HIDE) ? fieldElement.classList.remove(this.data.SELECTORS.CLASS_HIDDEN) : fieldElement.classList.add(this.data.SELECTORS.CLASS_HIDDEN);

			if (isValid) {
				// Change state.
				(defaults !== this.HIDE) ? fieldElement.classList.add(this.data.SELECTORS.CLASS_HIDDEN) : fieldElement.classList.remove(this.data.SELECTORS.CLASS_HIDDEN);
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
	setListeners(element) {
		// Find all fields that need listeners.
		const items = element.querySelectorAll(`input, textarea, select`);

		if (items) {
			items.forEach((item) => {
				if (item.localName === 'select') {
					item.addEventListener('change', this.onChangeEvent);
				} else {
					item.addEventListener('input', debounce(this.onFieldChangeEvent, 250));
				}
			});
		}
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
	setFieldsRules(field) {
		// Loop all fields.
		this.state.getState([this.state.FIELDS, field], this.FORM_ID).forEach((items, parent) => {
			// Loop all inner fields.
			items.forEach((inner, index) => {
				// Do the check based on the operator and set reference data with the correct state.
				console.log(inner[0], inner[1], inner[2], this.state.getState([this.state.VALUES, inner[0]], this.FORM_ID));
				this.state.setState([this.state.REFERENCE, field, parent, index], this.CONDITIONAL_TAGS_OPERATORS[inner[1]](this.state.getState([this.state.VALUES, inner[0]], this.FORM_ID), inner[2]), this.FORM_ID);
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
		if (typeof window?.[this.data.prefix]?.conditionalTags === 'undefined') {
			window[this.data.prefix].conditionalTags = {
				FORM_ID: this.FORM_ID,
				CONDITIONAL_TAGS_OPERATORS: this.CONDITIONAL_TAGS_OPERATORS,
				init: () => {
					this.init();
				},
				initOnlyForms: () => {
					this.initOnlyForms();
				},
				initOne: (element) => {
					this.initOne(element);
				},
				initForms: (element) => {
					this.initForms(element);
				},
				initFields: (element) => {
					this.initFields(element);
				},
				setValues: (element) => {
					this.setValues(element);
				},
				setInternalData: (element) => {
					this.setInternalData(element);
				},
				setInnerData: (element) => {
					this.setInnerData(element);
				},
				setListeners: () => {
					this.setListeners();
				},
				setInitFields: (element, item) => {
					this.setInitFields(element, item);
				},
				setFields: (element, item) => {
					this.setFields(element, item);
				},
				onChangeEvent: (event) => {
					this.onChangeEvent(event);
				},
				onFieldChangeEvent: (event) => {
					this.onFieldChangeEvent(event);
				},
			};
		}
	}
}
