import { debounce } from '@eightshift/frontend-libs/scripts/helpers';
import { CONDITIONAL_TAGS_LOGIC, Utils } from './../../form/assets/utilities';

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

		this.OR = CONDITIONAL_TAGS_LOGIC.OR;
		this.AND = CONDITIONAL_TAGS_LOGIC.AND;

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
				events: [],
				reference: {},
			},
		};

		this.setValues(element);
		this.setInternalData(element);

		console.log(this.INTERNAL_DATA);

		// Init all items that has condition to show, they are hidden by initial state.
		// this.setInitShow(element);

		// Init all items that has condition to hide, they are visible by default but if the value is populated after from refresh the conditions should apply.
		// this.setInitHide(element);

		this.setListeners(element);
	}

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

	setInternalData(element) {
		const fields = element.querySelectorAll(`[${this.utils.DATA_ATTRIBUTES.conditionalTags}]`);

		[...fields].forEach((field) => {
			const name = field.getAttribute(this.utils.DATA_ATTRIBUTES.fieldName);
			const tags = field.getAttribute(this.utils.DATA_ATTRIBUTES.conditionalTags);

			if (tags && name) {
				this.INTERNAL_DATA[this.FORM_ID] = {
					...this.INTERNAL_DATA[this.FORM_ID],
					fields: {
						...this.INTERNAL_DATA[this.FORM_ID].fields,
						[name]: {
							[this.SHOW]: [],
							[this.HIDE]: [],
						}
					},
					reference: {
						...this.INTERNAL_DATA[this.FORM_ID].reference,
						[name]: {
							[this.SHOW]: [],
							[this.HIDE]: [],
						}
					},
				}

				this.setData(tags, name, this.SHOW);
				this.setData(tags, name, this.HIDE);
			}
		});
	}

	setData(data, name, type) {
		const tag = JSON.parse(data);

		const dataItem = tag?.[0]?.[type];

		if (dataItem.length > 0) {
			this.INTERNAL_DATA[this.FORM_ID].fields[name][type] = dataItem;
			this.INTERNAL_DATA[this.FORM_ID].reference[name][type] = this.getDataReference(dataItem, name);
		}
	}

	getDataReference(items, name) {
		const output = [];

		items.forEach((item) => {
			item.forEach((inner) => {

				const itemName = inner[0];

				output.push(false);

				this.INTERNAL_DATA[this.FORM_ID].events[itemName] = [
					...this.INTERNAL_DATA[this.FORM_ID].events[itemName] ?? [],
					name,
				];

				// this.INTERNAL_DATA[this.FORM_ID].events = {
				// 	...this.INTERNAL_DATA[this.FORM_ID].events,
				// 	[item[0][0]]: [
				// 		...this.INTERNAL_DATA[this.FORM_ID].events[item[0][0]],
				// 		name,
				// 	],
				// }
				// console.log(inner);
			});
			// if (item.length === 1) {
			// 	this.INTERNAL_DATA[this.FORM_ID].events = {
			// 		...this.INTERNAL_DATA[this.FORM_ID].events,
			// 		[item[0][0]]: 
			// 	}
			// 	output.push([false]);
			// } else {
			// 	item.forEach((inner) => {
			// 		this.INTERNAL_DATA[this.FORM_ID].events.push(inner[0]);
			// 	});
			// }
		});

		return output;
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
		for (const [name, value] of Object.entries(this.INTERNAL_DATA?.[formId]?.[this.HIDE])) {
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
		for (const [name, value] of Object.entries(this.INTERNAL_DATA?.[formId]?.[this.SHOW])) {

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
		for (const [name] of Object.entries(this.INTERNAL_DATA[this.FORM_ID].fields)) {
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

		if (logic === this.utils.CONDITIONAL_TAGS_LOGIC.OR) {
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
	isRuleValid(rule, value, name, type, parent, index) {
		console.log(rule, value, name, type, parent, index);
		// Used for all type of action.
		// Push true for each valid rule and later compare number of rules with the length of this array.
		this.INTERNAL_DATA[this.FORM_ID].reference[name][type][parent][index] = this.CONDITIONAL_TAGS_OPERATORS[rule[1]](value, rule[2]);
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
		this.setValues(element);

		for (const [type, values] of Object.entries(this.INTERNAL_DATA[this.FORM_ID].fields[inputName])) {
			if (!values.length) {
				continue;
			}

			values.forEach((items, index) => {
				this.runDataType(inputName, type, items, parent);
			});
		};

		// Check mapped data of items that needs to have event listener attached to it.
		// this.INTERNAL_DATA?.[formId]?.[this.DATA_EVENT_ITEMS]?.[inputName].map((item) => {
		// 	// Find all conditional tags for this input.
		// 	const tags = this.INTERNAL_DATA[this.DATA_FIELDS][item];

		// 	// Find that input item but ID.
		// 	const input = document.querySelector(`${this.utils.formSelector} [name="${item}"]`);

		// 	// Bailout if non existing.
		// 	if (!input) {
		// 		return;
		// 	}

		// 	const {
		// 		action,
		// 		logic,
		// 		rules,
		// 	} = tags;

		// 	// Loop all rules on this input.
		// 	rules.map((rule, index) => {
		// 		const {
		// 			id,
		// 			inner,
		// 		} = rule;

		// 		// Find only rules applied to this this input.
		// 		if (id !== inputName) {
		// 			return;
		// 		}

		// 		// Find input field selector.
		// 		let field = '';

		// 		if (inner) {
		// 			if (input.type === 'select-one') {
		// 				// select.
		// 				field = input.closest(this.utils.fieldSelector).querySelector(`.choices__item--choice[data-value="${inner}"]`);
		// 			} else {
		// 				// checkbox/radio.
		// 				field = input.parentNode.parentNode;
		// 			}
		// 		} else {
		// 			// input/textarea.
		// 			field = input.closest(this.utils.fieldSelector);
		// 		}

		// 		if (field) {
		// 			this.Or(rule, inputValue, item, index);

		// 		// Validate rule by checking input value.
		// 			if (this.areAllRulesValid(logic, item)) {
		// 				// If rule is valid do action.
		// 				if (action === this.utils.CONDITIONAL_TAGS_ACTIONS.SHOW) {
		// 					field.classList.remove(this.utils.SELECTORS.CLASS_HIDDEN);
		// 				} else {
		// 					field.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
		// 				}
		// 			} else {
		// 				// If rule is not valid do action by resting the field to the original state.
		// 				if (action === this.utils.CONDITIONAL_TAGS_ACTIONS.SHOW) {
		// 					field.classList.add(this.utils.SELECTORS.CLASS_HIDDEN);
		// 				} else {
		// 					field.classList.remove(this.utils.SELECTORS.CLASS_HIDDEN);
		// 				}
		// 			}
		// 		}
		// 	});
		// });
	};

	runDataType(name, type, values, parent) {
		console.log(name, type, values);

		if (!values.length) {
			return;
		}

		values.forEach((rule, index) => {
			if (rule.length > 1) {
			} else {
				const value = this.INTERNAL_DATA[this.FORM_ID].values[name];

				if (value) {
					this.isRuleValid(rule, name, value, type, parent, index);
				}
			}
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
