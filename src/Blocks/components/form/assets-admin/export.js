import { ROUTES } from "../assets/state";

export class Export {
	constructor(options = {}) {
		/** @type {import('../assets/utils').Utils} */
		this.utils = options.utils;
		/** @type {import('../assets/state').State} */
		this.state = this.utils.getState();

		this.FORM_ID = 0;

		this.selector = options.selector;
		this.itemsSelector = options.itemsSelector;
	}

	init() {
		[...document.querySelectorAll(this.selector)].forEach((element) => {
			element.addEventListener('click', this.onClick, true);
		});
	}

	// Handle form submit and all logic.
	onClick = (event) => {
		event.preventDefault();

		if (this.isDisableButton(event.target)) {
			return;
		}

		const formData = new FormData();

		const field = this.state.getFormFieldElementByChild(event.target);
		const formId = field?.getAttribute(this.state.getStateAttribute('formId'));

		formData.append('formId', formId);
		formData.append('ids', document.querySelector(this.itemsSelector)?.getAttribute(this.state.getStateAttribute('bulkItems')));

		this.utils.showLoader(formId);

		// Populate body data.
		const body = {
			method: 'POST',
			mode: 'same-origin',
			headers: {
				Accept: 'application/json',
				'X-WP-Nonce': this.state.getStateConfigNonce(),
			},
			body: formData,
			credentials: 'same-origin',
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		fetch(this.state.getRestUrl(ROUTES.EXPORT), body)
			.then((response) => {
				this.utils.formSubmitErrorContentType(response, 'bulk', null);
				return response.json();
			})
			.then((response) => {
				const {
					message,
					status,
					data,
				} = response;

				if (data?.output) {
					const csv = this.downloadCSVFromJson(JSON.parse(data?.output));
					this.createDownloadLink('export.csv', csv);
				}

				this.utils.hideLoader(this.FORM_ID);
				this.utils.setGlobalMsg(this.FORM_ID, message, status);

				setTimeout(() => {
					this.utils.unsetGlobalMsg(this.FORM_ID);
				}, 6000);
			});
	};

	isDisableButton(element) {
		return element.classList.contains(this.state.getStateSelector('isDisabled'));
	}

	downloadCSVFromJson(arrayOfJson) {
		const replacer = (key, value) => value === null ? '' : value;
		const header = Object.keys(arrayOfJson[0]);
		let csv = arrayOfJson.map((row) => header.map(fieldName => JSON.stringify(row[fieldName], replacer)).join(','));
		csv.unshift(header.join(','));

		return csv.join('\r\n');
	}

	createDownloadLink(filename, csv) {
		let link = document.createElement('a');
		link.setAttribute('href', 'data:text/csv;charset=utf-8,%EF%BB%BF' + encodeURIComponent(csv));
		link.setAttribute('download', filename);
		link.style.visibility = 'hidden';
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
	}
}
