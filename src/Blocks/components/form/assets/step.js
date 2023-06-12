import { State } from './state';
import { prefix, setStateWindow } from './state/init';
import { Utils } from './utilities';

/**
 * Main step class.
 */
export class Steps {
	constructor() {
		this.state = new State();
		this.utils = new Utils();

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
			this.goToNextStep(formId, data?.nextStep);
		} else {
			if (data?.validation !== undefined) {
				this.utils.outputErrors(formId, data?.validation);
				this.utils.setGlobalMsg(formId, message, status);
			}
		}
	}

	goToNextStep(formId, nextStep) {
		if (!nextStep) {
			return;
		}
		const currentStep = this.state.getStateFormStepsCurrent(formId);

		const flow = [
			...this.state.getStateFormStepsFlow(formId),
			currentStep,
		];

		this.setChangeStep(formId, nextStep, flow);

		// Hide next button on last step.
		if (nextStep === this.state.getStateFormStepsLastStep(formId)) {
			this.state.getStateFormStepsElement(nextStep, formId).querySelector(`${this.state.getStateSelectorsStepSubmit()}[${this.state.getStateAttribute('submitStepDirection')}="${this.STEP_DIRECTION_NEXT}"]`).closest(this.state.getStateSelectorsField()).classList.add(this.state.getStateSelectorsClassHidden());
		}

		this.utils.dispatchFormEvent(formId, this.state.getStateEventsStepsGoToNextStep());
	}

	goToPrevStep(formId) {
		const flow = this.state.getStateFormStepsFlow(formId);

		const nextStep =  flow.pop();
		const newFlow = [
			...flow,
		];

		this.setChangeStep(formId, nextStep, newFlow);

		this.utils.dispatchFormEvent(formId, this.state.getStateEventsStepsGoToPrevStep());
	}

	goToStepWithError(formId, errors) {
		const flow = this.state.getStateFormStepsFlow(formId);
		const nextStep = Object.entries(this.state.getStateFormStepsItems(formId)).find(([key, arr]) => arr.includes(Object.keys(errors)[0]))?.[0] || null;
		const nextStepIndex = flow.findIndex((item) => item === nextStep);

		const newFlow = [
			...this.state.getStateFormStepsFlow(formId),
		];

		newFlow.splice(nextStepIndex, flow.length);

		this.setChangeStep(formId, nextStep, newFlow);
	}

	resetSteps(formId) {
		const firstStep = this.state.getStateFormStepsFirstStep(formId);
		if (!firstStep) {
			return;
		}

		this.setChangeStep(formId, firstStep, []);

		this.state.getStateFormStepsElement(firstStep, formId).querySelector(`${this.state.getStateSelectorsStepSubmit()}[${this.state.getStateAttribute('submitStepDirection')}="${this.STEP_DIRECTION_PREV}"]`).closest(this.state.getStateSelectorsField()).classList.add(this.state.getStateSelectorsClassHidden());
	}

	setChangeStep(formId, nextStep, flow) {
		if (!nextStep) {
			return;
		}

		const currentStep = this.state.getStateFormStepsCurrent(formId);

		this.state.getStateFormStepsElement(currentStep, formId).classList.remove(this.state.getStateSelectorsClassActive());
		this.state.getStateFormStepsElement(nextStep, formId).classList.add(this.state.getStateSelectorsClassActive());

		this.state.setStateFormStepsCurrent(nextStep, formId);
		this.state.setStateFormStepsFlow(flow, formId);
	}

	getIgnoreFields(formId) {
		const flow = [
			...this.state.getStateFormStepsFlow(formId),
		];

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
		setStateWindow();

		window[prefix].step = {}
	}
}
