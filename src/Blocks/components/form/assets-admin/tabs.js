export class Tabs {
	constructor(options) {
		this.tabsSelector = options.tabsSelector;
		this.tabSelector = options.tabSelector;
		this.navClass = 'js-es-tabs-nav';
	}

	init = () => {
		const tabsElements = document.querySelectorAll(this.tabsSelector);

		if (!tabsElements?.length) {
			return;
		}

		[...tabsElements].forEach((tabs) => {
			this.buildTabButtons(tabs);
			this.setActiveByHash(tabs);

			const tabElements = tabs.querySelectorAll(this.tabSelector);

			[...tabElements].forEach((tab) => {
				tab.addEventListener('toggle', this.onToggle);
			});
		});
	};

	buildTabButtons = (tabs) => {
		const details = [...tabs.querySelectorAll(this.tabSelector)];

		if (details.length <= 1) {
			return;
		}

		const btnClass = details[0]?.getAttribute('data-btn-class') ?? '';

		const nav = document.createElement('div');
		nav.className = `${this.navClass} esf:hidden esf:md:flex esf:flex-wrap esf:gap-x-15 esf:gap-y-10`;

		details.forEach((detail) => {
			const label = detail.querySelector('summary')?.textContent?.trim() ?? '';
			const hash = detail.getAttribute('data-hash') ?? '';

			const btn = document.createElement('button');
			btn.type = 'button';
			btn.className = btnClass;
			btn.setAttribute('data-hash', hash);
			btn.setAttribute('aria-selected', 'false');
			btn.textContent = label;
			btn.addEventListener('click', this.onTabButtonClick);

			nav.appendChild(btn);
		});

		tabs.insertBefore(nav, tabs.firstChild);
	};

	setActiveByHash = (elements) => {
		const hash = window.location.hash.substring(1);
		const element = elements.querySelector(`${this.tabSelector}[data-hash="${hash}"]`);

		this.activate(elements, element ?? elements.querySelector(this.tabSelector));
	};

	activate = (tabs, detail) => {
		if (!detail) {
			return;
		}

		[...tabs.querySelectorAll(this.tabSelector)].forEach((d) => {
			d.open = d === detail;
		});

		const hash = detail.getAttribute('data-hash');
		const nav = tabs.querySelector(`.${this.navClass}`);

		if (nav) {
			[...nav.querySelectorAll('button')].forEach((btn) => {
				btn.setAttribute('aria-selected', btn.getAttribute('data-hash') === hash ? 'true' : 'false');
			});
		}
	};

	onTabButtonClick = (event) => {
		const btn = event.target.closest('button');
		const tabs = btn.closest(this.tabsSelector);
		const hash = btn.getAttribute('data-hash');
		const detail = tabs.querySelector(`${this.tabSelector}[data-hash="${hash}"]`);

		this.activate(tabs, detail);
		this.updateHash(detail);
	};

	onToggle = (event) => {
		const detail = event.target;

		if (!detail.open) {
			return;
		}

		const tabs = detail.closest(this.tabsSelector);

		// Close other details (only one accordion open at a time on mobile).
		[...tabs.querySelectorAll(this.tabSelector)].forEach((d) => {
			if (d !== detail) {
				d.open = false;
			}
		});

		const hash = detail.getAttribute('data-hash');
		const nav = tabs?.querySelector(`.${this.navClass}`);

		if (nav) {
			[...nav.querySelectorAll('button')].forEach((btn) => {
				btn.setAttribute('aria-selected', btn.getAttribute('data-hash') === hash ? 'true' : 'false');
			});
		}

		this.updateHash(detail);
	};

	updateHash = (element) => {
		if (element) {
			window.location.hash = element.getAttribute('data-hash');
		}
	};
}
