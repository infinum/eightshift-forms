import { prefix, setStateWindow } from './state-init';

/**
 * Main step class.
 */
export class Steps {
	constructor(utils) {
		/** @type {import('./utils').Utils} */
		this.utils = utils;
		/** @type {import('./state').State} */
		this.state = this.utils.getState();

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
			this.state.getStateEvent('formJsLoaded'),
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
			this.goToNextStep(formId, data?.[this.state.getStateResponseOutputKey('stepNextStep')], parseInt(data?.[this.state.getStateResponseOutputKey('stepProgressBarItems')], 10), Boolean(data?.[this.state.getStateResponseOutputKey('stepIsDisableNextButton')]));
		} else {
			const validationOutputKey = this.state.getStateResponseOutputKey('validation');

			if (data?.[validationOutputKey] !== undefined) {
				this.utils.outputErrors(formId, data?.[validationOutputKey]);
				this.utils.setGlobalMsg(formId, message, status);
			}
		}
	}

	/**
	 * Actions to run after form submit.
	 *
	 * @param {string} formId Form Id.
	 * @param {object} response Api response.
	 *
	 * @returns {void}
	 */
	formStepSubmitAfter(formId, response) {
		// Reset timeout for after each submit.
		if (typeof this.GLOBAL_MSG_TIMEOUT_ID === "number") {
			clearTimeout(this.GLOBAL_MSG_TIMEOUT_ID);
		}

		// Hide global msg in any case after some time.
		this.GLOBAL_MSG_TIMEOUT_ID = setTimeout(() => {
			this.utils.unsetGlobalMsg(formId);
		}, parseInt(this.state.getStateSettingsHideGlobalMessageTimeout(formId), 10));

		// Dispatch event.
		this.utils.dispatchFormEvent(formId, this.state.getStateEvent('afterFormSubmitEnd'), response);
	}

	/**
	 * Go to next step in the flow.
	 *
	 * @param {string} formId Form Id.
	 *
	 * @returns {void}
	 */
	goToNextStep(formId, nextStep, progressBarItems = 0, disableNextButton = false) {
		if (!nextStep) {
			return;
		}
		const currentStep = this.state.getStateFormStepsCurrent(formId);

		const flow = [
			...this.state.getStateFormStepsFlow(formId),
			currentStep,
		];

		this.setChangeStep(formId, nextStep, flow, progressBarItems);

		// Hide next button on last step.
		if (nextStep === this.state.getStateFormStepsLastStep(formId)) {
			this.state.getStateFormStepsElement(nextStep, formId).querySelector(`${this.state.getStateSelector('field', true)}[${this.state.getStateAttribute('submitStepDirection')}="${this.STEP_DIRECTION_NEXT}"]`)?.classList?.add(this.state.getStateSelector('isHidden'));
		}

		// Hide next button direted from the api.
		if (disableNextButton) {
			this.state.getStateFormStepsElement(nextStep, formId).querySelector(`${this.state.getStateSelector('field', true)}[${this.state.getStateAttribute('submitStepDirection')}="${this.STEP_DIRECTION_NEXT}"]`)?.classList?.add(this.state.getStateSelector('isHidden'));
		}

		this.utils.dispatchFormEvent(formId, this.state.getStateEvent('stepsGoToNextStep'));
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

		this.utils.dispatchFormEvent(formId, this.state.getStateEvent('stepsGoToPrevStep'));
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

		// If index is found, remove all steps after that index.
		if (nextStepIndex >= 0) {
			newFlow.splice(nextStepIndex, flow.length);
		}

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

		// Hide prev button.
		this.state.getStateFormStepsElement(firstStep, formId).querySelector(`${this.state.getStateSelector('field', true)}[${this.state.getStateAttribute('submitStepDirection')}="${this.STEP_DIRECTION_PREV}"]`)?.classList?.add(this.state.getStateSelector('isHidden'));
	}

	/**
	 * Update state with next step.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} nextStep Next step Id.
	 * @param {array} flow Flow to update.
	 * @param {int} progressBarItems Progress bar number of items.
	 *
	 * @returns {void}
	 */
	setChangeStep(formId, nextStep, flow, progressBarItems = 0) {
		if (!nextStep) {
			return;
		}

		// Find current step.
		const currentStep = this.state.getStateFormStepsCurrent(formId);

		// Remove active from current step.
		this.state.getStateFormStepsElement(currentStep, formId)?.classList?.remove(this.state.getStateSelector('isActive'));

		// Add active to new step.
		this.state.getStateFormStepsElement(nextStep, formId)?.classList?.add(this.state.getStateSelector('isActive'));

		// Reset filled steps.
		this.state.getStateFormStepsElements(formId).forEach((item) => item?.classList?.remove(this.state.getStateSelector('isFilled')));

		// Add filled to all filled steps.
		flow.forEach((item) => {
			this.state.getStateFormStepsElement(item, formId)?.classList?.add(this.state.getStateSelector('isFilled'));
		});

		// Set progress bar.
		this.setProgressBar(formId, nextStep, flow, progressBarItems);

		// Update state with the new current step.
		this.state.setStateFormStepsCurrent(nextStep, formId);

		// Update state with the new flow.
		this.state.setStateFormStepsFlow(flow, formId);
	}

	/**
	 * Set progress bar.
	 *
	 * @param {string} formId Form Id.
	 * @param {string} nextStep Next step Id.
	 * @param {array} flow Flow to update.
	 * @param {int} progressBarItems Progress bar number of items.
	 *
	 * @returns {void}
	 */
	setProgressBar(formId, nextStep, flow, progressBarItems = 0) {
		if (this.state.getStateFormStepsIsMultiflow(formId)) {
			// Multiflow setup.

			// Update count state when we have something from api.
			if (progressBarItems > 0) {
				this.state.setStateFormStepsProgressBarCount(progressBarItems, formId);
			}

			// Clear current progress bar.
			this.state.getStateFormStepsProgressBar(formId).innerHTML = '';

			// Loop items count from state and output empty divs.
			for (let index = 0; index < this.state.getStateFormStepsProgressBarCount(formId); index++) {
				// Create div element.
				const node = document.createElement("div");

				// Output active elements.
				if ((flow?.length === 0 && index === 0) || (flow?.length > 0 && index <= flow?.length)) {
					node.classList.add(this.state.getStateSelector('isFilled'));
				}

				// Append div element to progress bar.
				this.state.getStateFormStepsProgressBar(formId).append(node);
			}
		} else {
			// Multistep setup.
			// Find current step.
			const currentStep = this.state.getStateFormStepsCurrent(formId);

			// Remove active from current step.
			this.state.getStateFormStepsElementProgressBar(currentStep, formId)?.classList?.remove(this.state.getStateSelector('isActive'));
	
			// Add active to new step.
			this.state.getStateFormStepsElementProgressBar(nextStep, formId)?.classList?.add(this.state.getStateSelector('isActive'));
	
			// Reset filled steps.
			this.state.getStateFormStepsElementsProgressBar(formId).forEach((item) => item?.classList?.remove(this.state.getStateSelector('isFilled')));

			// Add filled to all filled steps.
			flow.forEach((item) => {
				this.state.getStateFormStepsElementProgressBar(item, formId)?.classList?.add(this.state.getStateSelector('isFilled'));
			});
		}
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
			this.state.getStateEvent('formJsLoaded'),
			this.onInitEvent
		);
	}

	toggleDebugPreview() {
		const debug = document.querySelectorAll(this.state.getStateSelector('stepDebugPreview', true));

		debug.forEach((item) => item?.addEventListener('click', this.onToggleDebugPreview));
	}

	////////////////////////////////////////////////////////////////
	// Events callback
	////////////////////////////////////////////////////////////////

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

		// Toggle Debug Preview.
		this.toggleDebugPreview();
	};

	/**
	 * On toggle debug preview.
	 *
	 * @param {CustomEvent} event Event object.
	 *
	 * @returns {void}
	 */
	onToggleDebugPreview = (event) => {
		event.preventDefault();
		const formId = this.state.getFormIdByElement(event.target);
		const form = this.state.getStateFormElement(formId);

		if (!form) {
			return;
		}

		form.classList.toggle(this.state.getStateSelector('isStepPreviewActive'));
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

		if (window[prefix].step) {
			return;
		}

		window[prefix].step = {
			STEP_DIRECTION_PREV: this.STEP_DIRECTION_PREV,
			STEP_DIRECTION_NEXT: this.STEP_DIRECTION_NEXT,

			initOne: (formId) => {
				this.initOne(formId);
			},
			formStepSubmit: (formId, response) => {
				this.formStepSubmit(formId, response);
			},
			formStepSubmitAfter: (formId, response) => {
				this.formStepSubmitAfter(formId, response);
			},
			goToNextStep: (formId, nextStep, progressBarItems = 0, disableNextButton = false) => {
				this.goToNextStep(formId, nextStep, progressBarItems, disableNextButton);
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
			setChangeStep: (formId, nextStep, flow, progressBarItems = 0) => {
				this.setChangeStep(formId, nextStep, flow, progressBarItems);
			},
			setProgressBar: (formId, nextStep, flow, progressBarItems = 0) => {
				this.setProgressBar(formId, nextStep, flow, progressBarItems);
			},
			getIgnoreFields: (formId) => {
				return this.getIgnoreFields(formId);
			},
			removeEvents: () => {
				this.removeEvents();
			},
			onInitEvent: (event) => {
				this.onInitEvent(event);
			},
		};
	}
}
