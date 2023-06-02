export class Filter {
	constructor(options) {
		this.filterSelector = options.filterSelector;
		this.itemSelector = options.itemSelector;

		this.CLASS_HIDDEN = 'is-hidden';

		this.items = document.querySelectorAll(this.itemSelector);
	}

	init = () => {
		if (!this.items) {
			return;
		}

		this.setActiveFilterByUrl();

		document.querySelector(`${this.filterSelector} select`).addEventListener('change', this.onChange, true);
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

		if (selectedValue !== 'all') {
			this.filterItems(selectedValue);
		}
	};

	setActiveFilterByUrl = () => {
		const url = new URL(window.location);

		const param = url.searchParams.get('filter');

		if (param) {
			document.querySelector(`${this.filterSelector} select option[value=${param}]`).selected = true;
			this.setActiveItemsByFilter(param);
		}
	};

	filterItems = (selectedValue) => {
		[...this.items].forEach((item) => {
			if (item.getAttribute('data-integration-type') !== selectedValue) {
				item.classList.add(this.CLASS_HIDDEN);
			}
		});
	};

	filterResetItems = () => {
		[...this.items].forEach((item) => {
			item.classList.remove(this.CLASS_HIDDEN);
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
