import { prefix, setStateValues, setStateWindow, StateEnum } from './state-init';
import globalManifest from './../../../manifest.json';

/**
 * Main condition tags class.
 */
export class ConditionalTags {
	constructor(utils) {
		/** @type {import('./utils').Utils} */
		this.utils = utils;
		/** @type {import('./state').State} */
		this.state = this.utils.getState();

		// Simplify usage of constants
		this.SHOW = globalManifest.comparatorActions.SHOW;
		this.HIDE = globalManifest.comparatorActions.HIDE;
		this.OR = globalManifest.comparatorLogic.OR;
		this.AND = globalManifest.comparatorLogic.AND;
		this.IS = globalManifest.comparator.IS;
		this.ISN = globalManifest.comparator.ISN;
		this.C = globalManifest.comparator.C;
		this.CN = globalManifest.comparator.CN;

		// Map all conditional logic as a object.
		this.OPERATORS = this.utils.getComparator();

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
		window.addEventListener(this.state.getStateEvent('formJsLoaded'), this.onInitEvent);
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
		// Set all rules for all form fields.
		for (const [name] of this.state.getStateElements(formId)) {
			this.setFieldsRulesAll(formId, name);
		}

		// Set all rules for all non-form fields.
		for (const [name] of this.state.getStateElementsFields(formId)) {
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
		for (const [name] of this.state.getStateElements(formId)) {
			// Get element type.
			const type = this.state.getStateElementTypeField(name, formId);

			// Only select, checkbox and radio fields can have inner items.
			if (type === 'select' || type === 'checkbox' || type === 'radio') {
				// Prepare inner level outputs.
				let innerOutput = {};

				// Select fields can have multiple or single select inner options.
				if (type === 'select') {
					innerOutput = this.state.getStateElementConfig(name, StateEnum.CONFIG_SELECT_USE_MULTIPLE, formId)
						? this.getFieldInnerSelectMultiple(formId, name)
						: this.getFieldInnerSelectSingle(formId, name);
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

		// Loop all non-form fields.
		for (const [name] of this.state.getStateElementsFields(formId)) {
			// Set top level fields state.
			const check = this.getFieldTopLevel(formId, name, true);

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
		const formSelector = this.state.getStateSelector('form', true);
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
			let selectorType = this.state.getStateElementTypeField(fieldName, formId) === 'select' ? selectValueAttr : fieldNameAttr;

			// Loop all inner items.
			innerItems.forEach((inner) => {
				// Push selector for style output.
				output.push(`${formSelector}[${formIdAttr}="${formId}"] [${fieldNameAttr}="${fieldName}"] [${selectorType}="${inner}"]`);
			});
		}

		// Set styles to DOM.
		this.outputStyles(formId, output, stateName);

		this.removeActiveFieldsOnHide(formId, data, stateName);

		// Set state for conditional tags.
		this.state.setState(
			[StateEnum.FORM, stateName],
			{
				...data,
				topFinal: [...new Set(topFinalOutput)],
			},
			formId,
		);
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
	outputStyles(formId, data, stateName) {
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
		const styleOutput = data.length ? `${data.join(',')}{display:${type} !important;}` : '';

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
	 * @param {bool} isNoneFormBlock Is non-Forms block.
	 *
	 * @returns {bool}
	 */
	getFieldTopLevel(formId, name, isNoneFormBlock = false) {
		// Find defaults to know what direction to use.
		const defaultState = !isNoneFormBlock ? this.state.getStateElementConditionalTagsDefaults(name, formId) : this.state.getStateElementFieldConditionalTagsDefaults(name, formId); // eslint-disable-line max-len

		const ref = !isNoneFormBlock ? this.state.getStateElementConditionalTagsRef(name, formId) : this.state.getStateElementFieldConditionalTagsRef(name, formId);

		// Check if conditions are valid or not. This is where the magic happens.
		const isValid = ref?.map((validItem) => validItem.every(Boolean)).some(Boolean);

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
		const isValid = this.state
			.getStateElementConditionalTagsRefInner(name, innerName, formId)
			?.map((validItem) => validItem.every(Boolean))
			.some(Boolean);

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
		[...(this.state.getStateElementCustom(name, formId)?.passedElement?.element?.options ?? [])].forEach((option) => {
			// Find item value.
			const innerName = option.value;

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
		if (this.state.getStateElementTypeField(name, formId) === 'select') {
			// Get choices object.
			const items = this.state.getStateElementCustom(name, formId)?.passedElement?.element?.options;

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

		// Populate current ref state.
		let output = this.state.getStateElementConditionalTagsRefInner(topName, innerName, formId);

		// Loop all conditional tags.
		this.state.getStateElementConditionalTagsTagsInner(topName, innerName, formId).forEach((items, parent) => {
			// Loop all inner fields.
			items.forEach(([innerName, innerCondition, innerValue], index) => {
				// Placeholder value to check later.
				let value = '';

				// Get element type.
				const type = this.state.getStateElementTypeField(innerName, formId);

				// Bailout if type is missing.
				if (!type) {
					return;
				}

				switch (type) {
					case 'checkbox':
						// If check box inner items are missing this applies to parent element not children.
						if (innerValue === '') {
							// If all inner items are empty and not set this will output value to empty.
							if (
								Object.values(this.state.getStateElementValue(innerName, formId))
									.map((inner) => !inner)
									.every(Boolean)
							) {
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
					case 'select':
					case 'country':
						value = this.state.getStateElementValue(innerName, formId);

						if (value.length === 0) {
							value = '';
						}

						if (innerCondition === this.IS) {
							innerCondition = this.C;
						}

						if (innerCondition === this.ISN) {
							innerCondition = this.CN;
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
		// Populate current ref state.
		let output = this.state.getStateElementConditionalTagsRef(name, formId) ?? [];
		let data = this.state.getStateElementConditionalTagsTags(name, formId) ?? [];

		if (data.length === 0) {
			output = this.state.getStateElementFieldConditionalTagsRef(name, formId) ?? [];
			data = this.state.getStateElementFieldConditionalTagsTags(name, formId) ?? [];
		}

		// Loop all conditional tags.
		data.forEach((items, parent) => {
			// Loop all inner fields.
			items.forEach((inner, index) => {
				// Placeholder value to check later.
				let value = '';

				// Get element type.
				const type = this.state.getStateElementTypeField(inner[0], formId);

				// Bailout if type is missing.
				if (!type) {
					return;
				}

				switch (type) {
					case 'checkbox':
						// If check box inner items are missing this applies to parent element not children.
						if (inner[2] === '') {
							// If all inner items are empty and not set this will output value to empty.
							if (
								Object.values(this.state.getStateElementValue(inner[0], formId))
									.map((inner) => !inner)
									.every(Boolean)
							) {
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
					case 'select':
					case 'country':
						value = this.state.getStateElementValue(inner[0], formId);

						if (value.length === 0) {
							value = '';
						}

						if (inner[1] === this.IS) {
							inner[1] = this.C;
						}

						if (inner[1] === this.ISN) {
							inner[1] = this.CN;
						}
						break;
					case 'phone':
						value = this.utils.getPhoneCombinedValue(formId, inner[0]);
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

	/**
	 * Remove active fields on hide.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	removeActiveFieldsOnHide(formId, data, stateName) {
		if (stateName === StateEnum.CONDITIONAL_TAGS_STATE_FORM_SHOW) {
			return;
		}

		Object.entries(data.inner).forEach(([key, items]) => {
			const type = this.state.getStateElementTypeField(key, formId);

			if (!items.length) {
				return;
			}

			if (!data.top.includes(key)) {
				switch (type) {
					case 'select':
						this.removeManualSelectActiveValue(formId, key, items);
						break;
					case 'checkbox':
						this.removeManualCheckboxActiveValue(formId, key, items);
						break;
					case 'radio':
						this.removeManualRadioActiveValue(formId, key, items);
						break;
				}
			}
		});

		[...data.top, ...data.innerParents].forEach((name) => {
			this.removeActiveFieldsOnHideItem(formId, name);
		});
	}

	/**
	 * Remove active value from radio field.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {array} value Value to remove.
	 *
	 * @returns {void}
	 */
	removeManualRadioActiveValue(formId, name, value) {
		if (!Array.isArray(value)) {
			return;
		}

		if (!(name in this.state.getStateElementsObject(formId))) {
			return;
		}

		let newValue = this.state.getStateElementValue(name, formId);
		const inner = this.state.getStateElementItems(name, formId);

		if (inner) {
			Object.values(inner).forEach((item) => {
				if (value.includes(item.value)) {
					item.input.checked = false;
					newValue = '';
				}
			});
		}

		setStateValues(name, newValue, formId);
		this.utils.setMandatoryFieldState(formId, name, newValue, false);
	}

	/**
	 * Remove active value from select field.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {array} value Value to remove.
	 *
	 * @returns {void}
	 */
	removeManualSelectActiveValue(formId, name, value) {
		if (!Array.isArray(value)) {
			return;
		}

		if (!(name in this.state.getStateElementsObject(formId))) {
			return;
		}

		const custom = this.state.getStateElementCustom(name, formId);
		const currentValue = this.state.getStateElementValue(name, formId);

		if (custom) {
			custom.removeActiveItemsByValue(value);
		}

		const newValue = currentValue.filter((item) => !value.includes(item));

		setStateValues(name, newValue, formId);
		this.utils.setMandatoryFieldState(formId, name, newValue, false);
	}

	/**
	 * Remove active value from checkbox field.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 * @param {array} value Value to remove.
	 *
	 * @returns {void}
	 */
	removeManualCheckboxActiveValue(formId, name, value) {
		if (!Array.isArray(value)) {
			return;
		}

		if (!(name in this.state.getStateElementsObject(formId))) {
			return;
		}

		let newValue = this.state.getStateElementValue(name, formId);
		const inner = this.state.getStateElementItems(name, formId);

		if (inner) {
			Object.values(inner).forEach((item) => {
				if (value.includes(item.value)) {
					item.input.checked = false;
					newValue[item.value] = '';
				}
			});
		}

		setStateValues(name, newValue, formId);
		this.utils.setMandatoryFieldState(formId, name, newValue, false);
	}

	/**
	 * Remove active fields on hide item.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} name Field name.
	 *
	 * @returns {void}
	 */
	removeActiveFieldsOnHideItem(formId, name) {
		switch (this.state.getStateElementTypeField(name, formId)) {
			case 'range':
				this.utils.setManualRangeValue(formId, name, '', false);
				break;
			case 'rating':
				this.utils.setManualRatingValue(formId, name, '', false);
				break;
			case 'radio':
				this.utils.setManualRadioValue(formId, name, '', false);
				break;
			case 'checkbox':
				this.utils.setManualCheckboxValue(formId, name, {}, false);
				break;
			case 'select':
				this.utils.setManualSelectValue(formId, name, [], false);
				break;
			case 'country':
				this.utils.setManualCountryValue(formId, name, [], false);
				break;
			case 'phone':
				this.utils.setManualPhoneValue(formId, name, {}, false);
				break;
			case 'date':
			case 'dateTime':
				this.utils.setManualDateValue(formId, name, '', false);
				break;
			default:
				this.utils.setManualInputValue(formId, name, '', false);
				break;
		}
	}

	////////////////////////////////////////////////////////////////
	// Other
	////////////////////////////////////////////////////////////////

	/**
	 * Remove all event listeners from elements.
	 *
	 * @returns {void}
	 */
	removeEvents() {
		window?.removeEventListener(this.state.getStateEvent('formJsLoaded'), this.onInitEvent);
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
			setFieldsRulesAll: (formId, fieldName) => {
				this.setFieldsRulesAll(formId, fieldName);
			},
			setStyles: (formId, data, stateName) => {
				this.setStyles(formId, data, stateName);
			},
			outputStyles: (formId, data, stateName) => {
				this.outputStyles(formId, data, stateName);
			},
			getFieldTopLevel: (formId, name, isNoneFormBlock = false) => {
				return this.getFieldTopLevel(formId, name, isNoneFormBlock);
			},
			getFieldInner: (formId, name) => {
				return this.getFieldInner(formId, name);
			},
			getFieldInnerByName: (formId, name, innerName) => {
				return this.getFieldInnerByName(formId, name, innerName);
			},
			getFieldInnerSelect: (formId, name) => {
				return this.getFieldInnerSelect(formId, name);
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
			removeActiveFieldsOnHide: (formId, data, stateName) => {
				this.removeActiveFieldsOnHide(formId, data, stateName);
			},
			removeManualRadioActiveValue: (formId, name, value) => {
				this.removeManualRadioActiveValue(formId, name, value);
			},
			removeManualSelectActiveValue: (formId, data, stateName) => {
				this.removeManualSelectActiveValue(formId, data, stateName);
			},
			removeManualCheckboxActiveValue: (formId, data, stateName) => {
				this.removeManualCheckboxActiveValue(formId, data, stateName);
			},
			removeActiveFieldsOnHideItem: (formId, name) => {
				this.removeActiveFieldsOnHideItem(formId, name);
			},
			removeEvents: (formId) => {
				this.removeEvents(formId);
			},
			onInitEvent: (event) => {
				this.onInitEvent(event);
			},
		};
	}
}
