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
		this.DATA_EVENT_ITEMS = 'eventItems';
		this.DATA_REFERENCE = 'reference';
		this.DATA_SHOW = this.utils.CONDITIONAL_TAGS_ACTIONS.SHOW;
		this.DATA_HIDE = this.utils.CONDITIONAL_TAGS_ACTIONS.HIDE;

		// Internal Data.
		this.INTERNAL_DATA = {};

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
			if (this.utils.getFormStateByKey('isLoaded', formId)) {
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
		const fields = element.querySelectorAll(`[${this.utils.DATA_ATTRIBUTES.conditionalTags}]`);
		const formId = element.getAttribute(this.utils.DATA_ATTRIBUTES.formPostId);

		this.INTERNAL_DATA = {
			[formId]: {
				[this.DATA_SHOW]: {},
				[this.DATA_HIDE]: {},
				[this.DATA_EVENT_ITEMS]: [],
				[this.DATA_REFERENCE]: [],
			}
		};

		console.log(fields);

		[...fields].forEach((field) => {
			const name = field.getAttribute(this.utils.DATA_ATTRIBUTES.fieldName);
			const tags = field.getAttribute(this.utils.DATA_ATTRIBUTES.conditionalTags);

			if (tags && name) {
				const tag = JSON.parse(tags);

				this.INTERNAL_DATA[formId][this.DATA_SHOW][name] = tag?.[0]?.[this.DATA_SHOW];
				this.INTERNAL_DATA[formId][this.DATA_HIDE][name] = tag?.[0]?.[this.DATA_HIDE];
			}
		});

		this.setData();

		// Init all items that has condition to show, they are hidden by initial state.
		this.setInitShow(element);

		// Init all items that has condition to hide, they are visible by default but if the value is populated after from refresh the conditions should apply.
		// this.setInitHide(element);

		// this.setListeners(element);
	}

	setData() {

		console.log(this.INTERNAL_DATA);


		// if (name in object) {
		// }

		// this.INTERNAL_DATA[formId][this.DATA_REFERENCE][name].push(false);

		// if (!this.INTERNAL_DATA[formId][this.DATA_EVENT_ITEMS].includes(name)) {
		// 	this.INTERNAL_DATA[formId][this.DATA_EVENT_ITEMS].push(name);
		// }
	}

	/**
	 * Set init state of fields on page load.
	 *
	 * @public
	 */
	setInitHide(element) {
		// TODO
		const formId = element.getAttribute(this.utils.DATA_ATTRIBUTES.formPostId);

		// Loop all items to hide.
		for (const [name, value] of Object.entries(this.INTERNAL_DATA?.[formId]?.[this.DATA_HIDE])) {
			if (value.length === 0) {
				continue;
			}

			const field = this.utils.getFieldByName(element, name);

			if (!field) {
				continue;
			}

			// Find field type.
			const type = field.getAttribute(this.utils.DATA_ATTRIBUTES.fieldType);

			// Find all items that have inner items.
			const innerOptions = value.flat().map((item) => item?.[3] !== '' && item[3]).filter(item => item);

			switch (type) {
				case 'select':
					innerOptions.forEach((inner) => {
						const innerItem = field.querySelector(`.choices__item--choice[data-value="${inner}"]`);

						if (innerItem) {
							innerItem.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
						}
					});
					break;
				case 'checkboxes':
					case 'radios':
						innerOptions.forEach((inner) => {
							const innerItem = field.querySelector(`input[value="${inner}"]`)?.parentNode?.parentNode;

							if (innerItem) {
								innerItem.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
							}
						});
					break;
				default:
					field.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
					break;
			}
		}
		// const {
			// 	action,
			// } = value;

			// const isEmptyRule = value.rules.some((element) => element.value === '');
			// const isInner = value.rules.filter((element) => element.inner !== '');

			// let field = '';

			// if (isInner.length) {
			// 	if (input.type === 'select-one') {
			// 		// select.
			// 		field = input.closest(this.utils.fieldSelector).querySelector(`.choices__item--choice[data-value="${isInner?.[0]?.inner}"]`);
			// 	} else {
			// 		// checkbox/radio.
			// 		field = input.parentNode.parentNode;
			// 	}
			// } else {
			// 	// input/textarea.
			// 	field = input.closest(this.utils.fieldSelector);
			// }

			// if (field && action === this.utils.CONDITIONAL_TAGS_ACTIONS.SHOW && !isEmptyRule) {
			// 	// If action is to show the initial state is hide.
			// 	field.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
			// }
	}

	/**
	 * Set init state of fields on page load.
	 *
	 * @public
	 */
	setInitShow(element) {
		const formId = element.getAttribute(this.utils.DATA_ATTRIBUTES.formPostId);

		// Loop all items to hide.
		for (const [name, value] of Object.entries(this.INTERNAL_DATA?.[formId]?.[this.DATA_SHOW])) {

			if (value.length === 0) {
				continue;
			}

			const field = this.utils.getFieldByName(element, name);

			if (!field) {
				continue;
			}

			// Find field type.
			const type = field.getAttribute(this.utils.DATA_ATTRIBUTES.fieldType);

			// Find all items that have inner items.
			const innerOptions = value.flat().map((item) => item?.[3] !== '' && item[3]).filter(item => item);

			switch (type) {
				case 'select':
					innerOptions.forEach((inner) => {
						const innerItem = field.querySelector(`.choices__item--choice[data-value="${inner}"]`);

						if (innerItem) {
							innerItem.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
						}
					});
					break;
				case 'checkboxes':
				case 'radios':
					innerOptions.forEach((inner) => {
						const innerItem = field.querySelector(`input[value="${inner}"]`)?.parentNode?.parentNode;

						if (innerItem) {
							innerItem.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
						}
					});
					break;
				default:
					field.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
					break;
			}
		}
	}

	/**
	 * Add event listeners to all items that need it.
	 *
	 * @public
	 */
	setListeners(element) {
		const formId = element.getAttribute(this.utils.DATA_ATTRIBUTES.formPostId);

		this.INTERNAL_DATA?.[formId]?.[this.DATA_EVENT_ITEMS].forEach((name) => {
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
		const inputElement = event.target;
		const inputName = inputElement.name;
		const inputType = inputElement.type;
		let inputValue = inputElement.value;

		switch (inputType) {
			case 'checkbox':
			case 'radio':
				if (!inputElement.checked) {
					inputValue = '';
				}
				break;
		}

		const element = inputElement.closest(this.utils.formSelector);
		const formId = element.getAttribute(this.utils.DATA_ATTRIBUTES.formPostId);
		console.log(this.INTERNAL_DATA?.[formId]?.[this.DATA_EVENT_ITEMS]);

		// Check mapped data of items that needs to have event listener attached to it.
		this.INTERNAL_DATA?.[formId]?.[this.DATA_EVENT_ITEMS]?.[inputName].map((item) => {
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
		if (typeof window?.[this.utils.getPrefix()]?.conditionalTags === 'undefined') {
			window[this.utils.getPrefix()].conditionalTags = {
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
				setInit: (element) => {
					this.setInit(element);
				},
				setListeners: () => {
					this.setListeners();
				},
				areAllRulesValid: (logic, item) => {
					return this.areAllRulesValid(logic, item);
				},
				isRuleValid: (rule, inputValue, item, index) => {
					return this.isRuleValid(rule, inputValue, item, index);
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
