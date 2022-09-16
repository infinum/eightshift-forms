import Sortable from 'sortablejs';

export class Sorting {
	constructor(options) {
		this.selector = options.selector;
		this.trigger = options.trigger;
		this.update = options.update;

		this.CLASS_ACTIVE = 'is-active';

		this.DISPLAY_STYLE_NONE = 'none';
		this.DISPLAY_STYLE_BLOCK = 'block';

		this.sortable = null;
		this.container = null;

		this.triggerElement = document.querySelector(this.trigger);
		this.updateElement = document.querySelector(this.update);
	}

	init = () => {
		this.triggerElement.addEventListener('click', this.onClickTrigger, true);
		this.updateElement.addEventListener('click', this.onClickUpdate, true);
	};

	// Handle form submit and all logic.
	onClickTrigger = (event) => {
		event.preventDefault();
		this.triggerElement.style.display = this.DISPLAY_STYLE_NONE;
		this.updateElement.style.display = this.DISPLAY_STYLE_BLOCK;

		this.container = document.querySelector(`${this.selector} > *`);

		this.container.classList.add(this.CLASS_ACTIVE);
		
		this.sortable = new Sortable(this.container, {
			animation: 150,
		});
	};

	// Handle form submit and all logic.
	onClickUpdate = (event) => {
		event.preventDefault();
		this.updateElement.style.display = this.DISPLAY_STYLE_NONE;
		this.triggerElement.style.display = this.DISPLAY_STYLE_BLOCK;

		const fields = document.querySelectorAll(`${this.selector} [data-integration-field-type='order']`);

		if (fields.length) {
			fields.forEach((element, index) => {
				element.value = index + 1;
			});
		}

		this.container.classList.remove(this.CLASS_ACTIVE);

		this.sortable.destroy();
	};
}
