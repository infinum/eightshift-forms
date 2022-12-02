import { debounce } from '@eightshift/frontend-libs/scripts/helpers';
import { Utils } from './utilities';

/**
 * Main conditon tags class.
 */
export class ConditionalTags {
	constructor(options) {
		console.log(options);
		/** @type Utils */
		this.utils = options ?? new Utils();

		// Data
		this.data = options.data ?? '';

		// Internal Data Constants.
		this.DATA_FIELDS = 'fields';
		this.DATA_EVENT_ITEMS = 'eventItems';
		this.DATA_REFERENCE = 'reference';

		// Internal Data.
		this.internalData = {
			[this.DATA_FIELDS]: {},
			[this.DATA_EVENT_ITEMS]: {},
			[this.DATA_REFERENCE]: {},
		};

		// Map all conditional logic as ca object.
		this.conditionalLogic = {
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

	// Init all actions.
	init = () => {
		this.setData();
		this.setInit();
		this.setListeners();
	};

	// Prepare data.
	setData() {
		Object.entries(JSON.parse(this.data)).forEach(([key, value]) => {
			this.internalData[this.DATA_REFERENCE][key] = [];

			const internalValue = JSON.parse(value);

			this.internalData[this.DATA_FIELDS][key] = {
				'action': internalValue[0],
				'logic': internalValue[1],
				'rules': internalValue[2].map((innerItem) => {

					if (!(innerItem[0] in this.internalData[this.DATA_EVENT_ITEMS])) {
						this.internalData[this.DATA_EVENT_ITEMS][innerItem[0]] = [];
					}

					this.internalData[this.DATA_EVENT_ITEMS][innerItem[0]].push(key);
					this.internalData[this.DATA_REFERENCE][key].push(false);

					return {
						'id': innerItem[0],
						'operator': innerItem[1],
						'value': innerItem[2],
					};
				})
			};
		});
	}

	// Set init state of fields on page load.
	setInit() {
		for (const [key, value] of Object.entries(this.internalData[this.DATA_FIELDS])) {
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

	// Add event listeners to all items that need it.
	setListeners() {
		// Loop items from all rules mapped earlier.
		Object.entries(this.internalData[this.DATA_EVENT_ITEMS]).forEach(([key]) => {
			// Find that item by ID.
			const item = document.querySelector(`${this.utils.formSelector} [name="${key}"]`);

			// Bailout if non existing.
			if (!item) {
				return;
			}

			// Add event.
			if (this.utils.isCustom(item) && item.localName === 'select') {
				item.addEventListener('change', this.onCustomSelectChange);
			} else {
				item.addEventListener('input', debounce(this.onFieldChange, 250));
			}
		});
	}

	onCustomSelectChange = (event) => {
		this.onFieldChange(event);
	};

	// Do action on input change.
	onFieldChange = (event) => {
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
		this.internalData[this.DATA_EVENT_ITEMS][inputName].map((item) => {
			// Find all conditional tags for this input.
			const tags = this.internalData[this.DATA_FIELDS][item];

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

	// Test if one or all rules are valid.
	areAllRulesValid(logic, item) {
		const ref = this.internalData[this.DATA_REFERENCE][item];

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

	// Test if one rule is valid.
	isRuleValid(rule, inputValue, item, index) {
		const {
			operator,
			value,
		} = rule;

		const output = this.conditionalLogic[operator](inputValue, value);

		// Used for all type of action.
		// Push true for each valid rule and later compare number of rules with the length of this array.
		this.internalData[this.DATA_REFERENCE][item][index] = output;

		return output;
	}
}
