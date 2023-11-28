import {
	CONDITIONAL_TAGS_OPERATORS,
	CONDITIONAL_TAGS_ACTIONS,
	CONDITIONAL_TAGS_LOGIC,
} from '../../conditional-tags/assets/utils';
import {
	prefix,
	setStateWindow,
	StateEnum,
} from './state/init';

/**
 * Main conditon tags class.
 */
export class ConditionalTags {
	constructor(utils) {
		/** @type {import('./utils').Utils} */
		this.utils = utils;
		/** @type {import('./state').State} */
		this.state = this.utils.getState();

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
		// Bailout if admin.
		if (this.state.getStateConfigIsAdmin(formId)) {
			return;
		}

		// Listen to every field element change.
		this.state.getStateFormElement(formId).addEventListener(
			this.state.getStateEventsFormJsLoaded(),
			this.onInitEvent
		);
	}

	/**
	 * Init forms visibility conditional logic.
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

		// Prepare outputs.
		const outputHide = {
			top: [],
			topFinal: [],
			innerParents: [],
			inner: {},
		};
		const outputShow = {
			top: [],
			topFinal: [],
			innerParents: [],
			inner: {},
		};

		// Loop all conditional tags.
		tags.forEach(([tagName, tagVisibility, tagInner]) => {
			// Check if tag is hide state.
			if (tagVisibility === this.HIDE) {
				if (!tagInner) {
					// Push to top state if no inner items.
					outputHide.top.push(tagName);
				} else {
					// Create a new inner state if not existing.
					if (outputHide?.inner?.[tagName] === undefined) {
						outputHide.inner[tagName] = [];
					}

					// Push to inner state if existing.
					outputHide.inner[tagName].push(tagInner);
				}
			} else {
				// Check if tag is visible state.
				if (!tagInner) {
					// Push to top state if no inner items.
					outputShow.top.push(tagName);
				} else {
					// Create a new inner state if not existing.
					if (outputShow?.inner?.[tagName] === undefined) {
						outputShow.inner[tagName] = [];
					}

					// Push to inner state if existing.
					outputShow.inner[tagName].push(tagInner);
				}
			}
		});

		// Set state for form hide.
		this.state.setState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_STATE_FORM_HIDE], outputHide, formId);

		// Set styles for form hide.
		this.setStyles(formId, outputHide, StateEnum.CONDITIONAL_TAGS_STATE_FORM_HIDE);

		// Set state for form show.
		this.state.setState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_STATE_FORM_SHOW], outputShow, formId);

		// Set styles for form show.
		this.setStyles(formId, outputShow, StateEnum.CONDITIONAL_TAGS_STATE_FORM_SHOW);
	}

	/**
	 * Init fields conditional logic.
	 *
	 * @param {string} fromId Form Id.
	 *
	 * @returns {void}
	 */
	initFields(formId) {
		// Set all rules for all fields.
		for(const [name] of this.state.getStateElements(formId)) {
			this.setFieldsRulesAll(formId, name);
		}

		// Set fields states.
		this.setFields(formId);
	}

	/**
	 * Set field conditional logic on field change.
	 *
	 * @param {string} fromId Form Id.
	 * @param {string} fieldName Field name.
	 *
	 * @returns {void}
	 */
	setField(formId, fieldName) {
		// Set all rules for all fields.
		this.setFieldsRulesAll(formId, fieldName);

		// Set fields states.
		this.setFields(formId);
	}

	/**
	 * Set all fields state.
	 *
	 * @param {string} fromId Form Id.
	 *
	 * @returns {void}
	 */
	setFields(formId) {
		// Prepare outputs.
		const output = {
			top: [],
			topFinal: [],
			innerParents: [],
			inner: {},
		};

		// Loop all fields.
		for(const [name] of this.state.getStateElements(formId)) {
			// Get element type.
			const type = this.state.getStateElementTypeInternal(name, formId);

			// Only select, checkbox and radio fields can have inner items.
			if (
				type === this.state.getStateIntType('select') ||
				type === this.state.getStateIntType('checkbox') ||
				type === this.state.getStateIntType('radio')
			) {
				// Prepare inner level outputs.
				let innerOutput = {};

				// Select fields can have multiple or single select inner options.
				if (type === this.state.getStateIntType('select')) {
					innerOutput = this.state.getStateElementConfig(name, StateEnum.CONFIG_SELECT_USE_MULTIPLE, formId) ? this.getFieldInnerSelectMultiple(formId, name) : this.getFieldInnerSelectSingle(formId, name);
				} else {
					// Checkbox and radio inner fields.
					innerOutput = this.getFieldInner(formId, name);
				}

				// Set inner Parents fields state.
				if (innerOutput.innerParents) {
					output.innerParents.push(name);
				}

				// Set inner fields state.
				if (innerOutput.inner) {
					output.inner[name] = innerOutput.inner;
				}
			}

			// Set top level fields state.
			const check = this.getFieldTopLevel(formId, name);
			if (check) {
				output.top.push(name);
			}
		}

		// Set state for conditional tags.
		this.state.setState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_STATE_CT], output, formId);

		// Set styles for conditional tags.
		this.setStyles(formId, output, StateEnum.CONDITIONAL_TAGS_STATE_CT);
	}

	/**
	 * Set all rules for all fields.
	 *
	 * @param {string} fromId Form Id.
	 * @param {string} fieldName Field name.
	 *
	 * @returns {void}
	 */
	setFieldsRulesAll(formId, fieldName) {
		// Check and set top level events and set rules.
		this.state.getStateFormConditionalTagsEvents(formId)?.[fieldName]?.forEach((eventName) => {
			this.setFieldsRules(formId, eventName);
		});

		// Check and set inner level events and set rules.
		this.state.getStateFormConditionalTagsInnerEvents(formId)?.[fieldName]?.forEach((eventName) => {
			this.setFieldsRulesInner(formId, eventName);
		});
	}

	/**
	 * Set styles for conditional tags.
	 *
	 * @param {string} fromId Form Id.
	 * @param {object} data Data to set.
	 * @param {string} stateName State name to set.
	 *
	 * @returns {void}
	 */
	setStyles(formId, data, stateName) {
		let output = [];

		// Get attributes.
		const fieldNameAttr = this.state.getStateAttribute('fieldName');
		const formIdAttr = this.state.getStateAttribute('formId');
		const formSelector = this.state.getStateSelectorsForm();
		const selectValueAttr = this.state.getStateAttribute('selectValue');

		// New array for top final so we don't mutate the original object.
		const topFinalOutput = [...data.topFinal];

		// Loop all top level items.
		data?.top.forEach((name) => {
			// Push selector for style output.
			output.push(`${formSelector}[${formIdAttr}="${formId}"] [${fieldNameAttr}="${name}"]`);

			// Push to top final item.
			topFinalOutput.push(name);
		});

		// Loop all inner parents items.
		data?.innerParents.forEach((name) => {
			// Push selector for style output.
			output.push(`${formSelector}[${formIdAttr}="${formId}"] [${fieldNameAttr}="${name}"]`);

			// If top final item is not already set push it.
			if (!data?.top.includes(name)) {
				// Push to top final item .
				topFinalOutput.push(name);
			}
		});

		// Loop all inner items.
		for (const [fieldName, innerItems] of Object.entries(data?.inner ?? {})) {
			// Get correct selector type.
			let selectorType = this.state.getStateElementTypeInternal(fieldName, formId) === this.state.getStateIntType('select') ? selectValueAttr : fieldNameAttr;

			// Loop all inner items.
			innerItems.forEach((inner) => {
				// Push selector for style output.
				output.push(`${formSelector}[${formIdAttr}="${formId}"] [${fieldNameAttr}="${fieldName}"] [${selectorType}="${inner}"]`);
			});
		}

		// Set styles to DOM.
		this.ouputStyles(formId, output, stateName);

		// Set state for conditional tags.
		this.state.setState([StateEnum.FORM, stateName], {
			...data,
			topFinal: [...new Set(topFinalOutput)],
		}, formId);
	}

	/**
	 * Output styles to DOM.
	 *
	 * @param {string} fromId Form Id.
	 * @param {object} data Data to set.
	 * @param {string} stateName State name to set.
	 *
	 * @returns {void}
	 */
	ouputStyles(formId, data, stateName) {
		// Get form element.
		const form = this.state.getStateFormElement(formId);

		let selector = '';
		let type = '';

		// Set correct selector and type based on state name.
		switch (stateName) {
			case StateEnum.CONDITIONAL_TAGS_STATE_FORM_HIDE:
				selector = 'forms-hide';
				type = 'none';
				break;
			case StateEnum.CONDITIONAL_TAGS_STATE_FORM_SHOW:
				selector = 'forms-show';
				type = 'initial';
				break;
			case StateEnum.CONDITIONAL_TAGS_STATE_CT:
				selector = 'ct-hide';
				type = 'none';
				break;
		}

		// Set style selector.
		const styleSelector = `style-${formId}-${selector}`;

		// Get style tag that needs settings.
		const styleTag = document.getElementById(`${styleSelector}`);

		// Set style output.
		const styleOutput = data.length ? `${data.join(',')}{display:${type} !important;}`: '';

		// Set style to DOM.
		if (!styleTag) {
			// Insert style to DOM.
			form.insertAdjacentHTML('beforeend', `<style id="${styleSelector}">${styleOutput}</style>`);
		} else {
			// Update style in DOM.
			styleTag.innerHTML = styleOutput;
		}
	}

	/**
	 * Get field top level state.
	 *
	 * @param {string} fromId Form Id.
	 * @param {string} name Field Name.
	 *
	 * @returns {bool}
	 */
	getFieldTopLevel(formId, name) {
		// Find defaults to know what direction to use.
		const defaultState = this.state.getStateElementConditionalTagsDefaults(name, formId);

		// Check if conditions are valid or not. This is where the magic happens.
		const isValid = this.state.getStateElementConditionalTagsRef(name, formId)?.map((validItem) => validItem.every(Boolean)).some(Boolean);

		// In case if option is visible by default.
		if (isValid && defaultState === this.SHOW) {
			return true;
		}

		// In case if option is hidden by default the logic is flipped.
		if (!isValid && defaultState === this.HIDE) {
			return true;
		}

		return false;
	}

	/**
	 * Get field inner level state.
	 *
	 * @param {string} fromId Form Id.
	 * @param {string} name Field Name.
	 *
	 * @returns {object}
	 */
	getFieldInner(formId, name) {
		// Prepare outputs.
		const output = {
			innerParents: false,
			inner: [],
		};

		// Find inner items.
		const items = Object.keys(this.state.getStateElementItems(name, formId) ?? []);

		// Loop inner items.
		items.forEach((innerName) => {
			const inner = this.getFieldInnerByName(formId, name, innerName);

			if (inner) {
				// Push to inner state if existing.
				output.inner.push(inner);
			}
		});

		// Set inner parents fields state if every item in inner is hidden.
		if (this.getToggleParent(formId, name, output.inner)) {
			output.innerParents = true;
		}

		return output;
	}

	/**
	 * Get field inner items by name.
	 *
	 * @param {string} fromId Form Id.
	 * @param {string} name Field Name.
	 * @param {string} innerName Inner Field Name.
	 *
	 * @returns {bool|string}
	 */
	getFieldInnerByName(formId, name, innerName) {
		// Check if conditions are valid or not. This is where the magic happens.
		const isValid = this.state.getStateElementConditionalTagsRefInner(name, innerName, formId)?.map((validItem) => validItem.every(Boolean)).some(Boolean);

		// Find defaults to know what direction to use.
		const defaultState = this.state.getStateElementConditionalTagsDefaultsInner(name, innerName, formId);

		// In case if option is visible by default.
		if (isValid && defaultState === this.SHOW) {
			return innerName;
		}

		// In case if option is hidden by default the logic is flipped.
		if (!isValid && defaultState === this.HIDE) {
			return innerName;
		}

		return false;
	}

	/**
	 * Get field inner items - select.
	 *
	 * @param {string} fromId Form Id.
	 * @param {string} name Field Name.
	 *
	 * @returns {object}
	 */
	getFieldInnerSelect(formId, name) {
		// Prepare outputs.
		const output = {
			innerParents: false,
			inner: [],
		};

		// Loop all options.
		[...this.state.getStateElementCustom(name, formId)?.choiceList?.element?.children ?? []].forEach((option) => {
			// Find item value.
			const innerName = option.getAttribute(this.state.getStateAttribute('selectValue'));

			// Bailout if placeholder.
			if (!innerName) {
				return;
			}

			const inner = this.getFieldInnerByName(formId, name, innerName);
			if (inner) {
				// Push to inner state if existing.
				output.inner.push(inner);
			}
		});

		// Set inner parents fields state if every item in inner is hidden.
		if (this.getToggleParent(formId, name, output.inner)) {
			output.innerParents = true;
		}

		return output;
	}

	/**
	 * Get field inner items - select single only.
	 *
	 * @param {string} fromId Form Id.
	 * @param {string} name Field Name.
	 *
	 * @returns {object}
	 */
	getFieldInnerSelectSingle(formId, name) {

		// Get choices object.
		const custom = this.state.getStateElementCustom(name, formId);

		// Get active items.
		const innerName = custom?.getValue(true);

		// Remove active items by name.
		if (this.getFieldInnerByName(formId, name, innerName)) {
			custom?.removeActiveItemsByValue(innerName);
			custom?.setChoiceByValue('');
		}

		// Set inner items.
		return this.getFieldInnerSelect(formId, name);
	}

	/**
	 * Get field inner items - select multiple only.
	 *
	 * @param {string} fromId Form Id.
	 * @param {string} name Field Name.
	 *
	 * @returns {object}
	 */
	getFieldInnerSelectMultiple(formId, name) {
		// Get choices object.
		const custom = this.state.getStateElementCustom(name, formId);

		// Get active items.
		custom?.getValue(true).forEach((innerName) => {
			// Remove active items by name.
			if (this.getFieldInnerByName(formId, name, innerName)) {
				custom?.removeActiveItemsByValue(innerName);
			}
		});

		// Set inner items.
		return this.getFieldInnerSelect(formId, name);
	}

	/**
	 * Get toggle parent state.
	 *
	 * @param {string} fromId Form Id.
	 * @param {string} name Field Name.
	 * @param {string} currentState Current state name.
	 *
	 * @returns {bool}
	 */
	getToggleParent(formId, name, currentState) {
		if (this.state.getStateElementType(name, formId) === 'select') {
			// Get choices object.
			const items = this.state.getStateElementCustom(name, formId)?.config?.choices;

			// Get total items.
			let totalItems = items?.length - 1;

			// All items are hidden so we need to hide the parent also.
			if (totalItems === currentState.length) {
				return true;
			}
		} else {
			// Get checkbox/radio items.
			const items = Object.keys(this.state.getStateElementItems(name, formId) ?? []);

			// All items are hidden so we need to hide the parent also.
			if (items?.length > 0 && items?.length === currentState.length) {
				return true;
			}
		}

		return false;
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
	 * @param {string} formId Form Id.
	 *
	 * @returns {array}
	 */
	getIgnoreFields(formId) {
		const ct = this.state.getStateFormConditionalTagsStateCt(formId)?.topFinal ?? [];
		const form = this.state.getStateFormConditionalTagsStateHideForms(formId)?.topFinal ?? [];

		return [...new Set([...ct, ...form])];
	}

	////////////////////////////////////////////////////////////////
	// Other
	////////////////////////////////////////////////////////////////

	/**
	 * Remove all event listeners from elements.
	 * 
	 * @returns {vodi}
	 */
	removeEvents(formId) {
		this.state.getStateFormElement(formId).removeEventListener(
			this.state.getStateEventsFormJsLoaded(),
			this.onInitEvent
		);
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

		// Set forms logic.
		this.initForms(formId);

		// Set fields logic.
		this.initFields(formId);
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
			setFields: (formId) => {
				this.setFields(formId);
			},
			setFieldsRulesAll: (formId, name) => {
				this.setFieldsRulesAll(formId, name);
			},
			setStyles: (formId, data, stateName) => {
				this.setStyles(formId, data, stateName);
			},
			ouputStyles: (formId, data, stateName) => {
				this.ouputStyles(formId, data, stateName);
			},
			getFieldTopLevel: (formId, name) => {
				return this.getFieldTopLevel(formId, name);
			},
			getFieldInner: (formId, name) => {
				return this.getFieldInner(formId, name);
			},
			getFieldInnerByName: (formId, name, innerName) => {
				return this.getFieldInnerByName(formId, name, innerName);
			},
			getFieldInnerSelectSingle: (formId, name) => {
				return this.getFieldInnerSelectSingle(formId, name);
			},
			getFieldInnerSelectMultiple: (formId, name) => {
				return this.getFieldInnerSelectMultiple(formId, name);
			},
			getToggleParent: (formId, name, currentState) => {
				return this.getToggleParent(formId, name, currentState);
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
			removeEvents: () => {
				this.removeEvents();
			},
			onInitEvent: (event) => {
				this.onInitEvent(event);
			},
		};
	}
}
