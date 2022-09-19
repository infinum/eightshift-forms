export class ConditionalTags {
	constructor(options) {
		// Selectors.
		this.formSelector = options.formSelector;

		// Data
		this.data = this.parseData(options.data);
	}

	// Init all actions.
	init = () => {
		console.log(this.formSelector);
		console.log(this.data);

		for (const [key, value] of Object.entries(this.data)) {
			const item = document.querySelector(`${this.formSelector} #${key}`);

			if (!item) {
				continue;
			}

			const {
				action,
				logic,
				rules
			} = value;

			if (action === 'show') {
				item.style.display = 'none';
			}

			console.log(item);
		}
		
		// const elements = document.querySelectorAll(this.formSelector);

		// // Loop all forms on the page.
		// [...elements].forEach((element) => {
		// 	console.log(element);
			
		// });
	}

	// Prepare data.
	parseData(data) {

		const newData = JSON.parse(data);

		const output = {};

		for (const [key, value] of Object.entries(newData)) {
			output[key] = {
				'action': value[0],
				'logic': value[1],
				'rules': value[2].map((innerItem) => {
					return {
						'id': innerItem[0],
						'operator': innerItem[1],
						'value': innerItem[2],
					}
				})
			}
		}

		return output;
	}
}
