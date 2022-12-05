import { Utils } from "./utilities";

if (typeof esFormsLocalization === 'undefined') {
	throw 'Your project is missing global variable esFormsLocalization called from the enqueue script in the forms.';
}

/**
 * Local Storage Utilities class.
 */
export class LocalStorageUtils {
	constructor(options = {}) {
		/** @type Utils */
		this.utils = options ?? new Utils();

		// LocalStorage.
		this.STORAGE_NAME = options.STORAGE_NAME ?? 'es-storage';
	}

	// Set local storage value.
	setLocalStorage = () => {
		const storageConfig = JSON.parse(this.utils.storageConfig);

		const allowedTags = storageConfig?.allowed;
		const expiration = storageConfig?.expiration ?? '30';

		// Missing data from backend, bailout.
		if (!allowedTags) {
			return;
		}

		// Bailout if nothing is set in the url.
		if (!window.location.search) {
			return;
		}

		// Find url params.
		const searchParams = new URLSearchParams(window.location.search);

		// Get storage from backend this is considered new by the page request.
		const newStorage = {};

		// Loop entries and get new storage values.
		for (const [key, value] of searchParams.entries()) {
			// Bailout if not allowed or empty
			if (!allowedTags.includes(key) || value === '') {
				continue;
			}

			// Add valid tag.
			newStorage[key] = value;
		}

		// Bailout if nothing is set from allowed tags or everything is empty.
		if (Object.keys(newStorage).length === 0) {
			return;
		}

		// Add current timestamp to new storage.
		newStorage.timestamp = Date.now();

		// Store in a new variable for later usage.
		const newStorageFinal = {...newStorage};
		delete newStorageFinal.timestamp;

		// current storage is got from local storage.
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
	};

	// Get local storage value.
	getLocalStorage = () => {
		return localStorage.getItem(this.STORAGE_NAME);
	};
}
