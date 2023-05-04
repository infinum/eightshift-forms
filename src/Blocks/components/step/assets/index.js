import { Utils } from './../../form/assets/utilities';

/**
 * Main step class.
 */
export class Steps {
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
	}

	// Check if submit button is step change trigger or form submit.
	isStepTrigger(element) {
		if (element.classList.contains(this.utils.stepSubmitSelector.substring(1))) {
			return true;
		}

		return false;
	}

	formGoBackAStep(element, currentId) {
		this.utils.hideLoader(element);

		const flow = JSON.parse(element.getAttribute(this.utils.DATA_ATTRIBUTES.formStepsFlow));

		this.formChangeStepById(element, flow.pop(), currentId, 'prev');
	}

	formChangeStepById(element, nextId, currentId, direction) {
		// Find next and current step element.
		const nextStepElement = element.querySelector(`${this.utils.stepSelector}[${this.utils.DATA_ATTRIBUTES.fieldStepId}="${nextId}"]`);
		const currentStepElement = element.querySelector(`${this.utils.stepSelector}[${this.utils.DATA_ATTRIBUTES.fieldStepId}="${currentId}"]`);

		const flow = JSON.parse(element.getAttribute(this.utils.DATA_ATTRIBUTES.formStepsFlow));
		if (direction === 'next') {
			flow.push(currentId);
		} else {
			flow.pop();
		}
		element.setAttribute(this.utils.DATA_ATTRIBUTES.formStepsFlow, JSON.stringify(flow));

		// Activate next step.
		if (nextStepElement) {
			nextStepElement.classList.add(this.utils.SELECTORS.CLASS_ACTIVE);
		}

		// Deactivate current step.
		if (currentStepElement) {
			currentStepElement.classList.remove(this.utils.SELECTORS.CLASS_ACTIVE);
		}
	}

	/**
	 * Handle form submit in case of the step used and all logic.
	 * 
	 * @param {object} element Form element.
	 *
	 * @public
	 */
	formStepStubmit(element, response, currentId) {
		const isValidationError = response?.data?.validation !== undefined;

		// Add flow data attribute so we know where to go back.
		if (!element.hasAttribute(this.utils.DATA_ATTRIBUTES.formStepsFlow)) {
			element.setAttribute(this.utils.DATA_ATTRIBUTES.formStepsFlow, JSON.stringify([]));
		}

		// If error just output errors.
		if (isValidationError) {
			this.utils.outputErrors(element, response.data.validation);
		} else {
			// Is success go to the next step. Step order is determined by the backend.
			const nextId = response?.data?.nextStep;

			// If next step exists value exists do something.
			if (nextId) {
				this.formChangeStepById(element, nextId, currentId, 'next');
			}
		}
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
