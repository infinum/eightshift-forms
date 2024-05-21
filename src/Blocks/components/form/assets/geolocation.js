import { cookies } from '@eightshift/frontend-libs/scripts/helpers';
import { prefix, setStateWindow } from './state-init';

/**
 * Geolocation class.
 */
export class Geolocation {
	constructor(utils) {
		/** @type {import('./utils').Utils} */
		this.utils = utils;
		/** @type {import('./state').State} */
		this.state = this.utils.getState();

		// Set all public methods.
		this.publicMethods();
	}

	////////////////////////////////////////////////////////////////
	// Public methods
	////////////////////////////////////////////////////////////////

	/**
	 * Init one action.
	 *
	 * @returns {void}
	 */
	initOne(formId) {
		// Check if enrichment is used.
		if (!this.state.getStateGeolocationIsUsed()) {
			return;
		}

		// Set select fields based on geolocation.
		this.state.getStateFormElement(formId).addEventListener(
			this.state.getStateEvent('formJsLoaded'),
			this.onSetSelectField
		);
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
		this.state.getStateFormElement(formId)?.removeEventListener(
			this.state.getStateEvent('formJsLoaded'),
			this.onSetSelectField
		);
	}

	////////////////////////////////////////////////////////////////
	// Events callback
	////////////////////////////////////////////////////////////////

	/**
	 * Detect if we have country cookie and set value to the select.
	 *
	 * @param {object} event Event callback.
	 *
	 * @returns {void}
	 */
	onSetSelectField = (event) => {
		const { formId } = event.detail;
		const countryCookie = cookies?.getCookie('esForms-country')?.toLocaleLowerCase();

		if (!countryCookie) {
			return;
		}

		[
			...this.state.getStateElementByTypeField('select', formId),
			...this.state.getStateElementByTypeField('country', formId),
		].forEach((select) => {
			const name = select.name;

			const typeInternal = this.state.getStateElementTypeField(name, formId);
			const custom = this.state.getStateElementCustom(name, formId);

			const selectValue = this.utils.getSelectSelectedValueByCustomData(typeInternal, countryCookie, custom);
			if (selectValue) {
				this.utils.setManualSelectValue(formId, name, selectValue);
			}
		});

		if (!this.state.getStateFormConfigPhoneDisablePicker(formId) && this.state.getStateFormConfigPhoneUseSync(formId)) {
			[...this.state.getStateElementByTypeField('phone', formId)].forEach((phone) => {
				const name = phone.name;

				const typeInternal = this.state.getStateElementTypeField(name, formId);
				const custom = this.state.getStateElementCustom(name, formId);

				const selectValue = this.utils.getSelectSelectedValueByCustomData(typeInternal, countryCookie, custom);
				if (selectValue) {
					this.utils.setManualPhoneValue(
						formId,
						name,
						{
							prefix: selectValue,
							value: this.state.getStateElementValue(name, formId),
						}
					);
				}
			});
		}
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

		if (window[prefix].geolocation) {
			return;
		}

		window[prefix].geolocation = {
			initOne: () => {
				this.initOne();
			},
			removeEvents: (formId) => {
				this.removeEvents(formId);
			},
			onSetSelectField: (event) => {
				this.onSetSelectField(event);
			},
		};
	}
}
