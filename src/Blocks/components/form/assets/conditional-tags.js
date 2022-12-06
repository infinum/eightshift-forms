import { debounce } from '@eightshift/frontend-libs/scripts/helpers';
import { Utils } from './utilities';

/**
 * Main conditon tags class.
 */
export class ConditionalTags {
	constructor(options) {
		/** @type Utils */
		this.utils = options.utils ?? new Utils();

		// Internal data constants.
		this.DATA_FIELDS = 'fields';
		this.DATA_EVENT_ITEMS = 'eventItems';
		this.DATA_REFERENCE = 'reference';

		// Internal Data.
		this.INTERNAL_DATA = {
			[this.DATA_FIELDS]: {},
			[this.DATA_EVENT_ITEMS]: {},
			[this.DATA_REFERENCE]: {},
		};

		// Map all conditional logic as a object.
		this.CONDITIONAL_LOGIC = {
			[this.utils.CONDITIONAL_TAGS.IS]: (input, value) => value === input,
			[this.utils.CONDITIONAL_TAGS.ISN]: (input, value) => value !== input,
			[this.utils.CONDITIONAL_TAGS.GT]: (input, value) => parseFloat(String(input)) > parseFloat(String(value)),
			[this.utils.CONDITIONAL_TAGS.GTE]: (input, value) => parseFloat(String(input)) >= parseFloat(String(value)),
			[this.utils.CONDITIONAL_TAGS.LT]: (input, value) => parseFloat(String(input)) < parseFloat(String(value)),
			[this.utils.CONDITIONAL_TAGS.LTE]: (input, value) => parseFloat(String(input)) <= parseFloat(String(value)),
			[this.utils.CONDITIONAL_TAGS.C]: (input, value) => input.includes(value),
			[this.utils.CONDITIONAL_TAGS.SW]: (input, value) => input.startsWith(value),
			[this.utils.CONDITIONAL_TAGS.EW]: (input, value) => input.endsWith(value),
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
		const data = element.getAttribute(this.utils.DATA_ATTRIBUTES.conditionalTags);
		if (data) {
			this.setData(data);
			this.setInit();
			this.setListeners();
		}
	}

	/**
	 * Prepare data for later usage.
	 * 
	 * @param {string} data Tags data from json string.
	 *
	 * @public
	 */
	setData(data) {
		Object.entries(JSON.parse(data)).forEach(([key, value]) => {
			this.INTERNAL_DATA[this.DATA_REFERENCE][key] = [];

			const internalValue = JSON.parse(value);

			this.INTERNAL_DATA[this.DATA_FIELDS][key] = {
				'action': internalValue[0],
				'logic': internalValue[1],
				'rules': internalValue[2].map((innerItem) => {

					if (!(innerItem[0] in this.INTERNAL_DATA[this.DATA_EVENT_ITEMS])) {
						this.INTERNAL_DATA[this.DATA_EVENT_ITEMS][innerItem[0]] = [];
					}

					this.INTERNAL_DATA[this.DATA_EVENT_ITEMS][innerItem[0]].push(key);
					this.INTERNAL_DATA[this.DATA_REFERENCE][key].push(false);

					return {
						'id': innerItem[0],
						'operator': innerItem[1],
						'value': innerItem[2],
					};
				})
			};
		});
	}

	/**
	 * Set init state of fields on page load.
	 *
	 * @public
	 */
	setInit() {
		for (const [key, value] of Object.entries(this.INTERNAL_DATA[this.DATA_FIELDS])) {
			const item = document.querySelector(`${this.utils.formSelector} [name="${key}"]`);

			if (!item) {
				continue;
			}

			const field = item.closest(this.utils.fieldSelector);

			const {
				action,
			} = value;

			if (action === this.utils.CONDITIONAL_TAGS.SHOW) {
				// If action is to show the initial state is hide.
				field.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
			}
		}
	}

	/**
	 * Add event listeners to all items that need it.
	 *
	 * @public
	 */
	setListeners() {
		// Loop items from all rules mapped earlier.
		Object.entries(this.INTERNAL_DATA[this.DATA_EVENT_ITEMS]).forEach(([key]) => {
			// Find that item by ID.
			const item = document.querySelector(`${this.utils.formSelector} [name="${key}"]`);

			// Bailout if non existing.
			if (!item) {
				return;
			}

			// Add event.
			if (this.utils.isCustom(item) && item.localName === 'select') {
				item.addEventListener('change', this.onCustomSelectChangeEvent);
			} else {
				item.addEventListener('input', debounce(this.onFieldChangeEvent, 250));
			}
		});
	}

	/**
	 * Test if one or all rules are valid.
	 *
	 * @param {string} logic Logic operator.
	 * @param {string} item Conditional tag one item.
	 *
	 * @public
	 */
	areAllRulesValid(logic, item) {
		const ref = this.INTERNAL_DATA[this.DATA_REFERENCE][item];

		if (logic === this.utils.CONDITIONAL_TAGS.ANY) {
			if (ref.includes(true)) {
				return true;
			}
		} else {
			if (ref.every((element) => element === true)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Test if one rule is valid.
	 *
	 * @param {object} rule One rule object.
	 * @param {string} inputValue Value from input.
	 * @param {string} item Conditional tag one item.
	 * @param {number} index Loop index number.
	 *
	 * @public
	 */
	isRuleValid(rule, inputValue, item, index) {
		const {
			operator,
			value,
		} = rule;

		const output = this.CONDITIONAL_LOGIC[operator](inputValue, value);

		// Used for all type of action.
		// Push true for each valid rule and later compare number of rules with the length of this array.
		this.INTERNAL_DATA[this.DATA_REFERENCE][item][index] = output;

		return output;
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
	onCustomSelectChangeEvent = (event) => {
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
		// Map all current input data.
		const inputName = event.target.name;
		const inputType = event.target.type;
		let inputValue = event.target.value;

		switch (inputType) {
			case 'checkbox':
			case 'radio':
				inputValue = event.target.checked ? 'true' : 'false';
				break;
		}

		// Check mapped data of items that needs to have event listener attached to it.
		this.INTERNAL_DATA[this.DATA_EVENT_ITEMS][inputName].map((item) => {
			// Find all conditional tags for this input.
			const tags = this.INTERNAL_DATA[this.DATA_FIELDS][item];

			// Find that input item but ID.
			const input = document.querySelector(`${this.utils.formSelector} [name="${item}"]`);

			// Bailout if non existing.
			if (!input) {
				return;
			}

			const {
				action,
				logic,
				rules,
			} = tags;

			// Loop all rules on this input.
			rules.map((rule, index) => {
				const {
					id,
				} = rule;

				// Find only rules applied to this this input.
				if (id !== inputName) {
					return;
				}

				// Find input field selector.
				const field = input.closest(this.utils.fieldSelector);

				this.isRuleValid(rule, inputValue, item, index);

				// Validate rule by checking input value.
				if (this.areAllRulesValid(logic, item)) {
					// If rule is valid do action.
					if (action === this.utils.CONDITIONAL_TAGS.SHOW) {
						field.classList.remove(this.utils.SELECTORS.CLASS_HIDDEN);
					} else {
						field.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
					}
				} else {
					// If rule is not valid do action by resting the field to the original state.
					if (action === this.utils.CONDITIONAL_TAGS.SHOW) {
						field.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
					} else {
						field.classList.remove(this.utils.SELECTORS.CLASS_HIDDEN);
					}
				}
			});
		});
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
		if (typeof window[this.prefix]?.conditionalTags === 'undefined') {
			window[this.utils.prefix].conditionalTags = {
				DATA_FIELDS: this.DATA_FIELDS,
				DATA_EVENT_ITEMS: this.DATA_EVENT_ITEMS,
				DATA_REFERENCE: this.DATA_REFERENCE,
				INTERNAL_DATA: this.INTERNAL_DATA,
				CONDITIONAL_LOGIC: this.CONDITIONAL_LOGIC,
				init() {
					this.init();
				},
				initOnlyForms() {
					this.initOnlyForms();
				},
				initOne(element) {
					this.initOne(element);
				},
				setData(data) {
					this.setData(data);
				},
				setInit() {
					this.setInit();
				},
				setListeners() {
					this.setListeners();
				},
				onCustomSelectChangeEvent(event) {
					this.onCustomSelectChangeEvent(event);
				},
				onFieldChangeEvent(event) {
					this.onFieldChangeEvent(event);
				},
				areAllRulesValid(logic, item) {
					this.areAllRulesValid(logic, item);
				},
				isRuleValid(rule, inputValue, item, index) {
					this.isRuleValid(rule, inputValue, item, index);
				},
			};
		}
	}
}
