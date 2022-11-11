export class Tabs {
	constructor(options) {
		this.tabsSelector = options.tabsSelector;
		this.tabSelector = options.tabSelector;

		this.CLASS_ACTIVE = 'is-active';
		this.CLASS_LOADED = 'is-loaded';
	}

	init = () => {
		const tabsElements = document.querySelectorAll(this.tabsSelector);

		if (!tabsElements) {
			return;
		}

		[...tabsElements].forEach((tabs) => {
			this.setActiveByHash(tabs);

			this.addLoadedParent(tabs);

			const tabElements = tabs.querySelectorAll(this.tabSelector);

			[...tabElements].forEach((tab) => {
				tab.addEventListener('click', this.onClick, true);
			});
		});
	};

	setActiveByHash = (elements) => {
		const hash = window.location.hash.substring(1);

		const element = elements.querySelector(`${this.tabSelector}[data-hash="${hash}"]`);

		if (element) {
			this.addActive(element);
		} else {
			this.addActive(elements.children[0]);
		}
	};

	// The onclick handler handles the tab switching logic.
	onClick = (event) => {
		const element = event.target.closest(this.tabSelector);

		this.removeActive(element);
		this.addActive(element);
		this.updateHash(element);
	};

	removeActive = (element) => {
		const elements = element.parentElement.querySelectorAll(this.tabSelector);

		if (elements.length) {
			elements.forEach((item) => {
				item.classList.remove(this.CLASS_ACTIVE);
			});
		}
	};

	addActive = (element) => {
		if (element) {
			element.classList.add(this.CLASS_ACTIVE);
		}
	};

	addLoadedParent = (element) => {
		if (element) {
			element.classList.add(this.CLASS_LOADED);
		}
	};

	updateHash = (element) => {
		if (element) {
			window.location.hash = element.getAttribute('data-hash');
		}
	};
}
