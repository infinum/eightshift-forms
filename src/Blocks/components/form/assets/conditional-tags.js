
import { State } from './state';
import {
	CONDITIONAL_TAGS_OPERATORS,
	CONDITIONAL_TAGS_ACTIONS,
	CONDITIONAL_TAGS_LOGIC,
} from '../../conditional-tags/assets/utils';
import { Utils } from './utilities';
import {
	prefix,
	setStateWindow,
	StateEnum,
} from './state/init';

/**
 * Main conditon tags class.
 */
export class ConditionalTags {
	constructor() {
		this.state = new State();
		this.utils = new Utils();

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

		console.log(tags);

		// Loop tags.
		tags.forEach(([tagName, tagVisibility, tagInner]) => {
			const field = this.state.getStateElementField(tagName, formId);

			// Bailout if field doesn't exist.
			if (!field) {
				return;
			}

			if (tagVisibility === this.HIDE) {
				if (!tagInner) {
					outputHide.top.push(tagName);
				} else {
					if (outputHide?.inner?.[tagName] === undefined) {
						outputHide.inner[tagName] = [];
					}

					outputHide.inner[tagName].push(tagInner);
				}
			} else {
				if (!tagInner) {
					outputShow.top.push(tagName);
				} else {
					if (outputShow?.inner?.[tagName] === undefined) {
						outputShow.inner[tagName] = [];
					}

					outputShow.inner[tagName].push(tagInner);
				}
			}
		});

		this.state.setState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_STATE_FORM_HIDE], outputHide, formId);

		this.setStyles(formId, outputHide, StateEnum.CONDITIONAL_TAGS_STATE_FORM_HIDE);

		this.state.setState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_STATE_FORM_SHOW], outputShow, formId);

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
		for(const [name] of this.state.getStateElements(formId)) {
			this.setFieldsRulesAll(formId, name);
		}

		this.setFields(formId);
	}

	/**
	 * Set field conditional logic.
	 *
	 * @param {string} fieldName Field name.
	 * @param {string} fromId Form Id.
	 *
	 * @returns {void}
	 */
	setField(formId, fieldName) {
		this.setFieldsRulesAll(formId, fieldName);
		this.setFields(formId);
	}

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

	setStyles(formId, data, stateName) {
		let output = [];

		const fieldNameAttr = this.state.getStateAttribute('fieldName');
		const formIdAttr = this.state.getStateAttribute('formId');
		const formSelector = this.state.getStateSelectorsForm();
		const selectValueAttr = this.state.getStateAttribute('selectValue');

		const topFinalOutput = [...data.topFinal];

		data?.top.forEach((name) => {
			output.push(`${formSelector}[${formIdAttr}="${formId}"] [${fieldNameAttr}="${name}"]`);
			topFinalOutput.push(name);
		});

		data?.innerParents.forEach((name) => {
			output.push(`${formSelector}[${formIdAttr}="${formId}"] [${fieldNameAttr}="${name}"]`);
			if (!data?.top.includes(name)) {
				topFinalOutput.push(name);
			}
		});

		for (const [fieldName, innerItems] of Object.entries(data?.inner ?? {})) {
			let selectorType = this.state.getStateElementTypeInternal(fieldName, formId) === 'select' ? selectValueAttr : fieldNameAttr;

			innerItems.forEach((inner) => {
				output.push(`${formSelector}[${formIdAttr}="${formId}"] [${fieldNameAttr}="${fieldName}"] [${selectorType}="${inner}"]`);
			});
		}

		// Detect if style tag is present in dom.
		this.ouputStyles(formId, output, stateName);

		this.state.setState([StateEnum.FORM, stateName], {
			...data,
			topFinal: [...new Set(topFinalOutput)],
		}, formId);
	}

	ouputStyles(formId, data, stateName) {
		const form = this.state.getStateFormElement(formId);

		let selector = '';
		let type = '';

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

		const styleSelector = `style-${formId}-${selector}`;

		const styleTag = document.getElementById(`${styleSelector}`);

		const styleOutput = data.length ? `${data.join(',')}{display:${type} !important;}`: '';

		if (!styleTag) {
			form.insertAdjacentHTML('beforeend', `<style id="${styleSelector}">${styleOutput}</style>`);
		} else {
			styleTag.innerHTML = styleOutput;
		}
	}

	setFields(formId) {
		const output = {
			top: [],
			topFinal: [],
			innerParents: [],
			inner: {},
		};

		for(const [name] of this.state.getStateElements(formId)) {
			const type = this.state.getStateElementType(name, formId);

			if (type === 'select' || type === 'checkbox' || type === 'radio') {
				let innerOutput = {};

				if (type === 'select') {
					innerOutput = this.state.getStateElementConfig(name, StateEnum.CONFIG_SELECT_USE_MULTIPLE, formId) ? this.setFieldInnerSelectMultiple(formId, name) : this.setFieldInnerSelectSingle(formId, name);
				} else {
					// Set inner level fields state.
					innerOutput = this.setFieldInner(formId, name);
				}

				if (innerOutput.innerParents) {
					output.innerParents.push(name);
				}

				if (innerOutput.inner) {
					output.inner[name] = innerOutput.inner;
				}
			}

			// Set top level fields state.
			const check = this.setFieldTopLevel(formId, name);
			if (check) {
				output.top.push(name);
			}
		}

		this.state.setState([StateEnum.FORM, StateEnum.CONDITIONAL_TAGS_STATE_CT], output, formId);

		this.setStyles(formId, output, StateEnum.CONDITIONAL_TAGS_STATE_CT);
	}

	/**
	 * Set field top level state.
	 *
	 * @param {string} fromId Form Id.
	 *
	 * @returns {void}
	 */
	setFieldTopLevel(formId, name) {
		// Find defaults to know what direction to use.
		const defaultState = this.state.getStateElementConditionalTagsDefaults(name, formId);

		// Check if conditions are valid or not. This is where the magic happens.
		const isValid = this.state.getStateElementConditionalTagsRef(name, formId)?.map((validItem) => validItem.every(Boolean)).some(Boolean);

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
	 * Set field inner level state.
	 *
	 * @param {string} fromId Form Id.
	 *
	 * @returns {void}
	 */
	setFieldInner(formId, name) {
		const output = {
			innerParents: false,
			inner: [],
		};

		// Find inner items.
		const items = Object.keys(this.state.getStateElementItems(name, formId) ?? []);

		// Loop inner items.
		items.forEach((innerName) => {
			const inner = this.setFieldInnerByName(formId, name, innerName);
			if (inner) {
				output.inner.push(inner);
			}
		});

		if (this.getToggleParent(formId, name, output.inner)) {
			output.innerParents = true;
		}

		return output;
	}

	setFieldInnerByName(formId, name, innerName) {
		// Check if conditions are valid or not. This is where the magic happens.
		const isValid = this.state.getStateElementConditionalTagsRefInner(name, innerName, formId).map((validItem) => validItem.every(Boolean)).some(Boolean);

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
	 * Set field inner level state - select single only.
	 *
	 * @param {string} fromId Form Id.
	 * @param {string} name Field Name.
	 *
	 * @returns {void}
	 */
	setFieldInnerSelectSingle(formId, name) {
		const output = {
			innerParents: false,
			inner: [],
		};

		// Get dropdown items.
		[...this.state.getStateElementCustom(name, formId)?.choiceList?.element?.children ?? []].forEach((option) => {
			// Find item value.
			const innerName = option.getAttribute(this.state.getStateAttribute('selectValue'));

			// Bailout if placeholder.
			if (!innerName) {
				return;
			}

			const inner = this.setFieldInnerByName(formId, name, innerName);
			if (inner) {
				output.inner.push(inner);
			}
		});

		if (this.getToggleParent(formId, name, output.inner)) {
			output.innerParents = true;
		}

		return output;
	}

	getToggleParent(formId, name, currentState) {
		const type = this.state.getStateElementType(name, formId);

		if (type === 'select') {
			const items = this.state.getStateElementCustom(name, formId)?.config?.choices;
			let totalItems = items?.length;

			if (items?.[0]?.placeholder) {
				totalItems--;
			}

			if (totalItems === currentState.length) {
				return true;
			}
		} else {
			const items = Object.keys(this.state.getStateElementItems(name, formId) ?? []);

			if (items?.length > 0 && items?.length === currentState.length) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Set field inner level state - select multiple only.
	 *
	 * @param {string} fromId Form Id.
	 * @param {string} name Field Name.
	 *
	 * @returns {void}
	 */
	setFieldInnerSelectMultiple(formId, name) {
		// Get choices object.
		const custom = this.state.getStateElementCustom(name, formId);

		// Get active items.
		custom?.getValue(true).forEach((innerName) => {
			if (this.setFieldInnerByName(formId, name, innerName)) {
				custom?.removeActiveItemsByValue(innerName);
			}
		});

		return this.setFieldInnerSelectSingle(formId, name);
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
		return this.state.getStateFormConditionalTagsStateHide(formId)?.topFinal ?? [];
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
			setFieldTopLevel: (formId) => {
				this.setFieldTopLevel(formId);
			},
			setFieldInner: (formId) => {
				this.setFieldInner(formId);
			},
			setFieldInnerSelectSingle: (formId) => {
				this.setFieldInnerSelectSingle(formId);
			},
			setFieldInnerSelectMultiple: (formId) => {
				this.setFieldInnerSelectMultiple(formId);
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
