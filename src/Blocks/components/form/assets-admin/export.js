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

	onClick = (event) => {
		event.preventDefault();

		if (this.isDisableButton(event.target)) {
			return;
		}

		this.submit(event.target);
	};

	async submit(target) {
		const formData = new FormData();

		const formId = target.getAttribute(this.state.getStateAttribute('formId'));
		const type = target.getAttribute(this.state.getStateAttribute('exportType'));

		formData.append('formId', formId);
		formData.append('type', type);
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
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		try {
			const response = await fetch(this.state.getRestUrl('export'), body);
			const parsedResponse = await response.json();

			const { message, status, data } = parsedResponse;

			const exportContent = data?.[this.state.getStateResponseOutputKey('adminExportContent')];

			if (exportContent) {
				const csv = this.downloadCSVFromJson(JSON.parse(exportContent));
				this.createDownloadLink('export.csv', csv);
			}

			this.utils.hideLoader(this.FORM_ID);
			this.utils.setGlobalMsg(this.FORM_ID, message, status);

			setTimeout(() => {
				this.utils.unsetGlobalMsg(this.FORM_ID);
			}, 6000);
		} catch ({ name, message }) {
			if (name === 'AbortError') {
				return;
			}

			throw new Error(this.utils.formSubmitResponseError(formId, 'adminExport', name, message));
		}
	}

	isDisableButton(element) {
		return element.classList.contains(this.state.getStateSelector('isDisabled'));
	}

	downloadCSVFromJson(arrayOfJson) {
		const replacer = (key, value) => (value === null ? '' : value);
		const header = this.getCSVHeader(arrayOfJson);
		let csv = arrayOfJson.map((row) => header.map((fieldName) => (Object.hasOwn(row, fieldName) ? JSON.stringify(row[fieldName], replacer) : '""')).join(','));
		csv.unshift(header.join(','));

		return csv.join('\r\n');
	}

	/**
	 * Build the CSV header from the keys of all rows, not just the first one.
	 *
	 * Empty optional fields are not stored, so rows can have different keys.
	 * A key missing from earlier rows is inserted right after its preceding key in the row where it first appears,
	 * keeping the original field order. Keys that no row has are never added.
	 *
	 * Columns are kept in a linked list (key => next key), so every insert is O(1)
	 * and the whole build stays linear in the number of keys, even for entries with many distinct fields.
	 *
	 * @param {Array<Object>} arrayOfJson Rows to export.
	 *
	 * @returns {Array<string>} Ordered list of unique column names.
	 */
	getCSVHeader(arrayOfJson) {
		const nextKey = new Map();
		let firstKey = null;

		arrayOfJson.forEach((row) => {
			let previousKey = null;

			Object.keys(row).forEach((key) => {
				if (!nextKey.has(key)) {
					if (previousKey === null) {
						nextKey.set(key, firstKey);
						firstKey = key;
					} else {
						nextKey.set(key, nextKey.get(previousKey));
						nextKey.set(previousKey, key);
					}
				}

				previousKey = key;
			});
		});

		const header = [];

		for (let key = firstKey; key !== null; key = nextKey.get(key)) {
			header.push(key);
		}

		return header;
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
