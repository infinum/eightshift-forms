export class Transfer {
	constructor(options) {
		this.selector = options.selector;
		this.formSelector = options.formSelector;
		this.itemSelector = options.itemSelector;

		this.transferRestUrl = options.transferRestUrl;
		this.globalMsgSelector = `${this.formSelector}-global-msg`;

		this.CLASS_ACTIVE = 'is-active';
	}

	init = () => {
		const elements = document.querySelectorAll(this.selector);

		[...elements].forEach((element) => {
			element.addEventListener('click', this.onClick, true);
		});

		const items = document.querySelectorAll(`${this.itemSelector} input`);
		[...items].forEach((element) => {
			element.addEventListener('click', this.onClickItem, true);
		});
	};

	// Handle form submit and all logic.
	onClick = (event) => {
		event.preventDefault();

		const element = event.target;

		const formData = new FormData();

		formData.append('type', element.getAttribute('data-type'));
		formData.append('items', element.getAttribute('data-items'));

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

		fetch(this.transferRestUrl, body)
			.then((response) => {
				return response.json();
			})
			.then((response) => {
				this.setGlobalMsg(response.message, response.status);

				if (response.code >= 200 && response.code <= 299) {
					this.createFile(response.data.content, response.data.name);
				}
			});
	};

	onClickItem = (event) => {
		const element = event.target;

		const button = document.querySelector(`${this.selector}[data-type='export-forms']`);
		const items = button.getAttribute('data-items');

		let output = items ? items.split(",") : [];

		const {value} = element;

		if (element.checked) {
			output.push(value);
		} else {
			output = output.filter((item) => item !== value)
		}

		button.disabled = !output.length;

		button.setAttribute('data-items', output);
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

	createFile(data, exportName) {
		const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(data);

		const downloadAnchorNode = document.createElement('a');

		downloadAnchorNode.setAttribute("href", dataStr);
		downloadAnchorNode.setAttribute("download", exportName + ".json");

		document.body.appendChild(downloadAnchorNode); // required for firefox

		downloadAnchorNode.click();
		downloadAnchorNode.remove();
	}
}
