import { cookies } from '@eightshift/frontend-libs/scripts/helpers';
import { prefix, setStateWindow } from './state-init';

/**
 * Enrichment class.
 */
export class Enrichment {
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
	 * Init all actions.
	 *
	 * @returns {void}
	 */
	init() {
		// Check if enrichment is used.
		if (!this.state.getStateEnrichmentIsUsed()) {
			return;
		}

		// Set local storage data for enrichment.
		this.setLocalStorageEnrichment();
	}

	/**
	 * Set localStorage value for enrichment.
	 *
	 * @returns {void}
	 */
	setLocalStorageEnrichment() {
		// Check if enrichment is used.
		if (!this.state.getStateEnrichmentIsUsed() || !this.state.getStateEnrichmentIsLocalStorageUsed()) {
			return;
		}

		const allowedTags = this.state.getStateEnrichmentAllowed();

		// Missing data from backend, bailout.
		if (!allowedTags) {
			return;
		}

		// Get storage from backend this is considered new by the page request.
		const newStorage = {
			...this.getUrlAllowedParams(allowedTags),
			...this.getCookiesAllowedParams(allowedTags)
		};

		this.setLocalStorage(newStorage, this.state.getStateEnrichmentStorageName());
	}

	/**
	 * Prefill localStorage value for every field.
	 *
	 * @returns {void}
	 */
	setLocalStorageFormPrefill(formId) {
		// Check if enrichment is used.
		if (!this.state.getStateEnrichmentIsUsed() || !this.state.getStateEnrichmentIsPrefillUsed()) {
			return;
		}

		this.state.getStateFormElement(formId).addEventListener(
			this.state.getStateEvent('formJsLoaded'),
			this.onLocalstoragePrefillEvent
		);
	}

	/**
	 * Prefill url params value for every field.
	 *
	 * @returns {void}
	 */
	setUrlParamsFormPrefill(formId) {
		// Check if enrichment is used.
		if (!this.state.getStateEnrichmentIsUsed() || !this.state.getStateEnrichmentIsPrefillUrlUsed()) {
			return;
		}

		this.state.getStateFormElement(formId).addEventListener(
			this.state.getStateEvent('formJsLoaded'),
			this.onUrlParamsPrefillEvent
		);
	}

	/**
	 * Set localStorage value for every field.
	 *
	 * @returns {void}
	 */
	setLocalStorageFormPrefillItem(formId, name) {
		// Check if enrichment is used.
		if (!this.state.getStateEnrichmentIsUsed() || !this.state.getStateEnrichmentIsPrefillUsed() || !this.state.getStateEnrichmentIsLocalStorageUsed()) {
			return;
		}

		let valueData = this.state.getStateElementValue(name, formId);

		const newStorage = {
			[name]: typeof valueData === 'undefined' ? '' : valueData,
		};

		this.setLocalStorage(
			newStorage,
			this.state.getStateEnrichmentFormPrefillStorageName(formId),
			this.state.getStateEnrichmentExpirationPrefill()
		);
	}

	/**
	 * Set localStorage value.
	 *
	 * @returns {void}
	 */
	setLocalStorage(newStorage, storageName, expiration = this.state.getStateEnrichmentExpiration()) {
		// Check if enrichment is used.
		if (!this.state.getStateEnrichmentIsUsed() || !this.state.getStateEnrichmentIsLocalStorageUsed()) {
			return;
		}

		if (!localStorage || !newStorage) {
			return;
		}

		// Add current timestamp to new storage.
		newStorage.timestamp = Date.now();

		// Create new storage if this is the first visit or it was expired.
		if (this.getLocalStorage(storageName) === null) {
			newStorage.timestamp = newStorage.timestamp.toString();

			localStorage?.setItem(
				storageName,
				JSON.stringify(newStorage)
			);

			return;
		}

		// Store in a new variable for later usage.
		const newStorageFinal = {...newStorage};
		delete newStorageFinal.timestamp;

		// Current storage is got from localStorage.
		const currentStorage = JSON.parse(this.getLocalStorage(storageName));

		// Store in a new variable for later usage.
		const currentStorageFinal = {...currentStorage};
		delete currentStorageFinal.timestamp;

		currentStorage.timestamp = parseInt(currentStorage?.timestamp, 10);

		// If storage exists check if it is expired.
		if (this.getLocalStorage(storageName) !== null) {
			// Update expiration date by number of days from the current
			let expirationDate = new Date(currentStorage.timestamp);
			expirationDate.setDate(expirationDate.getDate() + parseInt(expiration, 10));

			// Remove expired storage if it exists.
			if (expirationDate.getTime() < currentStorage.timestamp) {
				localStorage?.removeItem(storageName);
			}
		}

		// Prepare new output.
		const output = {
			...currentStorageFinal,
			...newStorageFinal,
		};

		// If output is empty something was wrong here and just bailout.
		if (Object.keys(output).length === 0) {
			return;
		}

		// If nothing has changed bailout.
		if (JSON.stringify(currentStorageFinal) === JSON.stringify(output)) {
			return;
		}

		// Add timestamp to the new output.
		const finalOutput = {
			...output,
			timestamp: newStorage.timestamp.toString(),
		};

		// Update localStorage with the new item.
		localStorage?.setItem(storageName, JSON.stringify(finalOutput));
	}

	/**
	 * Get localStorage data.
	 *
	 * @param {string} storageName Storage name.
	 *
	 * @returns {object}
	 */
	getLocalStorage(storageName) {
		if (!this.state.getStateEnrichmentIsUsed() || !this.state.getStateEnrichmentIsLocalStorageUsed()) {
			return null;
		}

		if (!localStorage) {
			return null;
		}

		return localStorage?.getItem(storageName);
	}

	/**
	 * Delete localStorage data.
	 *
	 * @param {string} storageName Storage name.
	 *
	 * @returns {void}
	 */
	deleteLocalStorage(storageName) {
		if (!this.state.getStateEnrichmentIsUsed() || !this.state.getStateEnrichmentIsLocalStorageUsed()) {
			return;
		}

		if (!localStorage) {
			return;
		}

		localStorage?.removeItem(storageName);
	}

	/**
	 * Filter all url params based on the allowed tags list.
	 *
	 * @param {array} allowedTags List of allowed tags from config.
	 *
	 * @returns {object}
	 */
	getUrlAllowedParams(allowedTags) {
		const output = {};

		// Bailout if nothing is set in the url.
		if (!window.location.search) {
			return output;
		}

		// Find url params.
		const searchParams = new URLSearchParams(window.location.search);

		allowedTags.forEach((element) => {
			const item = searchParams.get(element);

			if (item) {
				output[element] = item.toString();
			}
		});

		return output;
	}

	/**
	 * Filter all set cookies based on the allowed tags list.
	 *
	 * @param {array} allowedTags List of allowed tags from config.
	 *
	 * @returns {object}
	 */
	getCookiesAllowedParams(allowedTags) {
		const output = {};

		allowedTags.forEach((element) => {
			const item = cookies.getCookie(element);

			if (item) {
				output[element] = item.toString();
			}
		});

		return output;
	}

	/**
	 * Prefill form fields with data - url params.
	 *
	 * @param {string} formId Form ID.
	 * @param {array} data Field data.
	 *
	 * Note:
	 * Field divider is / and value divider is ==.
	 *
	 * Fields:
	 * - checkboxes (checkboxes==check1---test). If the value is not in the checkboxes group, it will be added to the input field if it exists.
	 * - input (input==test).
	 * - range (range==10).
	 * - rating (rating==1).
	 * - textarea (textarea==test test).
	 * - radios (radios==radio-2). If the value is not in the radio group, it will be added to the input field if it exists.
	 * - date (date==2021-01-01).
	 * - datetime (datetime==2021-01-01 12:00).
	 * - select (select==option-1---option-2).
	 * - phone (phone==385---123456789). Prefix and value.
	 * - country (country==hr---de). Country code.
	 *
	 * Example:
	 * ?form-840=checkboxes==check1---check2/input==test/range==10
	 *
	 * @returns {void}
	 */
	prefillByUrlData(formId, data) {
		data.forEach((param) => {
			const paramItem = param.split('==');

			if(!paramItem.length) {
				return;
			}

			const name = paramItem[0];
			const value = paramItem[1];

			if (!name || !value) {
				return;
			}

			switch (this.state.getStateElementTypeField(name, formId)) {
				case 'phone':
					const phoneValue = value.split('---');

					if (!phoneValue.length) {
						break;
					}

					const newPhoneValue = {
						prefix: phoneValue[0] || '',
						value: phoneValue[1] ,
					};

					this.utils.setManualPhoneValue(formId, name, newPhoneValue);
					break;
				case 'date':
				case 'dateTime':
					this.utils.setManualDateValue(formId, name, value);
					break;
				case 'country':
				case 'select':
					const selectValue = value.split('---');

					if (!selectValue.length) {
						break;
					}

					const newSelectValue = selectValue.map((item) => ({value: item}));

					this.utils.setManualSelectValue(formId, name, newSelectValue);
					break;
				case 'checkbox':
					const checkboxValue = value.split('---');

					if (!checkboxValue.length) {
						break;
					}

					const innerCheckbox = this.state.getStateElementItems(name, formId);
					const inputCheckbox = this.state.getStateElementCustom(name, formId);

					const newCheckboxValue = {};

					checkboxValue.forEach((item) => {
						if (item in innerCheckbox) {
							newCheckboxValue[item] = item;
						} else {
							if (inputCheckbox) {
								this.utils.setManualInputValue(
									formId,
									inputCheckbox.name,
									item,
								);
							}
						}
					});

					this.utils.setManualCheckboxValue(formId, name, newCheckboxValue);
					break;
				case 'radio':
					const innerRadio = this.state.getStateElementItems(name, formId);
					const inputRadio = this.state.getStateElementCustom(name, formId);

					// If we have input part of the radio, and the value is not in the radio group add it to the input.
					if (value !== '' && !innerRadio?.[value] && inputRadio) {
						this.utils.setManualInputValue(
							formId,
							inputRadio.name,
							value,
							true,
							true
						);
					}

					this.utils.setManualRadioValue(formId, name, value, true, true);
					break;
				case 'rating':
					this.utils.setManualRatingValue(formId, name, value);
					break;
				case 'range':
					this.utils.setManualRangeValue(formId, name, value);
					break;
				default:
					this.utils.setManualInputValue(formId, name, value);
					break;
			}
		});

		this.utils.dispatchFormEvent(formId, this.state.getStateEvent('enrichmentPrefill'), data);
	}

	/**
	 * Prefill form fields with data - localstorage.
	 *
	 * @param {string} formId Form ID.
	 * @param {object} data Field data.
	 * 
	 * @returns {void}
	 */
	prefillByLocalstorageData(formId, data) {
		Object.entries(data).forEach(([name, value]) => {
			if (name === 'timestamp') {
				return;
			}

			switch (this.state.getStateElementTypeField(name, formId)) {
				case 'phone':
					this.utils.setManualPhoneValue(formId, name, value);
					break;
				case 'date':
				case 'dateTime':
					this.utils.setManualDateValue(formId, name, value);
					break;
				case 'country':
				case 'select':
					this.utils.setManualSelectValue(formId, name, value);
					break;
				case 'checkbox':
					this.utils.setManualCheckboxValue(formId, name, value);
					break;
				case 'radio':
					this.utils.setManualRadioValue(formId, name, value, true, true);
					break;
				case 'rating':
					this.utils.setManualRatingValue(formId, name, value);
					break;
				case 'range':
					this.utils.setManualRangeValue(formId, name, value);
					break;
				default:
					this.utils.setManualInputValue(formId, name, value, true, true);
					break;
			}
		});

		this.utils.dispatchFormEvent(formId, this.state.getStateEvent('enrichmentPrefill'), data);
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
			this.onLocalstoragePrefillEvent
		);

		this.state.getStateFormElement(formId)?.removeEventListener(
			this.state.getStateEvent('formJsLoaded'),
			this.onUrlParamsPrefillEvent
		);
	}

	////////////////////////////////////////////////////////////////
	// Events callback
	////////////////////////////////////////////////////////////////

	/**
	 * Set url params value for every field.
	 *
	 * @param {object} event Event callback.
	 *
	 * @returns {void}
	 */
	onUrlParamsPrefillEvent = (event) => {
		const { formId } = event.detail;

		// Bailout if nothing is set in the url.
		if (!window.location.search) {
			return;
		}

		// Find url params.
		const searchParams = new URLSearchParams(window.location.search);

		let params = searchParams.get(`form-${this.state.getStateFormFid(formId)}`);

		if(!params) {
			return;
		}

		params = params.split('/');

		if(!params.length) {
			return;
		}

		this.prefillByUrlData(formId, params);
	};

	/**
	 * Set localStorage value for every field.
	 *
	 * @param {object} event Event callback.
	 *
	 * @returns {void}
	 */
	onLocalstoragePrefillEvent = (event) => {
		const { formId } = event.detail;

		let data = {};

		try {
			data = JSON.parse(this.getLocalStorage(this.state.getStateEnrichmentFormPrefillStorageName(formId)));
		} catch {
			return;
		}

		if (!data) {
			return;
		}

		this.prefillByLocalstorageData(formId, data);
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

		if (window[prefix].enrichment) {
			return;
		}

		window[prefix].enrichment = {
			init: () => {
				this.init();
			},
			setLocalStorageEnrichment: () => {
				this.setLocalStorageEnrichment();
			},
			setLocalStorageFormPrefill: () => {
				this.setLocalStorageFormPrefill();
			},
			setUrlParamsFormPrefill: () => {
				this.setUrlParamsFormPrefill();
			},
			setLocalStorageFormPrefillItem: (formId, name) => {
				this.setLocalStorageFormPrefillItem(formId, name);
			},
			setLocalStorage: (newStorage, storageName, expiration) => {
				this.setLocalStorage(newStorage, storageName, expiration);
			},
			getLocalStorage: (storageName) => {
				return this.getLocalStorage(storageName);
			},
			deleteLocalStorage: (storageName) => {
				this.deleteLocalStorage(storageName);
			},
			getUrlAllowedParams: (allowedTags) => {
				return this.getUrlAllowedParams(allowedTags);
			},
			getCookiesAllowedParams: (allowedTags) => {
				return this.getCookiesAllowedParams(allowedTags);
			},
			prefillByLocalstorageData: (formId, data) => {
				this.prefillByLocalstorageData(formId, data);
			},
			removeEvents: (formId) => {
				this.removeEvents(formId);
			},
			onUrlParamsPrefillEvent: (event) => {
				this.onUrlParamsPrefillEvent(event);
			},
			onLocalstoragePrefillEvent: (event) => {
				this.onLocalstoragePrefillEvent(event);
			},
		};
	}
}
