/* global ajaxurl */

export class RegenerateDataTransients {
	constructor({
		selector,
		elements,
		nonceId,
	}) {
		this.selector = selector;
		this.elements = elements;
		this.nonceId = document.getElementById(nonceId);
	}

	initAll() {
		[...this.elements].forEach((element) => {
				this.init(element);
		});
	}

	init(element) {
		element.addEventListener('click', () => {
			const confirmAction = confirm('Are you sure?'); // eslint-disable-line no-alert
			if (confirmAction) {
				this.rebuild(element);
			}
		})
	}

	rebuild(element) {
		const data = new FormData();

		data.append('label', element.getAttribute('data-label'));
		data.append('type', element.getAttribute('data-type'));
		data.append('action', element.getAttribute('data-action'));
		data.append('nonce', this.nonceId.value);

		const body = {
			method: 'POST',
			mode: 'same-origin',
			headers: {
				Accept: 'application/json',
			},
			body: data,
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		fetch(ajaxurl, body)
			.then((response) => {
				return response.json();
			})
			.then((response) => {
				alert(response.data.msg); // eslint-disable-line no-alert
			})
			.catch((error) => {
				throw new Error(error);
			});
	}
}
