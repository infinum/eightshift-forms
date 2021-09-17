export class Form {
	constructor(options) {
		this.formSubmitRestApiUrl = options.formSubmitRestApiUrl;

		this.formSelector = options.formSelector;
		this.errorSelector = `${this.formSelector}-error-msg`;
	}

	init = () => {
		const elements = document.querySelectorAll(this.formSelector);

		console.log(this.formSelector);
		

		[...elements].forEach((element) => {
			this.onFormSubmit(element);
		});
	}

	onFormSubmit = (element) => {
		element.addEventListener('submit', (event) => {
			event.preventDefault();

			const body = {
				method: element.getAttribute('method'),
				mode: 'same-origin',
				headers: {
					Accept: 'multipart/form-data',
				},
				body: this.formatFormData(event),
				credentials: 'same-origin',
				redirect: 'follow',
				referrer: 'no-referrer',
			};

			fetch(this.formSubmitRestApiUrl, body)
			.then((response) => {
				return response.json();
			})
			.then((response) => {

				// this.hideLoader(event);
	
				if (response.code === 200) {
	
					// Send GTM.
					// this.gtmSubmit(event);
	
					// If success, redirect or output msg.
					if (Object.prototype.hasOwnProperty.call(response.data, 'redirectSuccess') && response.data.redirectSuccess) {
						// window.location.href = this.$form.attr('data-success-redirect');
					} else {
						// this.successActions();
						console.log(response);
						
					}
				}
	
				// Normal errors.
				if (response.status === 'error') {
					// this.setGlobalMsg(response.data.message, 'error');
				}
	
				// Fatal errors, trigger bugsnag.
				if (response.status === 'error_fatal') {
					// this.setGlobalMsg(response.data.message, 'error');
					throw new Error(JSON.stringify(response));
				}
	
				// Validate fields.
				if (response.status === 'error_fields') {
					this.outputErrors(response.validation, element);
				}
			});
		});
	}

	formatFormData = (event) => {
		const items = event.target.elements;

		const formData = new FormData();

		for (const key in items) {
			if (Object.prototype.hasOwnProperty.call(items, key) && typeof items[key] === 'object') {
				const item = items[key];

				const data = {
					value: item.value,
					type: item.type,
					data: item?.dataset,
				};

				// if (item.type === 'select-one') {
				// 	data.selectLabel = Object.values(item.options).find((o) => o.value === data.value).text;
				// }

				// // Change value to checked.
				// if (item.type === 'checkbox') {
				// 	data.value = item.checked;
				// }

				// // Output files.
				// if (item.type === 'file') {
				// 	formData.append(`${item.name}_file`, item.files[0]);
				// }

				// Output all fields.
				formData.append(item.name, JSON.stringify(data));
			}
		}

		return formData;
	}

	outputErrors = (fields, element) => {
		for (const key in fields) {
			if (Object.prototype.hasOwnProperty.call(fields, key)) {
				const value = fields[key];

				const item = element.querySelector(`${this.errorSelector}[data-id="${key}"]`);

				if (item !== null) {
					item.innerHTML(value);
				}
			}
		}
	}
}
