import { Utils } from "./utilities";
import { cookies } from '@eightshift/frontend-libs/scripts/helpers';

/**
 * Enrichment class.
 */
export class Enrichment {
	constructor(options = {}) {
		/** @type Utils */
		this.utils = options.utils ?? new Utils();

		// LocalStorage name.
		this.STORAGE_NAME = options.STORAGE_NAME ?? 'es-storage';
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

		// Check if enrichment is used.
		if (this.isEnrichmentUsed()) {
			this.setLocalStorage();
		}
	}

	/**
	 * Check if enrichment is used.
	 * 
	 * @public
	 */
	isEnrichmentUsed() {
		if (this.utils.SETTINGS.ENRICHMENT_CONFIG !== '[]') {
			return true;
		}

		return false;
	}

	/**
	 * Filter all url params based on the allowed tags list.
	 *
	 * @param {array} allowedTags List of allowed tags from config.
	 *
	 * @returns {object}
	 *
	 * @public
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
				output[element] = item;
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
	 *
	 * @public
	 */
	getCookiesAllowedParams(allowedTags) {
		const output = {};

		allowedTags.forEach((element) => {
			const item = cookies.getCookie(element);

			if (item) {
				output[element] = item;
			}
		});

		return output;
	}

	/**
	 * Set localStorage value.
	 * 
	 * @public
	 */
	setLocalStorage() {
		const config = JSON.parse(this.utils.SETTINGS.ENRICHMENT_CONFIG);

		const allowedTags = config?.allowed;
		const expiration = config?.expiration ?? '30';

		// Missing data from backend, bailout.
		if (!allowedTags) {
			return;
		}

		// Get storage from backend this is considered new by the page request.
		const newStorage = {
			...this.getUrlAllowedParams(allowedTags),
			...this.getCookiesAllowedParams(allowedTags)
		};

		// Add current timestamp to new storage.
		newStorage.timestamp = Date.now();

		// Store in a new variable for later usage.
		const newStorageFinal = {...newStorage};
		delete newStorageFinal.timestamp;

		// Current storage is got from localStorage.
		const currentStorage = JSON.parse(this.getLocalStorage());

		// Store in a new variable for later usage.
		const currentStorageFinal = {...currentStorage};
		delete currentStorageFinal.timestamp;

		// If storage exists check if it is expired.
		if (this.getLocalStorage() !== null) {
			// Update expiration date by number of days from the current
			let expirationDate = new Date(currentStorage.timestamp);
			expirationDate.setDate(expirationDate.getDate() + parseInt(expiration, 10));

			// Remove expired storage if it exists.
			if (expirationDate.getTime() < currentStorage.timestamp) {
				localStorage.removeItem(this.STORAGE_NAME);
			}
		}

		// Create new storage if this is the first visit or it was expired.
		if (this.getLocalStorage() === null) {
			localStorage.setItem(
				this.STORAGE_NAME,
				JSON.stringify(newStorage)
			);
			return;
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
			timestamp: newStorage.timestamp,
		};

		// Update localStorage with the new item.
		localStorage.setItem(this.STORAGE_NAME, JSON.stringify(finalOutput));
	}

	/**
	 * Get localStorage value.
	 * 
	 * @public
	 */
	getLocalStorage() {
		return localStorage.getItem(this.STORAGE_NAME);
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
		if (typeof window[this.prefix]?.enrichment === 'undefined') {
			window[this.utils.prefix].enrichment = {
				STORAGE_NAME: this.STORAGE_NAME,
				init: () => {
					this.init();
				},
				isEnrichmentUsed: () => {
					this.isEnrichmentUsed();
				},
				getUrlAllowedParams: (allowedTags) => {
					this.getUrlAllowedParams(allowedTags);
				},
				getCookiesAllowedParams: (allowedTags) => {
					this.getCookiesAllowedParams(allowedTags);
				},
				setLocalStorage: () => {
					this.setLocalStorage();
				},
				getLocalStorage: () => {
					this.getLocalStorage();
				},
			};
		}
	}
}
