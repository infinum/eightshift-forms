import {
	setGlobalMsg,
	hideGlobalMsg,
} from './utilities';

export class Transfer {
	constructor(options) {
		this.selector = options.selector;
		this.formSelector = options.formSelector;
		this.itemSelector = options.itemSelector;
		this.uploadSelector = options.uploadSelector;
		this.overrideExistingSelector = options.overrideExistingSelector;
		this.uploadConfirmMsg = options.uploadConfirmMsg;

		this.transferRestUrl = options.transferRestUrl;
		this.globalMsgSelector = `${this.formSelector}-global-msg`;
	}

	init = () => {
		const elements = document.querySelectorAll(this.selector);

		if (elements.length) {
			[...elements].forEach((element) => {
				element.addEventListener('click', this.onClick, true);
			});
		}

		const items = document.querySelectorAll(`${this.itemSelector} input`);
			if (items.length) {
			[...items].forEach((element) => {
				element.addEventListener('click', this.onClickItem, true);
			});
		}
	};

	// Handle form submit and all logic.
	onClick = (event) => {
		event.preventDefault();

		const element = event.target;
		const type = element.getAttribute('data-type');

		const formData = new FormData();

		formData.append('type', type);
		formData.append('items', element.getAttribute('data-items'));

		if (type === 'import') {
			const upload = document.querySelector(this.uploadSelector);
			formData.append('upload', upload.files[0]);

			const existing = document.querySelector(`${this.overrideExistingSelector} input`);
			formData.append('override', existing.checked);

			confirm(this.uploadConfirmMsg);
		}

		// Populate body data.
		const body = {
			method: 'POST',
			mode: 'same-origin',
			headers: {
				Accept: 'multipart/form-data',
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
				setGlobalMsg(this.globalMsgSelector, response.message, response.status);

				if (response.code >= 200 && response.code <= 299) {

					if (type === 'import') {
						setTimeout(() => {
							location.reload();
						}, 1000);
					} else {
						this.createFile(response.data.content, response.data.name);
					}
				}

				setTimeout(() => {
					hideGlobalMsg(this.globalMsgSelector);
				}, 6000);
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
			output = output.filter((item) => item !== value);
		}

		button.disabled = !output.length;

		button.setAttribute('data-items', output);
	};

	createFile(data, exportName) {
		const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(data);

		const downloadAnchorNode = document.createElement('a');

		downloadAnchorNode.setAttribute("href", dataStr);
		downloadAnchorNode.setAttribute("download", exportName + ".json");

		document.body.appendChild(downloadAnchorNode); // required for Firefox browser

		downloadAnchorNode.click();
		downloadAnchorNode.remove();
	}
}
