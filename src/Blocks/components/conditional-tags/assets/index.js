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
				checkboxes: {},
			},
		};

		// Set internal preset data.
		this.setInternalData(element);

		// Set field values preset data.
		this.setInitValues(element);
		this.setValues(element);

		// Loop all fields and set data.
		for (const [name, rules] of Object.entries(this.INTERNAL_DATA[this.FORM_ID].fields)) {
			// Hide initial fields that are set to bi hidden.
			this.setInitFields(element, name);

			console.log(name, rules);

			// Check initial state of fields and set conditional logic. This applies if you set values to fields.
			this.setFields(element, name);
		}

		// Set event listeners.
		this.setListeners(element);
	}

	/**
	 * Init fields initial values data.
	 *
	 * @param {object} element Form element.
	 *
	 * @returns void
	 */
	setInitValues(element) {
		// Find all fields.
		let items = element.querySelectorAll('input, select, textarea');

		// Loop all fields.
		for (const [key, item] of Object.entries(items)) {
			const itemValue = item.value;
			const itemName = item.name;
			const itemId = item.id;

			// Skip search field of the select.
			if (itemName === 'search_terms') {
				continue;
			}

			switch (item.type) {
				case 'checkbox':
					this.INTERNAL_DATA[this.FORM_ID].values = {
						...this.INTERNAL_DATA[this.FORM_ID].values,
						[itemId]: '',
					}

					// Set checkboxes array
					this.INTERNAL_DATA[this.FORM_ID].checkboxes = {
						...this.INTERNAL_DATA[this.FORM_ID].checkboxes,
						[itemName]: [
							...this.INTERNAL_DATA[this.FORM_ID].checkboxes[itemName] ?? [],
							itemId,
						],
					}
					break;
				case 'radio':
					this.INTERNAL_DATA[this.FORM_ID].values = {
						...this.INTERNAL_DATA[this.FORM_ID].values,
						[itemName]: '',
					}
					break;
				default:
					this.INTERNAL_DATA[this.FORM_ID].values = {
						...this.INTERNAL_DATA[this.FORM_ID].values,
						[itemName]: itemValue,
					}
					break;
			}
		}
	}

	/**
	 * Init fields initial values data.
	 *
	 * @param {object} element Form element.
	 *
	 * @returns void
	 */
	setValues(element) {
		// Find all fields.
		let items = element.querySelectorAll('input, select, textarea');

		// Loop all fields.
		for (const [key, item] of Object.entries(items)) {
			const itemValue = item.value;
			const itemName = item.name;
			const itemId = item.id;
			const itemChecked = item.checked;

			// Skip search field of the select.
			if (itemName === 'search_terms') {
				continue;
			}

			switch (item.type) {
				case 'radio':
					if (itemChecked) {
						this.INTERNAL_DATA[this.FORM_ID].values = {
							...this.INTERNAL_DATA[this.FORM_ID].values,
							[itemName]: itemValue,
						}
					}
					break;
				case 'checkbox':
					this.INTERNAL_DATA[this.FORM_ID].values = {
						...this.INTERNAL_DATA[this.FORM_ID].values,
						[itemId]: itemChecked ? itemValue : '',
					}
					break;
				default:
					this.INTERNAL_DATA[this.FORM_ID].values = {
						...this.INTERNAL_DATA[this.FORM_ID].values,
						[itemName]: itemValue,
					}
					break;
			}
		}
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

		// Loop all items.
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
					this.setInnerData(dataItem[1], name);
				}
			}
		});
	}

	/**
	 * Set inner data and events for show/hide options depending on the or/and operators.
	 *
	 * @param {object} items Items of field.
	 * @param {string} name Field name.
	 */
	setInnerData(items, name) {
		const output = [];

		// Loop fields.
		items.forEach((item, parent) => {
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
	 * @param {string} item Field name.
	 *
	 * @returns void
	 */
	setInitFields(element, item) {
		const values = this.INTERNAL_DATA[this.FORM_ID].reference[item];
		const defaults = this.INTERNAL_DATA[this.FORM_ID].defaults[item];

		// Find if field is hidden by default and add class.
		if (defaults === this.HIDE && values.length > 0) {
			const fieldElement = this.utils.getFieldByName(element, item);

			fieldElement.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
		}
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
			items.forEach((inner, index) => {
				const name = inner[0];
				const operator = inner[1];
				const value = inner[2];

				let condition = false;

				// Checkboxes are different.
				if (name in this.INTERNAL_DATA[this.FORM_ID].checkboxes) {
					// Set internal state.
					let internalCheckboxesState = [];

					// Loop all checkboxes with this name and push to internal state if the condition is true.
					this.INTERNAL_DATA[this.FORM_ID].checkboxes[name].forEach((checkbox) => {
						internalCheckboxesState.push(this.CONDITIONAL_TAGS_OPERATORS[operator](this.INTERNAL_DATA[this.FORM_ID].values[checkbox], value));
						return;
					});

					// If checkboxes state contains true the rule is valid.
					condition = internalCheckboxesState.includes(true);
				} else {
					// Do the normal operation for all other field types.
					condition = this.CONDITIONAL_TAGS_OPERATORS[operator](this.INTERNAL_DATA[this.FORM_ID].values[name], value);
				}

				this.INTERNAL_DATA[this.FORM_ID].reference[item][parent][index] = condition;
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

		this.setFieldsRules(name);

		const values = this.INTERNAL_DATA[this.FORM_ID].reference[name];
		const defaults = this.INTERNAL_DATA[this.FORM_ID].defaults[name];

		// Check if conditions are valid or not. This is where the magic happens.
		const ruleIndex = values.map((validItem) => validItem.every(Boolean));
		const isValid = ruleIndex.some(Boolean);
		const validIndex = ruleIndex.indexOf(true);
		let innerItem = '';

		console.log(ruleIndex, isValid);

		if (validIndex !== -1) {
			innerItem = this.INTERNAL_DATA[this.FORM_ID].fields?.[name]?.[validIndex]?.[0]?.[3] ?? [];
		}

		const fieldElement = this.utils.getFieldByName(element, name);

		this.resetFieldConditions(defaults, fieldElement);

		if (isValid) {
			this.setFieldConditions(defaults, fieldElement, innerItem, name);
		}

		console.log(this.INTERNAL_DATA[this.FORM_ID]);
	}

	setFieldConditions(type, fieldElement, innerItem, name) {
		let items = [];

		switch (fieldElement.getAttribute(this.utils.DATA_ATTRIBUTES.fieldType)) {
			case 'radios':
			case 'checkboxes':
				if (innerItem) {
					innerItem.forEach((item) => {
						items = fieldElement.querySelectorAll(`input[name=${name}][value="${item}"]`)?.parentNode?.parentNode;
					})
				}
				break;
		}

		console.log(innerItem);

		if (innerItem === -1) {
			if (type !== this.HIDE) {
				fieldElement.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
			} else {
				fieldElement.classList.remove(this.utils.SELECTORS.CLASS_HIDDEN);
			}
		}

		if (items) {
			items.forEach((item) => {
				if (type !== this.HIDE) {
					item.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
				} else {
					item.classList.remove(this.utils.SELECTORS.CLASS_HIDDEN);
				}
			});
		}
	}

	resetFieldConditions(type, fieldElement) {
		let items = [];

		switch (fieldElement.getAttribute(this.utils.DATA_ATTRIBUTES.fieldType)) {
			case 'radios':
			case 'checkboxes':
				items = fieldElement.querySelectorAll('input');

				if (items) {
					items.forEach((item) => {
						if (type !== this.HIDE) {
							item?.parentNode?.parentNode.classList.remove(this.utils.SELECTORS.CLASS_HIDDEN);
						} else {
							item?.parentNode?.parentNode.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
						}
					})
				}
				break;
		}


		if (type !== this.HIDE) {
			fieldElement.classList.remove(this.utils.SELECTORS.CLASS_HIDDEN);
		} else {
			fieldElement.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
		}
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
