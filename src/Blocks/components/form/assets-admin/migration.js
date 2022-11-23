import {
	setGlobalMsg,
	hideGlobalMsg,
} from './utilities';

export class Migration {
	constructor(options) {
		this.selector = options.selector;
		this.formSelector = options.formSelector;

		this.migrationRestUrl = options.migrationRestUrl;
		this.globalMsgSelector = `${this.formSelector}-global-msg`;
	}

	init = () => {
		const elements = document.querySelectorAll(this.selector);

		[...elements].forEach((element) => {
			element.addEventListener('click', this.onClick, true);
		});
	};

	// Handle form submit and all logic.
	onClick = (event) => {
		event.preventDefault();

		const element = event.target;

		const formData = new FormData();

		formData.append('type', element.getAttribute('data-type'));

		// Populate body data.
		const body = {
			method: 'POST',
			mode: 'same-origin',
			headers: {
				Accept: 'application/json',
			},
			body: formData,
			credentials: 'same-origin',
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		fetch(this.migrationRestUrl, body)
			.then((response) => {
				return response.json();
			})
			.then((response) => {
				setGlobalMsg(this.globalMsgSelector, response.message, response.status);

				setTimeout(() => {
					hideGlobalMsg(this.globalMsgSelector);
				}, 6000);
			});
	};
}
