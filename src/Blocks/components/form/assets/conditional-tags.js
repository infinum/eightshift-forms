
import { State } from './state';
import {
	CONDITIONAL_TAGS_OPERATORS,
	CONDITIONAL_TAGS_ACTIONS,
	CONDITIONAL_TAGS_LOGIC,
} from '../../conditional-tags/assets/utils';
import { prefix, setStateWindow } from './state/init';

/**
 * Main conditon tags class.
 */
export class ConditionalTags {
	constructor() {
		this.state = new State();

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

		// Set all public methods.
		this.publicMethods();
	}

	////////////////////////////////////////////////////////////////
	// Public methods
	////////////////////////////////////////////////////////////////
	/**
	 * Init one form by element after the loaded event is fired.
	 * 
	 * @param {string} fromId Form Id.
	 *
	 * @returns {void}
	 */
	initOne(formId) {
		if (this.state.getStateConfigIsAdmin(formId)) {
			return;
		}

		this.state.getStateFormElement(formId).addEventListener(
			this.state.getStateEventsFormJsLoaded(),
			this.onInitEvent
		);
	}

	/**
	 * Init forms conditional logic.
	 *
	 * @param {string} fromId Form Id.
	 *
	 * @returns {void}
	 */
	initForms(formId) {
		// Get all tags from state.
		let tags = this.state.getStateFormConditionalTagsForm(formId);
		if (!tags) {
			return;
		}

		// Loop tags.
		tags.forEach(([tagName, tagVisibility, tagInner]) => {
			const field = this.state.getStateElementField(tagName, formId);

			// Bailout if field doesn't exist.
			if (!field) {
				return;
			}

			// Get field type.
			const type = this.state.getStateElementType(tagName, formId);

			// Prepare element placeholder.
			let innerItem = '';

			// If tag has inner items items.
			if (tagInner) {
				if (type === 'select') {
					// Use for select.
					innerItem = field.querySelector(`.choices__item--choice[data-value="${tagInner}"]`);
				} else {
					// Use for radio and checkbox.
					innerItem = this.state.getStateElementItemsField(tagName, tagInner, formId);
				}
			} else {
				// input/textarea, etc.
				innerItem = this.state.getStateElementField(tagName, formId);
			}

			// Bailout if we have no element to apply.
			if (!innerItem) {
				return;
			}

			if (tagVisibility === this.HIDE) {
				// Handle hide state.
				innerItem?.classList?.add(this.state.getStateSelectorsClassHiddenConditionalTags());

				// Select must set internal state for attributes due to rerendering in the choices lib.
				if (tagInner && type === 'select') {
					const customItem = this.state.getStateElementCustom(tagName, formId).config.choices.filter((item) => item.value === tagInner)?.[0];

					if (customItem) {
						customItem.customProperties[this.state.getStateAttribute('selectVisibility')] = this.state.getStateSelectorsClassHiddenConditionalTags();
					}
				}
			} else {
				// Handle show state.
				innerItem?.classList?.add(this.state.getStateSelectorsClassVisibleConditionalTags());

				// Select must set internal state for attributes due to rerendering in the choices lib.
				if (tagInner && type === 'select') {
					const customItem = this.state.getStateElementCustom(tagName, formId).config.choices.filter((item) => item.value === tagInner)?.[0];

					if (customItem) {
						customItem.customProperties[this.state.getStateAttribute('selectVisibility')] = this.state.getStateSelectorsClassVisibleConditionalTags();
					}
				}
			}
		});
	}

	/**
	 * Init fields conditional logic.
	 *
	 * @param {string} fromId Form Id.
	 *
	 * @returns {void}
	 */
	initFields(formId) {
		for(const [name] of this.state.getStateElements(formId)) {
			this.setField(formId, name);
		}
	}

	/**
	 * Set field conditional logic.
	 *
	 * @param {string} name Field name.
	 * @param {string} fromId Form Id.
	 *
	 * @returns {void}
	 */
	setField(formId, name) {
		// Check and set top level events and set rules.
		this.state.getStateFormConditionalTagsEvents(formId)?.[name]?.forEach((eventName) => {
			this.setFieldsRules(formId, eventName);
		});

		// Set top level fields state.
		this.setFieldTopLevel(formId);

		// Check and set inner level events and set rules.
		this.state.getStateFormConditionalTagsInnerEvents(formId)?.[name]?.forEach((eventName) => {
			this.setFieldsRulesInner(formId, eventName);
		});

		// Set inner level fields state.
		this.setFieldInner(formId);

		// Set inner level fields state - for select specific.
		this.setFieldInnerSelect(formId);
	}

	/**
	 * Set field top level state.
	 *
	 * @param {string} fromId Form Id.
	 *
	 * @returns {void}
	 */
	setFieldTopLevel(formId) {
		// Loop all elements.
		for(const [name] of this.state.getStateElements(formId)) {
			// Find field.
			const field = this.state.getStateElementField(name, formId);

			// Find defaults to know what direction to use.
			const defaults = this.state.getStateElementConditionalTagsDefaults(name, formId);

			// Check if conditions are valid or not. This is where the magic happens.
			const isValid = this.state.getStateElementConditionalTagsRef(name, formId)?.map((validItem) => validItem.every(Boolean)).some(Boolean);

			// Reset to original state.
			(defaults !== this.HIDE) ? field?.classList?.remove(this.state.getStateSelectorsClassHiddenConditionalTags()) : field?.classList?.add(this.state.getStateSelectorsClassHiddenConditionalTags());

			if (isValid) {
				// Change state if valid.
				(defaults !== this.HIDE) ? field?.classList?.add(this.state.getStateSelectorsClassHiddenConditionalTags()) : field?.classList?.remove(this.state.getStateSelectorsClassHiddenConditionalTags());
			}
		}
	}

	/**
	 * Set field inner level state.
	 *
	 * @param {string} fromId Form Id.
	 *
	 * @returns {void}
	 */
	setFieldInner(formId) {
		// Loop all elements.
		for(const [name] of this.state.getStateElements(formId)) {
			// Find parent field.
			const parentField = this.state.getStateElementField(name, formId);

			// Find inner items.
			const items = Object.keys(this.state.getStateElementItems(name, formId) ?? []);

			// Loop inner items.
			items.forEach((innerName) => {
				const field = this.state.getStateElementItemsField(name, innerName, formId);

				// Find defaults to know what direction to use.
				const defaults = this.state.getStateElementConditionalTagsDefaultsInner(name, innerName, formId);

				// Check if conditions are valid or not. This is where the magic happens.
				const isValid = this.state.getStateElementConditionalTagsRefInner(name, innerName, formId).map((validItem) => validItem.every(Boolean)).some(Boolean);

				// Reset to original state.
				(defaults !== this.HIDE) ? field?.classList?.remove(this.state.getStateSelectorsClassHiddenConditionalTags()) : field?.classList?.add(this.state.getStateSelectorsClassHiddenConditionalTags());

				if (isValid) {
					// Change state if valid.
					(defaults !== this.HIDE) ? field?.classList?.add(this.state.getStateSelectorsClassHiddenConditionalTags()) : field?.classList?.remove(this.state.getStateSelectorsClassHiddenConditionalTags());
				}

				// If all items in in parent are hidden, hide the top level field.
				if (parentField.querySelectorAll(`.${this.state.getStateSelectorsClassHiddenConditionalTags()}`).length === items.length && items.length > 0) {
					parentField?.classList?.add(this.state.getStateSelectorsClassHiddenConditionalTags());
				} else {
					if (defaults === this.HIDE) {
						parentField?.classList?.remove(this.state.getStateSelectorsClassHiddenConditionalTags());
					}
				}
			});
		}
	}

	/**
	 * Set field inner level state - select only.
	 *
	 * @param {string} fromId Form Id.
	 *
	 * @returns {void}
	 */
	setFieldInnerSelect(formId) {
		// Loop all select elements.
		[...this.state.getStateElementByType('select', formId)].forEach(({name}) => {
			// Find parent field.
			const parentField = this.state.getStateElementField(name, formId);

			// Get choices object.
			const custom = this.state.getStateElementCustom(name, formId);

			// Get choices items.
			const items = custom?.choiceList?.element?.children ?? [];

			// Get active choice value.
			const activeItem = custom?.getValue(true);

			// Loop inner items.
			[...items].forEach((field) => {
				// Find item value.
				const innerName = field.getAttribute(this.state.getStateAttribute('selectValue'));

				// Bailout if placeholder.
				if (!innerName) {
					return;
				}

				// Find defaults to know what direction to use.
				const defaults = this.state.getStateElementConditionalTagsDefaultsInner(name, innerName, formId);

				// Check if conditions are valid or not. This is where the magic happens.
				const isValid = this.state.getStateElementConditionalTagsRefInner(name, innerName, formId).map((validItem) => validItem.every(Boolean)).some(Boolean);

				// Find option index.
				const index = field.getAttribute(this.state.getStateAttribute('selectId')) - 1;

				// Reset to original state.
				if (defaults !== this.HIDE) {
					// Set attribute.
					field.setAttribute(this.state.getStateAttribute('selectVisibility'), this.state.getStateSelectorsClassVisibleConditionalTags());

					// Set object config value due to rerendering issue.
					custom.config.choices[index].customProperties[this.state.getStateAttribute('selectVisibility')] = this.state.getStateSelectorsClassVisibleConditionalTags();
				} else {
					// Set attribute.
					field.setAttribute(this.state.getStateAttribute('selectVisibility'), this.state.getStateSelectorsClassHiddenConditionalTags());

					// Set object config value due to rerendering issue.
					custom.config.choices[index].customProperties[this.state.getStateAttribute('selectVisibility')] = this.state.getStateSelectorsClassHiddenConditionalTags();
				}

				if (isValid) {
					// Change state if valid.
					if (defaults !== this.HIDE) {
						// Set attribute.
						field.setAttribute(this.state.getStateAttribute('selectVisibility'), this.state.getStateSelectorsClassHiddenConditionalTags());

						// Set object config value due to rerendering issue.
						custom.config.choices[index].customProperties[this.state.getStateAttribute('selectVisibility')] = this.state.getStateSelectorsClassHiddenConditionalTags();

						// If current item to change is selected, unset the choice.
						if (innerName === activeItem) {
							// Filed will be unable to unset unless we have placeholder set.
							custom.setChoiceByValue('');
						}

					 } else {
						// Set attribute.
						field.setAttribute(this.state.getStateAttribute('selectVisibility'), this.state.getStateSelectorsClassVisibleConditionalTags());

						// Set object config value due to rerendering issue.
						custom.config.choices[index].customProperties[this.state.getStateAttribute('selectVisibility')] = this.state.getStateSelectorsClassVisibleConditionalTags();
					 }
				}

				// In case if option is hidden by default the logic is flipped.
				if (!isValid && defaults === this.HIDE) {
					// If current item to change is selected, unset the choice.
					if (innerName === activeItem) {
						// Filed will be unable to unset unless we have placeholder set.
						custom.setChoiceByValue('');
					}
				}

				// If all items in in parent are hidden, hide the top level field.
				if (parentField.querySelectorAll(`[${this.state.getStateAttribute('selectVisibility')}="${this.state.getStateSelectorsClassHiddenConditionalTags()}"]`).length === items.length && items.length > 0) {
					parentField?.classList?.add(this.state.getStateSelectorsClassHiddenConditionalTags());
					custom.setChoiceByValue('');
				} else {
					if (defaults === this.HIDE) {
						parentField?.classList?.remove(this.state.getStateSelectorsClassHiddenConditionalTags());
					}
				}
			});
		});
	}

	/**
	 * Do the actual logic of checking conditions for rule - inner item.
	 *
	 * @param {string} fromId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	setFieldsRulesInner(formId, name) {
		// Explode name because we can have inner items that have parent prefix.
		const [topName, innerName] = name.split('---');

		// Opulate current ref state.
		let output = this.state.getStateElementConditionalTagsRefInner(topName, innerName, formId);

		// Loop all conditional tags.
		this.state.getStateElementConditionalTagsTagsInner(topName, innerName, formId).forEach((items, parent) => {
			// Loop all inner fields.
			items.forEach(([innerName, innerCondition, innerValue], index) => {
				// Placeholder value to chack later.
				let value = '';

				// Get element type.
				const type = this.state.getStateElementType(innerName, formId);

				// Bailout if type is missing.
				if (!type) {
					return;
				}

				switch (type) {
					case 'checkbox':
						// If check box inner items are missing this applys to parent element not children.
						if (innerValue === '') {
							// If all inner items are empty and not set this will output value to empty.
							if (Object.values(this.state.getStateElementValue(innerName, formId)).map((inner) => !inner).every(Boolean)) {
								value = '';
							} else {
								// If we don't care about value just need to have not empty any item, value is set to something random.
								value = 'empty';
							}
						} else {
							// If we selected inner item in options use the value of that item.
							value = this.state.getStateElementValue(innerName, formId)[innerValue] === innerValue ? innerValue : '';
						}
						break;
					default:
						// Get element value by name.
						value = this.state.getStateElementValue(innerName, formId);
						break;
				}

				// Do the check based on the operator and set reference data with the correct state.
				output[parent][index] = this.OPERATORS[innerCondition](value, innerValue);
			});
		});
	}

	/**
	 * Do the actual logic of checking conditions for rule - top level item.
	 *
	 * @param {string} fromId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	setFieldsRules(formId, name) {
		// Opulate current ref state.
		let output = this.state.getStateElementConditionalTagsRef(name, formId);

		// Loop all conditional tags.
		this.state.getStateElementConditionalTagsTags(name, formId).forEach((items, parent) => {
			// Loop all inner fields.
			items.forEach((inner, index) => {
				// Placeholder value to chack later.
				let value = '';

				// Get element type.
				const type = this.state.getStateElementType(inner[0], formId);

				// Bailout if type is missing.
				if (!type) {
					return;
				}

				switch (type) {
					case 'checkbox':
						// If check box inner items are missing this applys to parent element not children.
						if (inner[2] === '') {
							// If all inner items are empty and not set this will output value to empty.
							if (Object.values(this.state.getStateElementValue(inner[0], formId)).map((inner) => !inner).every(Boolean)) {
								value = '';
							} else {
								// If we don't care about value just need to have not empty any item, value is set to something random.
								value = 'empty';
							}
						} else {
							// If we selected inner item in options use the value of that item.
							value = this.state.getStateElementValue(inner[0], formId)[inner[2]] === inner[2] ? inner[2] : '';
						}
						break;
					default:
						// Get element value by name.
						value = this.state.getStateElementValue(inner[0], formId);
						break;
				}

				// Do the check based on the operator and set reference data with the correct state.
				output[parent][index] = this.OPERATORS[inner[1]](value, inner[2]);
			});
		});
	}

	/**
	 * Get ignore fields like hidden fields used when sending data to api.
	 *
	 * @param {string} formId Form Id
	 *
	 * @returns {array}
	 */
	getIgnoreFields(formId) {
		const output = [];
		for(const [name] of this.state.getStateElements(formId)) {
			const isHidden = this.state.getStateElementField(name, formId)?.classList?.contains(this.state.getStateSelectorsClassHiddenConditionalTags());

			if (!isHidden) {
				continue;
			}

			output.push(name);
		}

		return output;
	}

	////////////////////////////////////////////////////////////////
	// Events callback
	////////////////////////////////////////////////////////////////
	/**
	 * On init event callback.
	 *
	 * @param {CustomEvent} event Event object.
	 *
	 * @returns {void}
	 */
	onInitEvent = (event) => {
		const { formId } = event.detail;

		// Set fields logic.
		this.initFields(formId);

		// Set forms logic.
		this.initForms(formId);
	};

	////////////////////////////////////////////////////////////////
	// Private methods - not shared to the public window object.
	////////////////////////////////////////////////////////////////

	/**
	 * Set all public methods.
	 * 
	 * @returns {void}
	 */
	publicMethods() {
		setStateWindow();

		if (window[prefix].conditionalTags) {
			return;
		}

		window[prefix].conditionalTags = {
			SHOW: this.SHOW,
			HIDE: this.HIDE,
			OR: this.OR,
			AND: this.AND,
			OPERATORS: this.OPERATORS,

			initOne: (formId) => {
				this.initOne(formId);
			},
			initForms: (formId) => {
				this.initForms(formId);
			},
			initFields: (formId) => {
				this.initFields(formId);
			},
			setField: (formId, name) => {
				this.setField(formId, name);
			},
			setFieldTopLevel: (formId) => {
				this.setFieldTopLevel(formId);
			},
			setFieldInner: (formId) => {
				this.setFieldInner(formId);
			},
			setFieldInnerSelect: (formId) => {
				this.setFieldInnerSelect(formId);
			},
			setFieldsRulesInner: (formId, name) => {
				this.setFieldsRulesInner(formId, name);
			},
			setFieldsRules: (formId, name) => {
				this.setFieldsRules(formId, name);
			},
			getIgnoreFields: (formId) => {
				return this.getIgnoreFields(formId);
			},
			onInitEvent: (event) => {
				this.onInitEvent(event);
			},
		};
	}
}
