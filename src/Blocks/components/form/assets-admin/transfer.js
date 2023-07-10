import { Utils } from "../assets/utilities";
import { State, ROUTES } from "../assets/state";

export class Transfer {
	constructor(options = {}) {
		this.utils = new Utils();
		this.state = new State();

		this.selector = options.selector;
		this.itemSelector = options.itemSelector;
		this.uploadSelector = options.uploadSelector;
		this.overrideExistingSelector = options.overrideExistingSelector;
		this.uploadConfirmMsg = options.uploadConfirmMsg;
	}

	init () {
		[...document.querySelectorAll(this.selector)].forEach((element) => {
			element.addEventListener('click', this.onClick, true);
		});

		[...document.querySelectorAll(`${this.itemSelector} input`)].forEach((element) => {
			element.addEventListener('click', this.onClickItem, true);
		});
	}

	// Handle form submit and all logic.
	onClick = (event) => {
		event.preventDefault();

		const formId = this.state.getFormIdByElement(event.target);
		const field = this.state.getFormFieldElementByChild(event.target);
		const type = field.getAttribute(this.state.getStateAttribute('migrationType'));

		const formData = new FormData();

		formData.append('type', type);

		if (type === 'import') {
			const { name } = document.querySelector(this.uploadSelector);

			const file = this.state.getStateElementCustom(name, formId)?.files?.[0];

			formData.append('upload', this.utils.getFileNameFromFileObject(file));
			formData.append('override', document.querySelector(`${this.overrideExistingSelector} input`).checked);

			confirm(this.uploadConfirmMsg);
		} else {
			formData.append('items', field.getAttribute(this.state.getStateAttribute('migrationExportItems')));
		}

		// Populate body data.
		const body = {
			method: 'POST',
			mode: 'same-origin',
			headers: {
				Accept: 'multipart/form-data',
				'X-WP-Nonce': this.state.getStateConfigNonce(),
			},
			body: formData,
			credentials: 'same-origin',
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		fetch(this.state.getRestUrl(ROUTES.TRANSFER), body)
			.then((response) => {
				return response.json();
			})
			.then((response) => {
				const {
					message,
					status,
					data,
				} = response;

				this.utils.setGlobalMsg(formId, message, status);

				if (status === 'success') {
					if (type === 'import') {
						setTimeout(() => {
							location.reload();
						}, 1000);
					} else {
						this.createFile(data.content, data.name);
					}
				}

				setTimeout(() => {
					this.utils.unsetGlobalMsg(formId);
				}, 6000);
			});
	};

	onClickItem = (event) => {
		const button = document.querySelector(`${this.state.getStateSelectorsField()}[${this.state.getStateAttribute('migrationType')}='export-forms']`);
		const items = button.getAttribute(this.state.getStateAttribute('migrationExportItems'));

		let output = items ? items.split(",") : [];

		const {
			value,
			checked,
		} = event.target;

		if (checked) {
			output.push(value);
		} else {
			output = output.filter((item) => item !== value);
		}

		button.setAttribute(this.state.getStateAttribute('migrationExportItems'), output);
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
