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
		const formId = element.getAttribute(this.utils.DATA_ATTRIBUTES.formPostId);
		const interval = setInterval(() => {
			if (window[this.utils.prefix].utils.FORMS?.[formId]) {
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
		const elements = element.querySelectorAll(`[${this.utils.DATA_ATTRIBUTES.conditionalTags}]`);

		const data = {};

		[...elements].forEach((element) => {
			const name = element.getAttribute(this.utils.DATA_ATTRIBUTES.fieldName);
			const tags = element.getAttribute(this.utils.DATA_ATTRIBUTES.conditionalTags);

			if (tags && name) {
				data[name] = JSON.parse(tags);
			}
		});

		if (data) {
			this.setData(data);
			this.setInit();
			this.setListeners();
		}
	}

	/**
	 * Prepare data for later usage.
	 * 
	 * @param {string} data Tags data array from each field.
	 *
	 * @public
	 */
	setData(data) {
		Object.entries(data).forEach(([key, value]) => {
			this.INTERNAL_DATA[this.DATA_REFERENCE][key] = [];

			this.INTERNAL_DATA[this.DATA_FIELDS][key] = {
				'action': value[0],
				'logic': value[1],
				'rules': value[2].map((innerItem) => {

					if (!(innerItem[0] in this.INTERNAL_DATA[this.DATA_EVENT_ITEMS])) {
						this.INTERNAL_DATA[this.DATA_EVENT_ITEMS][innerItem[0]] = [];
					}

					this.INTERNAL_DATA[this.DATA_EVENT_ITEMS][innerItem[0]].push(key);
					this.INTERNAL_DATA[this.DATA_REFERENCE][key].push(false);

					return {
						'id': innerItem[0],
						'operator': innerItem[1],
						'value': innerItem[2],
						'inner': innerItem[3],
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
			const input = document.querySelector(`${this.utils.formSelector} [name="${key}"]`);

			if (!input) {
				continue;
			}

			const {
				action,
			} = value;

			const isEmptyRule = value.rules.some((element) => element.value === '');
			const isInner = value.rules.filter((element) => element.inner !== '');

			let field = '';

			if (isInner.length) {
				if (input.type === 'select-one') {
					// select.
					field = input.closest(this.utils.fieldSelector).querySelector(`.choices__item--choice[data-value="${isInner?.[0]?.inner}"]`);
				} else {
					// checkbox/radio.
					field = input.parentNode.parentNode;
				}
			} else {
				// input/textarea.
				field = input.closest(this.utils.fieldSelector);
			}

			if (field && action === this.utils.CONDITIONAL_TAGS_ACTIONS.SHOW && !isEmptyRule) {
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
			const items = document.querySelectorAll(`${this.utils.formSelector} [name="${key}"]`);

			// Bailout if non existing.
			if (!items) {
				return;
			}

			[...items].forEach((element) => {
				// Add event.
				if (element.localName === 'select') {
					element.addEventListener('change', this.onChangeEvent);
				} else {
					element.addEventListener('input', debounce(this.onFieldChangeEvent, 250));
				}
			});
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

		if (logic === this.utils.CONDITIONAL_TAGS_LOGIC.ANY) {
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
		// Map all current input data.
		const inputName = event.target.name;
		const inputType = event.target.type;
		let inputValue = event.target.value;

		switch (inputType) {
			case 'checkbox':
			case 'radio':
				if (!event.target.checked) {
					inputValue = '';
				}
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
					inner,
				} = rule;

				// Find only rules applied to this this input.
				if (id !== inputName) {
					return;
				}

				// Find input field selector.
				let field = '';

				if (inner) {
					if (input.type === 'select-one') {
						// select.
						field = input.closest(this.utils.fieldSelector).querySelector(`.choices__item--choice[data-value="${inner}"]`);
					} else {
						// checkbox/radio.
						field = input.parentNode.parentNode;
					}
				} else {
					// input/textarea.
					field = input.closest(this.utils.fieldSelector);
				}

				if (field) {
					this.isRuleValid(rule, inputValue, item, index);

				// Validate rule by checking input value.
					if (this.areAllRulesValid(logic, item)) {
						// If rule is valid do action.
						if (action === this.utils.CONDITIONAL_TAGS_ACTIONS.SHOW) {
							field.classList.remove(this.utils.SELECTORS.CLASS_HIDDEN);
						} else {
							field.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
						}
					} else {
						// If rule is not valid do action by resting the field to the original state.
						if (action === this.utils.CONDITIONAL_TAGS_ACTIONS.SHOW) {
							field.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
						} else {
							field.classList.remove(this.utils.SELECTORS.CLASS_HIDDEN);
						}
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
				setData: (data) => {
					this.setData(data);
				},
				setInit: () => {
					this.setInit();
				},
				setListeners: () => {
					this.setListeners();
				},
				areAllRulesValid: (logic, item) => {
					this.areAllRulesValid(logic, item);
				},
				isRuleValid: (rule, inputValue, item, index) => {
					this.isRuleValid(rule, inputValue, item, index);
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
