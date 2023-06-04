import { State, prefix } from './state';
import { Utils } from './utilities';

/**
 * Main step class.
 */
export class Steps {
	constructor(options = {}) {
		this.state = new State(options);
		this.utils = new Utils(options);

		this.STEP_DIRECTION_PREV = 'prev';
		this.STEP_DIRECTION_NEXT = 'next';
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
		// this.publicMethods();
	}

	/**
	 * Handle form submit in case of the step used and all logic.
	 * 
	 * @param {object} element Form element.
	 *
	 * @public
	 */
		formStepStubmit(formId, response) {
			const {
				status,
				data: {
					validation,
					nextStep,
				},
			} = response;
	
			console.log(response);
	
			if (status === 'success') {
				this.goToNextStep(nextStep, formId);
			} else {
				if (validation !== undefined) {
					// this.steps.goBackToFirstValidationErrorStep(formId, validation);
					this.utils.outputErrors(formId, validation);
				}
			}
	
			// // If error just output errors.
			// if (isValidationError) {
			// 	this.utils.outputErrors(element, response.data.validation);
			// } else {
			// 	// Is success go to the next step. Step order is determined by the backend.
			// 	const nextId = response?.data?.nextStep;
	
			// 	// If next step exists value exists do something.
			// 	if (nextId) {
			// 		this.changeStepById(element, nextId, this.STEP_DIRECTION_NEXT);
			// 	}
			// }
		}

	goToNextStep(nextStep, formId) {
		const currentStep = this.state.getStateFormStepsCurrent(formId);
		const flow = [
			...this.state.getStateFormStepsFlow(formId),
			currentStep,
		];

		this.setChangeStep(nextStep, flow, formId);
	}

	goToPrevStep(formId) {
		const flow = this.state.getStateFormStepsFlow(formId);
		const nextStep =  flow[flow.length - 1];

		const newFlow = [
			...flow,
		];

		newFlow.pop();

		this.setChangeStep(nextStep, newFlow, formId);
	}

	resetSteps(formId) {
		this.setChangeStep('step-0', [], formId);
	}

	setChangeStep(nextStep, flow, formId) {
		const currentStep = this.state.getStateFormStepsCurrent(formId);

		this.state.getStateFormStepsElement(currentStep, formId).classList.remove(this.state.getStateSelectorsClassActive());
		this.state.getStateFormStepsElement(nextStep, formId).classList.add(this.state.getStateSelectorsClassActive());

		this.state.setState([this.state.FORM, this.state.STEPS, this.state.STEPS_CURRENT], nextStep, formId);
		this.state.setState([this.state.FORM, this.state.STEPS, this.state.STEPS_FLOW], flow, formId);
	}















	// getAllFieldsInStep(element, stepId) {
	// 	return element.querySelectorAll(`${this.utils.stepSelector}[${this.utils.DATA_ATTRIBUTES.fieldStepId}="${stepId}"] ${this.utils.fieldSelector}`);
	// }

	// getStepById(element, stepId) {
	// 	return element.querySelector(`${this.utils.stepSelector}[${this.utils.DATA_ATTRIBUTES.fieldStepId}="${stepId}"]`)
	// }

	// // Return step ID by field name.
	// getStepIdByFieldName(element, name) {
	// 	return this.utils.getFieldByName(element, name)?.closest(this.utils.stepSelector)?.getAttribute(this.utils.DATA_ATTRIBUTES.fieldStepId);
	// }

	// // Check if submit button is step change trigger or form submit.
	// isStepTrigger(element) {
	// 	if (element.classList.contains(this.utils.stepSubmitSelector.substring(1))) {
	// 		return true;
	// 	}

	// 	return false;
	// }

	// goBackAStep(element) {
	// 	this.utils.hideLoader(element);

	// 	const flow = JSON.parse(element.getAttribute(this.utils.DATA_ATTRIBUTES.formStepsFlow));

	// 	this.changeStepById(element, flow.pop(), this.STEP_DIRECTION_PREV);
	// }

	// goBackToFirstValidationErrorStep(element, fields) {
	// 	if (typeof fields === 'undefined') {
	// 		return;
	// 	}

	// 	if (Object.entries(fields).length > 0) {
	// 		const firstItem = Object.keys(fields)[0];

	// 		const stepId = this.getStepIdByFieldName(element, firstItem);

	// 		if (stepId) {
	// 			this.changeStepById(element, stepId, this.STEP_DIRECTION_PREV);
	// 		}
	// 	}
	// }

	// changeStepById(element, stepId, direction) {
	// 	const currentId = element.getAttribute(this.utils.DATA_ATTRIBUTES.formStepsCurrent);

	// 	const flow = JSON.parse(element.getAttribute(this.utils.DATA_ATTRIBUTES.formStepsFlow));
	// 	if (direction === 'next') {
	// 		flow.push(currentId);
	// 	} else {
	// 		flow.pop();
	// 	}
	// 	element.setAttribute(this.utils.DATA_ATTRIBUTES.formStepsCurrent, stepId);
	// 	element.setAttribute(this.utils.DATA_ATTRIBUTES.formStepsFlow, JSON.stringify(flow));

	// 	// Deactivate all steps.
	// 	const allStepsElement = element.querySelectorAll(`${this.utils.stepSelector}`);
	// 	if (allStepsElement) {
	// 		[...allStepsElement].forEach((step) => {
	// 			step.classList.remove(this.utils.SELECTORS.CLASS_ACTIVE);
	// 		})
	// 	}

	// 	// Activate next step.
	// 	const nextStepElement = this.getStepById(element, stepId);
	// 	if (nextStepElement) {
	// 		nextStepElement.classList.add(this.utils.SELECTORS.CLASS_ACTIVE);
	// 	}
	// }

	// ////////////////////////////////////////////////////////////////
	// // Events callback
	// ////////////////////////////////////////////////////////////////

	// 	////////////////////////////////////////////////////////////////
	// // Private methods - not shared to the public window object.
	// ////////////////////////////////////////////////////////////////

	// /**
	//  * Set all public methods.
	//  * 
	//  * @private
	//  */
	// publicMethods() {
	// 	this.state.setStateWindow();
	// 	window[prefix].step = {}
	// }
}
