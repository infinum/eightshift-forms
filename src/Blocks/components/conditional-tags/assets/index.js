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
		for(const [name] of this.state.getStateElements(formId)) {
			this.setField(name, formId);
		}
	}

	setField(name, formId) {
		this.state.getStateFormConditionalTagsEvents(formId)?.[name]?.forEach((eventName) => {
			this.setFieldsRules(eventName, formId);
		});

		for(const [name] of this.state.getStateElements(formId)) {
			const field = this.state.getStateElementField(name, formId);

			// Find defaults to know what direction to use.
			const defaults = this.state.getStateElementConditionalTagsDefaults(name, formId);

			// Check if conditions are valid or not. This is where the magic happens.
			const isValid = this.state.getStateElementConditionalTagsRef(name, formId).map((validItem) => validItem.every(Boolean)).some(Boolean);

			// Reset to original state.
			(defaults !== this.HIDE) ? field.classList.remove(this.state.getStateSelectorsClassHidden()) : field.classList.add(this.state.getStateSelectorsClassHidden());

			if (isValid) {
				// Change state.
				(defaults !== this.HIDE) ? field.classList.add(this.state.getStateSelectorsClassHidden()) : field.classList.remove(this.state.getStateSelectorsClassHidden());
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
	setFieldsRules(name, formId) {
		// Loop all fields.
		let output = this.state.getStateElementConditionalTagsRef(name, formId);

		this.state.getStateElementConditionalTagsTags(name, formId).forEach((items, parent) => {
			// Loop all inner fields.
			items.forEach((inner, index) => {
				const value = this.state.getStateElementValue(inner[0], formId);

				// Do the check based on the operator and set reference data with the correct state.
				output[parent][index] = this.OPERATORS[inner[1]](value, inner[2]);
			});
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
		this.state.setStateWindow();

		window[prefix].conditionalTags = {}
	}
}
