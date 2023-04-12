import { Utils } from './../../form/assets/utilities';

/**
 * Main step class.
 */
export class Step {
	constructor(options) {
		/** @type Utils */
		this.utils = options.utils ?? new Utils();
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

		// Init all forms.
		// this.initOnlyForms();
	}

	////////////////////////////////////////////////////////////////
	// Events callback
	////////////////////////////////////////////////////////////////

		////////////////////////////////////////////////////////////////
	// Private methods - not shared to the public window object.
	////////////////////////////////////////////////////////////////

	/**
	 * Set all public methods.
	 * 
	 * @private
	 */
	publicMethods() {
		if (typeof window?.[this.utils.getPrefix()]?.step === 'undefined') {
			window[this.utils.getPrefix()].step = {
			}
		}
	}
}
