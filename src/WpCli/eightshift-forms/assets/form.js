export class Form {
	constructor(options) {
		this.formSelector = options.formSelector;

		this.CLASS_ACTIVE = 'es-form-is-active';
	}

	// Init all actions.
	init = () => {
		const elements = document.querySelectorAll(this.formSelector);

		if (elements.length) {
			[...elements].forEach(() => {

			});
		}
	};
}
