export class Tabs {
	constructor(options) {
		this.tabSelector = options.tabSelector;

		this.CLASS_ACTIVE = 'is-active';
		this.CLASS_LOADED = 'is-loaded';
	}

	init = () => {
		const elements = document.querySelectorAll(this.tabSelector);

		if (!elements) {
			return
		}

		[...elements].forEach((element) => {
			element.addEventListener('click', this.onClick, true);
		});

		this.setActiveByHash(elements);

		this.addLoadedParent(elements[0].parentElement);
	};

	setActiveByHash = (elements) => {
		const hash = window.location.hash.substring(1);

		const element = document.querySelector(`${this.tabSelector}[data-hash="${hash}"]`);

		if (element) {
			this.addActive(element);
		} else {
			this.addActive(elements[0]);
		}
	}

	// Handle form submit and all logic.
	onClick = (event) => {
		const element = event.target;

		this.removeActive(element);
		this.addActive(element);
		this.updateHash(element);
	}

	removeActive = (element) => {
		const elements = element.parentElement.querySelectorAll(this.tabSelector);

		if (elements.length) {
			elements.forEach((item) => {
				item.classList.remove(this.CLASS_ACTIVE);
			});
		}
	}

	addActive = (element) => {
		if (element) {
			element.classList.add(this.CLASS_ACTIVE);
		}
	}

	addLoadedParent = (element) => {
		if (element) {
			element.classList.add(this.CLASS_LOADED);
		}
	}

	updateHash = (element) => {
		if (element) {
			window.location.hash = element.getAttribute('data-hash');
		}
	}
}
