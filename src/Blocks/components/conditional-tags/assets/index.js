import { debounce } from '@eightshift/frontend-libs/scripts/helpers';
import { Utils } from './../../form/assets/utilities';

/**
 * Main conditon tags class.
 */
export class ConditionalTags {
	constructor(options) {
		/** @type Utils */
		this.utils = options.utils ?? new Utils();

		// Simplify usage of constants
		this.SHOW = this.utils.CONDITIONAL_TAGS_ACTIONS.SHOW;
		this.HIDE = this.utils.CONDITIONAL_TAGS_ACTIONS.HIDE;
		this.OR = this.utils.CONDITIONAL_TAGS_LOGIC.OR;
		this.AND = this.utils.CONDITIONAL_TAGS_LOGIC.AND;

		// Define this forms ID.
		this.FORM_ID = '';

		// Internal Data.
		this.INTERNAL_DATA = {};

		// Map all conditional logic as a object.
		this.CONDITIONAL_TAGS_OPERATORS = {
			[this.utils.CONDITIONAL_TAGS_OPERATORS.IS]: (input, value) => value === input,
			[this.utils.CONDITIONAL_TAGS_OPERATORS.ISN]: (input, value) => value !== input,
			[this.utils.CONDITIONAL_TAGS_OPERATORS.GT]: (input, value) => parseFloat(String(input)) > parseFloat(String(value)),
			[this.utils.CONDITIONAL_TAGS_OPERATORS.GTE]: (input, value) => parseFloat(String(input)) >= parseFloat(String(value)),
			[this.utils.CONDITIONAL_TAGS_OPERATORS.LT]: (input, value) => parseFloat(String(input)) < parseFloat(String(value)),
			[this.utils.CONDITIONAL_TAGS_OPERATORS.LTE]: (input, value) => parseFloat(String(input)) <= parseFloat(String(value)),
			[this.utils.CONDITIONAL_TAGS_OPERATORS.C]: (input, value) => input.includes(value),
			[this.utils.CONDITIONAL_TAGS_OPERATORS.SW]: (input, value) => input.startsWith(value),
			[this.utils.CONDITIONAL_TAGS_OPERATORS.EW]: (input, value) => input.endsWith(value),
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
		this.FORM_ID = element.getAttribute(this.utils.DATA_ATTRIBUTES.formPostId);

		const interval = setInterval(() => {
			// Wait until everything is fully loaded.
			if (this.utils.getFormStateByKey('isLoaded', this.FORM_ID)) {
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
		let tags = element.getAttribute(this.utils.DATA_ATTRIBUTES.conditionalTags);

		if (!tags) {
			return;
		}

		JSON.parse(tags).forEach((tag) => {
			const item = element.querySelector(`${this.utils.fieldSelector}[data-field-name='${tag[0]}']`);

			if (!item) {
				return;
			}

			const type = item.getAttribute(this.utils.DATA_ATTRIBUTES.fieldType);
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
				if (tag[1] === this.utils.CONDITIONAL_TAGS_ACTIONS.HIDE) {
					innerItem.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
				} else {
					innerItem.classList.add(this.utils.SELECTORS.CLASS_VISIBLE);
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
		// Preset store object.
		this.INTERNAL_DATA = {
			[this.FORM_ID]: {
				fields: {},
				values: {},
				defaults: {},
				events: [],
				reference: {},
				customTypes: {},
				types: {},
			},
		};

		// Set internal preset data.
		this.setInternalData(element);

		// Loop all fields and set data.
		for (const [name] of Object.entries(this.INTERNAL_DATA[this.FORM_ID].fields)) {
			// Hide initial fields that are set to hidden.
			this.setInitFields(element, name);
		}

		// Set event listeners.
		this.setListeners(element);

		console.log(this.INTERNAL_DATA[this.FORM_ID]);
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
		const fields = element.querySelectorAll(`[${this.utils.DATA_ATTRIBUTES.conditionalTags}]`);

		// Loop all items with conditional tags.
		[...fields].forEach((field) => {
			// Get field name and tags.
			const name = field.getAttribute(this.utils.DATA_ATTRIBUTES.fieldName);
			const tags = field.getAttribute(this.utils.DATA_ATTRIBUTES.conditionalTags);

			// Bailout if missing data.
			if (tags && name) {
				// Set default object.
				this.INTERNAL_DATA[this.FORM_ID] = {
					...this.INTERNAL_DATA[this.FORM_ID],
					defaults: {
						...this.INTERNAL_DATA[this.FORM_ID].defaults,
						[name]: this.HIDE,
					},
					fields: {
						...this.INTERNAL_DATA[this.FORM_ID].fields,
						[name]: [],
					},
					reference: {
						...this.INTERNAL_DATA[this.FORM_ID].reference,
						[name]: [],
					},
				}

				// Decode json data.
				const tag = JSON.parse(tags);

				const dataItem = tag?.[0];

				// Bailout if there is no logic.
				if (dataItem.length > 0) {
					this.INTERNAL_DATA[this.FORM_ID].defaults[name] = dataItem[0];
					this.INTERNAL_DATA[this.FORM_ID].fields[name] = dataItem[1];

					// Set init data for one field.
					const output = [];

					// Loop fields.
					dataItem[1].forEach((item, parent) => {
						// Create initial state for logic for or/and.
						output[parent] = Array(item.length).fill(false);

						// Loop inner fields.
						item.forEach((inner) => {
							this.INTERNAL_DATA[this.FORM_ID].events[inner[0]] = [
								...this.INTERNAL_DATA[this.FORM_ID].events[inner[0]] ?? [],
								name,
							];
						});
					});

					this.INTERNAL_DATA[this.FORM_ID].reference[name] = output;
				}
			}
		});

		// Set initial values of fields.
		this.setValues(element, true);
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

			// Make changes depending on the field type.
			switch (itemType) {
				case 'radio':
				case 'checkbox':
					// Set values based on the input value not the name depeneding on the check state.
					this.INTERNAL_DATA[this.FORM_ID].values = {
						...this.INTERNAL_DATA[this.FORM_ID].values,
						[itemValue]: item.checked ? itemValue : '',
					}

					// Do this only on init once.
					if (isInit) {
						// Add types data.
						this.INTERNAL_DATA[this.FORM_ID].types = {
							...this.INTERNAL_DATA[this.FORM_ID].types,
							[itemValue]: itemType,
						}

						// Add custom types data.
						this.INTERNAL_DATA[this.FORM_ID].customTypes = {
							...this.INTERNAL_DATA[this.FORM_ID].customTypes,
							[itemName]: [
								...this.INTERNAL_DATA[this.FORM_ID].customTypes[itemName] ?? [],
								itemValue,
							],
						}
					}
					break;
				default:
					// Set values based on the input name depeneding on the value.
					this.INTERNAL_DATA[this.FORM_ID].values = {
						...this.INTERNAL_DATA[this.FORM_ID].values,
						[itemName]: itemValue,
					}

					// Do this only on init once.
					if (isInit) {
						// Add types data.
						this.INTERNAL_DATA[this.FORM_ID].types = {
							...this.INTERNAL_DATA[this.FORM_ID].types,
							[itemName]: itemType,
						}
					}
					break;
			}
		}
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
		for (const [name] of Object.entries(this.INTERNAL_DATA[this.FORM_ID].events)) {
			const input = element.querySelector(`${this.utils.formSelector} [${this.utils.DATA_ATTRIBUTES.fieldName}="${name}"]`);

			// Bailout if non existing.
			if (!input) {
				return;
			}

			// Change logic for select.
			if (input.localName === 'select') {
				input.addEventListener('change', this.onChangeEvent);
			} else {
				input.addEventListener('input', debounce(this.onFieldChangeEvent, 250));
			}
		}
	}

	/**
	 * Init fields logic to hide fields that are hidden by default.
	 *
	 * @param {object} element Form element.
	 * @param {string} name Field name.
	 *
	 * @returns void
	 */
	setInitFields(element, name) {
		// Find if field is hidden by default and add class.
		if (
			this.INTERNAL_DATA[this.FORM_ID].defaults[name] === this.HIDE &&
			this.INTERNAL_DATA[this.FORM_ID].reference[name].length > 0
		) {
			const fieldElement = this.getItemByName(element, name);

			if (fieldElement) {
				fieldElement.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
			}
		}

		// Check initial state of fields and set conditional logic. This applies if you set values to fields.
		this.setFields(element, name);
	}

	/**
	 * Do the actual logic of checking conditions for rule.
	 *
	 * @param {string} item Field name.
	 *
	 * @returns void
	 */
	setFieldsRules(item) {
		// Loop all fields.
		this.INTERNAL_DATA[this.FORM_ID].fields[item].forEach((items, parent) => {
			// Loop all inner fields.
			items.forEach((inner, index) => {
				const ruleName = inner[0];
				const ruleOperator = inner[1];
				const ruleValue = inner[2];

				// Prepare temp input value.
				let inputValue = '';

				// Find field type from the state.
				let type = this.INTERNAL_DATA[this.FORM_ID].types[ruleValue];

				// If type is undefined this can be a custom field type like radios, checkboxes.
				if (!type) {
					type = this.INTERNAL_DATA[this.FORM_ID].types[this.INTERNAL_DATA[this.FORM_ID].customTypes[ruleName]?.[0]];
				}

				switch (type) {
					case 'radio':
					case 'checkbox':
						// This can be a empty rule value this is a checkboxes or radios empty value selection.
						if (ruleValue === '') {
							// We need to check all checkboxes or radios in this field to see if all are empty.
							const checkIfAllEmpty = this.INTERNAL_DATA[this.FORM_ID].customTypes[ruleName].every((key) => this.INTERNAL_DATA[this.FORM_ID].values[key] === '');

							// If all are empty just provide empty and this will be true.
							if (checkIfAllEmpty) {
								inputValue = '';
							} else {
								// If not all are empty output normal.
								inputValue = this.INTERNAL_DATA[this.FORM_ID].values[ruleValue];
							}
						} else {
							// Find value of the item in the values object based on the value item.
							inputValue = this.INTERNAL_DATA[this.FORM_ID].values[ruleValue];
						}
						break;
					default:
						// Find value of the item in the values object based on the value name.
						inputValue = this.INTERNAL_DATA[this.FORM_ID].values[ruleName];
						break;
				}

				// Do the check based on the operator and set reference data with the correct state.
				this.INTERNAL_DATA[this.FORM_ID].reference[item][parent][index] = this.CONDITIONAL_TAGS_OPERATORS[ruleOperator](inputValue, ruleValue);
			});
		});
	}

	/**
	 * Set master logic for conditional tags.
	 *
	 * @param {object} element Form element.
	 * @param {string} name Field name.
	 *
	 * @returns void
	 */
	setFields(element, name) {
		// Set reference data based on the condtions.
		this.setFieldsRules(name);

		// Find defaults to know what direction to use.
		const defaults = this.INTERNAL_DATA[this.FORM_ID].defaults[name];

		// Check if conditions are valid or not. This is where the magic happens.
		const isValid = this.INTERNAL_DATA[this.FORM_ID].reference[name].map((validItem) => validItem.every(Boolean)).some(Boolean);

		// Find field or inner item to toggle.
		const fieldElement = this.getItemByName(element, name);

		// Reset to original state.
		this.resetFieldConditions(defaults, fieldElement);

		if (isValid) {
			// Change state.
			this.setFieldConditions(defaults, fieldElement);
		}
	}

	/**
	 * Set new field state.
	 *
	 * @param {string} type Type show/hide.
	 * @param {object} fieldElement Field element to toggle.
	 *
	 * @returns void
	 */
	setFieldConditions(type, fieldElement) {
		if (type !== this.HIDE) {
			fieldElement.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
		} else {
			fieldElement.classList.remove(this.utils.SELECTORS.CLASS_HIDDEN);
		}
	}

	/**
	 * Reset field to original state.
	 *
	 * @param {string} type Type show/hide.
	 * @param {object} fieldElement Field element to toggle.
	 *
	 * @returns void
	 */
	resetFieldConditions(type, fieldElement) {
		if (type !== this.HIDE) {
			fieldElement.classList.remove(this.utils.SELECTORS.CLASS_HIDDEN);
		} else {
			fieldElement.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
		}
	}

	/**
	 * Return field element by name.
	 *
	 * @param {object} element Form element.
	 * @param {string} name Name of the field.
	 *
	 * @returns {object} Item element.
	 */
	getItemByName(element, name) {
		return element.querySelector(`[${this.utils.DATA_ATTRIBUTES.fieldName}="${name}"]`);
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
		const formElement = inputElement.closest(this.utils.formSelector);

		this.setValues(formElement);

		this.INTERNAL_DATA[this.FORM_ID].events[inputElement.name].forEach((item) => {
			this.setFields(formElement, item);
		});
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
		if (typeof window?.[this.utils.getPrefix()]?.conditionalTags === 'undefined') {
			window[this.utils.getPrefix()].conditionalTags = {
				FORM_ID: this.FORM_ID,
				INTERNAL_DATA: this.INTERNAL_DATA,
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
