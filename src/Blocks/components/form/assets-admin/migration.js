export class Migration {
	constructor(options) {
		this.selector = options.selector;
		this.formSelector = options.formSelector;

		this.migrationRestUrl = options.migrationRestUrl;
		this.globalMsgSelector = `${this.formSelector}-global-msg`;

		this.CLASS_ACTIVE = 'is-active';
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
				this.setGlobalMsg(response.message, response.status);

				// Hide global msg in any case after some time.
				setTimeout(() => {
					location.reload();
				}, 1000);
			});
	};

	// Set global message.
	setGlobalMsg = (msg, status) => {
		const messageContainer = document.querySelector(this.globalMsgSelector);

		if (!messageContainer) {
			return;
		}

		messageContainer.classList.add(this.CLASS_ACTIVE);
		messageContainer.dataset.status = status;
		messageContainer.innerHTML = `<span>${msg}</span>`;
	};
}
