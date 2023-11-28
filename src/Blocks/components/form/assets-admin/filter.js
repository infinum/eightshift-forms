export class Filter {
	constructor(options={}) {
		/** @type {import('./../assets/utils').Utils} */
		this.utils = options.utils;
		/** @type {import('./../assets/state').State} */
		this.state = this.utils.getState();

		this.filterSelector = options.filterSelector;
		this.itemSelector = options.itemSelector;
	}

	init = () => {
		if (!document.querySelectorAll(this.itemSelector)?.length) {
			return;
		}

		this.setActiveFilterByUrl();

		document.querySelector(this.filterSelector).addEventListener('change', this.onChange, true);
	};

	// Handle form submit and all logic.
	onChange = (event) => {
		const element = event.target;
		const selectedValue = element.options[element.selectedIndex].value;

		this.setActiveItemsByFilter(selectedValue);
	};

	setActiveItemsByFilter = (selectedValue) => {
		this.updateFilterToUrl(selectedValue);

		this.filterResetItems();

		if (selectedValue !== '') {
			this.filterItems(selectedValue);
		}
	};

	setActiveFilterByUrl = () => {
		const url = new URL(window.location);

		const param = url.searchParams.get('filter');

		if (param) {
			document.querySelector(`${this.filterSelector} option[value=${param}]`).selected = true;
			this.setActiveItemsByFilter(param);
		}
	};

	filterItems = (selectedValue) => {
		[...document.querySelectorAll(this.itemSelector)].forEach((item) => {
			if (item.getAttribute(this.state.getStateAttribute('adminIntegrationType')) !== selectedValue) {
				item?.classList?.add(this.state.getStateSelectorsClassHidden());
			}
		});
	};

	filterResetItems = () => {
		[...document.querySelectorAll(this.itemSelector)].forEach((item) => {
			item?.classList?.remove(this.state.getStateSelectorsClassHidden());
		});
	};

	updateFilterToUrl = (filter) => {
		const url = new URL(window.location);

		if (filter) {
			url.searchParams.set('filter', filter);
		} else {
			url.searchParams.delete('filter');
		}
		window.history.pushState(null, '', url.toString());
	};
}
