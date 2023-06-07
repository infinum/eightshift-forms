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

		// Set all public methods.
		this.publicMethods();
	}

	////////////////////////////////////////////////////////////////
	// Public methods
	////////////////////////////////////////////////////////////////

	/**
	 * Init steps.
	 * 
	 * @public
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
	 * Handle form submit in case of the step used and all logic.
	 * 
	 * @param {object} element Form element.
	 *
	 * @public
	 */
	formStepSubmit(formId, response) {
		const {
			status,
			message,
			data,
		} = response;

		if (status === 'success') {
			this.goToNextStep(data?.nextStep, formId);
		} else {
			if (data?.validation !== undefined) {
				this.utils.outputErrors(formId, data?.validation);
				this.utils.setGlobalMsg(formId, message, status);
			} else {
			}
		}
	}

	goToNextStep(nextStep, formId) {
		if (!nextStep) {
			return;
		}
		const currentStep = this.state.getStateFormStepsCurrent(formId);

		const flow = [
			...this.state.getStateFormStepsFlow(formId),
			currentStep,
		];

		this.setChangeStep(nextStep, flow, formId);

		// Hide next button on last step.
		if (nextStep === this.state.getStateFormStepsLastStep(formId)) {
			this.state.getStateFormStepsElement(nextStep, formId).querySelector(`${this.state.getStateSelectorsStepSubmit()}[${this.state.getStateAttribute('submitStepDirection')}="${this.STEP_DIRECTION_NEXT}"]`).closest(this.state.getStateSelectorsField()).classList.add(this.state.getStateSelectorsClassHidden());
		}

		this.dispatchFormEvent(formId, this.state.getStateEventsStepsGoToNextStep());
	}

	goToPrevStep(formId) {
		const flow = this.state.getStateFormStepsFlow(formId);

		const nextStep =  flow[flow.length - 1];

		const newFlow = [
			...flow,
		];

		newFlow.pop();

		this.setChangeStep(nextStep, newFlow, formId);

		this.dispatchFormEvent(formId, this.state.getStateEventsStepsGoToPrevStep());
	}

	resetSteps(formId) {
		const firstStep = this.state.getStateFormStepsFirstStep(formId);
		if (!firstStep) {
			return;
		}

		this.setChangeStep(firstStep, [], formId);

		this.state.getStateFormStepsElement(firstStep, formId).querySelector(`${this.state.getStateSelectorsStepSubmit()}[${this.state.getStateAttribute('submitStepDirection')}="${this.STEP_DIRECTION_PREV}"]`).closest(this.state.getStateSelectorsField()).classList.add(this.state.getStateSelectorsClassHidden());
	}

	setChangeStep(nextStep, flow, formId) {
		if (!nextStep) {
			return;
		}

		const currentStep = this.state.getStateFormStepsCurrent(formId);

		this.state.getStateFormStepsElement(currentStep, formId).classList.remove(this.state.getStateSelectorsClassActive());
		this.state.getStateFormStepsElement(nextStep, formId).classList.add(this.state.getStateSelectorsClassActive());

		this.state.setState([this.state.FORM, this.state.STEPS, this.state.STEPS_CURRENT], nextStep, formId);
		this.state.setState([this.state.FORM, this.state.STEPS, this.state.STEPS_FLOW], flow, formId);
	}

	getIgnoreFields(formId) {
		const flow = this.state.getStateFormStepsFlow(formId);

		flow.push(this.state.getStateFormStepsLastStep(formId));

		const items = this.state.getStateFormStepsItems(formId);

		const filteredObject = [];

		Object.keys(items).forEach((key) => {
			if (!flow.includes(key)) {
				filteredObject.push(...items[key]);
			}
		});

		return filteredObject;
	}

	// ////////////////////////////////////////////////////////////////
	// // Events callback
	// ////////////////////////////////////////////////////////////////

	/**
	 * On init event callback.
	 *
	 * @param {CustomEvent} event Event object.
	 *
	 * @public
	 */
	onInitEvent = (event) => {
		const { formId } = event.detail;

		// Set fields logic.
		this.resetSteps(formId);
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
		this.state.setStateWindow();

		window[prefix].step = {}
	}
}
