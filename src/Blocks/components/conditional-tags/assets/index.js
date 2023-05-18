import { debounce } from '@eightshift/frontend-libs/scripts/helpers';
import { Utils } from './../../form/assets/utilities';

/**
 * Main conditon tags class.
 */
export class ConditionalTags {
	constructor(options) {
		/** @type Utils */
		this.utils = options.utils ?? new Utils();

		// Internal data constants.
		this.DATA_REFERENCE = 'reference';

		this.SHOW = this.utils.CONDITIONAL_TAGS_ACTIONS.SHOW;
		this.HIDE = this.utils.CONDITIONAL_TAGS_ACTIONS.HIDE;

		this.OR = this.utils.CONDITIONAL_TAGS_LOGIC.OR;
		this.AND = this.utils.CONDITIONAL_TAGS_LOGIC.AND;

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
		this.FORM_ID = element.getAttribute(this.utils.DATA_ATTRIBUTES.formPostId);;

		const interval = setInterval(() => {
			if (this.utils.getFormStateByKey('isLoaded', this.FORM_ID)) {
				clearInterval(interval);

				this.initForms(element);
				this.initFields(element);
			}
		}, 100);
	}

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

	initFields(element) {

		this.INTERNAL_DATA = {
			[this.FORM_ID]: {
				fields: {},
				values: {},
				defaults: {},
				events: [],
				reference: {},
			},
		};

		this.setInternalData(element);
		this.setValues(element);

		console.log(this.INTERNAL_DATA[this.FORM_ID]);

		for (const [item] of Object.entries(this.INTERNAL_DATA[this.FORM_ID].fields)) {
			console.log(item);
			this.setInitFields(element, item);
			this.setFields(element, item);
		}

		this.setListeners(element);
	}

	// Set internal state for input values.
	setValues(element) {
		let items = element.querySelectorAll('input, select, textarea');

		for (const [key, item] of Object.entries(items)) {
			if (item.name === 'search_terms') {
				continue;
			}

			if(item.type === 'checkbox' && !item.checked) {
				item.value = '';
			}

			this.INTERNAL_DATA[this.FORM_ID].values = {
				...this.INTERNAL_DATA[this.FORM_ID].values,
				[item.name]: item.value,
			}
		}
	}

	// Set internal data state.
	setInternalData(element) {
		const fields = element.querySelectorAll(`[${this.utils.DATA_ATTRIBUTES.conditionalTags}]`);

		[...fields].forEach((field) => {
			const name = field.getAttribute(this.utils.DATA_ATTRIBUTES.fieldName);
			const tags = field.getAttribute(this.utils.DATA_ATTRIBUTES.conditionalTags);

			if (tags && name) {
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

				const tag = JSON.parse(tags);

				const dataItem = tag?.[0];

				if (dataItem.length > 0) {
					this.INTERNAL_DATA[this.FORM_ID].defaults[name] = dataItem[0];
					this.INTERNAL_DATA[this.FORM_ID].fields[name] = dataItem[1];

					this.setInnerData(dataItem[1], name);
				}
			}
		});
	}

	// Set inner data and events for show/hide options depending on the or/and operators.
	setInnerData(items, name) {
		const output = [];

		items.forEach((item, parent) => {
			output[parent] = Array(item.length).fill(false);

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
	 * Add event listeners to all items that need it.
	 *
	 * @public
	 */
	setListeners(element) {
		for (const [name] of Object.entries(this.INTERNAL_DATA[this.FORM_ID].events)) {
			const input = element.querySelector(`${this.utils.formSelector} [${this.utils.DATA_ATTRIBUTES.fieldName}="${name}"]`);

			// Bailout if non existing.
			if (!input) {
				return;
			}

			if (input.localName === 'select') {
				input.addEventListener('change', this.onChangeEvent);
			} else {
				input.addEventListener('input', debounce(this.onFieldChangeEvent, 250));
			}
		}
	}

	setInitFields(element, item) {
		const values = this.INTERNAL_DATA[this.FORM_ID].reference[item];
		const defaults = this.INTERNAL_DATA[this.FORM_ID].defaults[item];

		if (defaults === this.HIDE && values.length > 0) {
			const fieldElement = this.utils.getFieldByName(element, item);

			fieldElement.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
		}
	}

	setFields(element, item) {
		this.INTERNAL_DATA[this.FORM_ID].fields[item].forEach((items, parent) => {
			items.forEach((inner, index) => {
				this.INTERNAL_DATA[this.FORM_ID].reference[item][parent][index] = this.CONDITIONAL_TAGS_OPERATORS[inner[1]]( this.INTERNAL_DATA[this.FORM_ID].values[inner[0]], inner[2]);
			});
		});

		const values = this.INTERNAL_DATA[this.FORM_ID].reference[item];
		const defaults = this.INTERNAL_DATA[this.FORM_ID].defaults[item];

		// Check if conditions are valid or not. This is where the magic happens.
		const isValid = values.map((validItem) => validItem.every(Boolean)).some(Boolean);

		const fieldElement = this.utils.getFieldByName(element, item);

		if (values.length) {
			if (defaults !== this.HIDE) {
				if (isValid) {
					fieldElement.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
				} else {
					fieldElement.classList.remove(this.utils.SELECTORS.CLASS_HIDDEN);
				}
			} else {
				if (isValid) {
					fieldElement.classList.remove(this.utils.SELECTORS.CLASS_HIDDEN);
				} else {
					fieldElement.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
				}
			}
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
				DATA_FIELDS: this.DATA_FIELDS,
				DATA_EVENT_ITEMS: this.DATA_EVENT_ITEMS,
				DATA_REFERENCE: this.DATA_REFERENCE,
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
				setInit: (element) => {
					this.setInit(element);
				},
				setListeners: () => {
					this.setListeners();
				},
				areAllRulesValid: (logic, item) => {
					return this.areAllRulesValid(logic, item);
				},
				// Or: (rule, inputValue, item, index) => {
				// 	return this.Or(rule, inputValue, item, index);
				// },
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
