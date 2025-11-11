export class Filter {
	constructor(options={}) {
		/** @type {import('./../assets/utils').Utils} */
		this.utils = options.utils;
		/** @type {import('./../assets/state').State} */
		this.state = this.utils.getState();


		this.searchSelector = document.querySelector(options.searchSelector);
		this.pageSelector = document.querySelector(options.pageSelector);
		this.itemSelector = document.querySelector(options.itemSelector);
	}

	init = () => {
		this.searchSelector.focus();
		this.searchSelector.addEventListener('change', this.onChangeInput, true);
		this.pageSelector.addEventListener('change', this.onChangeSelect, true);
	};

	// Handle form submit and all logic.
	onChangeInput = (event) => {
		const element = event.target;
		const selectedValue = element.value;

		this.updateSearchToUrl(element.name, selectedValue);
	};

	onChangeSelect = (event) => {
		const element = event.target;
		const selectedValue = element.value;

		this.updateSearchToUrl(element.name, selectedValue);
	};

	updateSearchToUrl = (param, value) => {
		const url = new URL(window.location);

		if (value) {
			url.searchParams.set(param, value);
		} else {
			url.searchParams.delete(param);
		}

		url.searchParams.delete('paged');

		window.location.href = url;
	};
}
