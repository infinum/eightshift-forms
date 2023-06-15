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
	 * @param {string} formId Form Id.
	 * 
	 * @returns {void}
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
	 * @param {string} formId Form Id.
	 * @param {object} response Response from the API..
	 *
	 * @returns {void}
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

	/**
	 * Go to next step in the flow.
	 *
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
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
			this.state.getStateFormStepsElement(nextStep, formId).querySelector(`${this.state.getStateSelectorsStepSubmit()}[${this.state.getStateAttribute('submitStepDirection')}="${this.STEP_DIRECTION_NEXT}"]`).closest(this.state.getStateSelectorsField())?.classList?.add(this.state.getStateSelectorsClassHidden());
		}

		this.utils.dispatchFormEvent(formId, this.state.getStateEventsStepsGoToNextStep());
	}

	/**
	 * Go to prev step in the flow.
	 *
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	goToPrevStep(formId) {
		const flow = this.state.getStateFormStepsFlow(formId);

		const nextStep =  flow.pop();
		const newFlow = [
			...flow,
		];

		this.setChangeStep(formId, nextStep, newFlow);

		this.utils.dispatchFormEvent(formId, this.state.getStateEventsStepsGoToPrevStep());
	}

	/**
	 * Go to step with error if integration returns error after our validation.
	 *
	 * @param {string} formId Form Id.
	 * @param {object} errors Error object.
	 *
	 * @returns {void}
	 */
	goToStepWithError(formId, errors) {
		const flow = this.state.getStateFormStepsFlow(formId);
		const nextStep = Object.entries(this.state.getStateFormStepsItems(formId)).find(([key, arr]) => arr.includes(Object.keys(errors)[0]))?.[0] || null; // eslint-disable-line no-unused-vars
		const nextStepIndex = flow.findIndex((item) => item === nextStep);

		const newFlow = [
			...this.state.getStateFormStepsFlow(formId),
		];

		newFlow.splice(nextStepIndex, flow.length);

		this.setChangeStep(formId, nextStep, newFlow);
	}

	/**
	 * Reset steps to first step.
	 *
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	resetSteps(formId) {
		const firstStep = this.state.getStateFormStepsFirstStep(formId);
		if (!firstStep) {
			return;
		}

		this.setChangeStep(formId, firstStep, []);

		this.state.getStateFormStepsElement(firstStep, formId).querySelector(`${this.state.getStateSelectorsStepSubmit()}[${this.state.getStateAttribute('submitStepDirection')}="${this.STEP_DIRECTION_PREV}"]`).closest(this.state.getStateSelectorsField())?.classList?.add(this.state.getStateSelectorsClassHidden());
	}

	/**
	 * Update state with next step.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} nextStep Next step Id.
	 * @param {array} flow Flow to update.
	 *
	 * @returns {void}
	 */
	setChangeStep(formId, nextStep, flow) {
		if (!nextStep) {
			return;
		}

		const currentStep = this.state.getStateFormStepsCurrent(formId);

		this.state.getStateFormStepsElement(currentStep, formId)?.classList?.remove(this.state.getStateSelectorsClassActive());
		this.state.getStateFormStepsElement(nextStep, formId)?.classList?.add(this.state.getStateSelectorsClassActive());

		this.state.setStateFormStepsCurrent(nextStep, formId);
		this.state.setStateFormStepsFlow(flow, formId);
	}

	/**
	 * Get fields to ignore on form submit.
	 *
	 * @param {string} formId Form Id.
	 *
	 * @returns {array}
	 */
	getIgnoreFields(formId) {
		const flow = [
			...this.state.getStateFormStepsFlow(formId),
		];

		flow.push(this.state.getStateFormStepsLastStep(formId));

		const items = this.state.getStateFormStepsItems(formId);

		const filteredArray = [];

		Object.keys(items).forEach((key) => {
			if (!flow.includes(key)) {
				filteredArray.push(...items[key]);
			}
		});

		return filteredArray;
	}

	// ////////////////////////////////////////////////////////////////
	// // Events callback
	// ////////////////////////////////////////////////////////////////

	/**
	 * On init event callback.
	 *
	 * @param {CustomEvent} event Event object.
	 *
	 * @returns {void}
	 */
	onInitEvent = (event) => {
		const { formId } = event.detail;

		// Set fields logic.
		this.resetSteps(formId);
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

		window[prefix].step = {};
		window[prefix].step = {
			STEP_DIRECTION_PREV: this.STEP_DIRECTION_PREV,
			STEP_DIRECTION_NEXT: this.STEP_DIRECTION_NEXT,

			initOne: (formId) => {
				this.initOne(formId);
			},
			formStepSubmit: (formId, response) => {
				this.formStepSubmit(formId, response);
			},
			goToNextStep: (formId, nextStep) => {
				this.goToNextStep(formId, nextStep);
			},
			goToPrevStep: (formId) => {
				this.goToPrevStep(formId);
			},
			goToStepWithError: (formId, errors) => {
				this.goToStepWithError(formId, errors);
			},
			resetSteps: (formId) => {
				this.resetSteps(formId);
			},
			setChangeStep: (formId, nextStep, flow) => {
				this.setChangeStep(formId, nextStep, flow);
			},
			getIgnoreFields: (formId) => {
				return this.getIgnoreFields(formId);
			},
			onInitEvent: (event) => {
				this.onInitEvent(event);
			},
		};
	}
}
