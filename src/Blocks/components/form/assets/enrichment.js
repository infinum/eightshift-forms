import { cookies } from '@eightshift/frontend-libs/scripts/helpers';
import { prefix, setStateWindow } from './state/init';

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
		if (!this.state.getStateEnrichmentIsUsed()) {
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
			this.state.getStateEventsFormJsLoaded(),
			this.onPrefillEvent
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
			this.state.getStateEventsFormJsLoaded(),
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
		if (!this.state.getStateEnrichmentIsUsed() || !this.state.getStateEnrichmentIsPrefillUsed()) {
			return;
		}

		const type = this.state.getStateElementTypeInternal(name, formId);
		let value = '';
		let valueData = this.state.getStateElementValue(name, formId);

		if (typeof valueData === 'undefined') {
			valueData = '';
		}

		switch (type) {
			case this.state.getStateIntType('phone'):
				value = {
					prefix: this.state.getStateElementValueCountry(name, formId)?.number,
					value: valueData,
				};
				break;
			default:
				value = valueData;
				break;
		}

		const newStorage = {
			[name]: value,
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
		if (!this.state.getStateEnrichmentIsUsed()) {
			return;
		}

		if (!newStorage) {
			return;
		}

		// Add current timestamp to new storage.
		newStorage.timestamp = Date.now();

		// Create new storage if this is the first visit or it was expired.
		if (this.getLocalStorage(storageName) === null) {
			newStorage.timestamp = newStorage.timestamp.toString();

			localStorage.setItem(
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
				localStorage.removeItem(storageName);
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
		localStorage.setItem(storageName, JSON.stringify(finalOutput));
	}

	/**
	 * Get localStorage data.
	 *
	 * @param {string} storageName Storage name.
	 *
	 * @returns {object}
	 */
	getLocalStorage(storageName) {
		return localStorage.getItem(storageName);
	}

	/**
	 * Delete localStorage data.
	 *
	 * @param {string} storageName Storage name.
	 *
	 * @returns {void}
	 */
	deleteLocalStorage(storageName) {
		localStorage.removeItem(storageName);
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
	 * Prefill form fields with data.
	 *
	 * @param {string} formId Form ID.
	 * @param {object} data Data to prefill.
	 * 
	 * @returns {void}
	 */
	prefillByData(formId, data) {
		Object.entries(data).forEach(([name, value]) => {
			if (name === 'timestamp') {
				return;
			}

			switch (this.state.getStateElementTypeInternal(name, formId)) {
				case this.state.getStateIntType('phone'):
					this.utils.setManualPhoneValue(formId, name, value);
					break;
				case this.state.getStateIntType('date'):
				case this.state.getStateIntType('dateTime'):
					this.utils.setManualDateValue(formId, name, value);
					break;
				case this.state.getStateIntType('country'):
				case this.state.getStateIntType('select'):
					this.utils.setManualSelectValue(formId, name, value);
					break;
				case this.state.getStateIntType('checkbox'):
					this.utils.setManualCheckboxValue(formId, name, value);
					break;
				case this.state.getStateIntType('radio'):
					this.utils.setManualRadioValue(formId, name, value);
					break;
				case this.state.getStateIntType('rating'):
					this.utils.setManualRatingValue(formId, name, value);
					break;
				default:
					this.utils.setManualInputValue(formId, name, value);
					break;
			}
		});

		this.utils.dispatchFormEvent(formId, this.state.getStateEventsEnrichmentPrefill(), data);
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
			this.onPrefillEvent
		);

		this.state.getStateFormElement(formId).removeEventListener(
			this.state.getStateEventsFormJsLoaded(),
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

		const param = searchParams.get(`form-${formId}`);

		if(!param) {
			return;
		}

		let data = {};

		try {
			data = JSON.parse(param);
		} catch {
			return;
		}

		if(!data) {
			return;
		}

		this.prefillByData(formId,data);
	};

	/**
	 * Set localStorage value for every field.
	 *
	 * @param {object} event Event callback.
	 *
	 * @returns {void}
	 */
	onPrefillEvent = (event) => {
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

		this.prefillByData(formId,data);
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
			prefillByData: (formId, data) => {
				this.prefillByData(formId, data);
			},
			removeEvents: () => {
				this.removeEvents();
			},
			onUrlParamsPrefillEvent: (event) => {
				this.onUrlParamsPrefillEvent(event);
			},
			onPrefillEvent: (event) => {
				this.onPrefillEvent(event);
			},
		};
	}
}
