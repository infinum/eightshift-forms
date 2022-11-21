/**
 * Custom component was used from this repo.
 *
 * @link https://github.com/goranalkovic-infinum/conditional-logic-repeater
 * @version 0.6.0
 *
 * @author Goran Alković
 */

import { conditionalLogicRepeater } from '@eightshift/web-components/conditional-logic-repeater';

export class ConditionalTags {
	constructor(options) {
		this.fieldSelector = options.fieldSelector;
	}

	init = () => {
		conditionalLogicRepeater;

		const elements = document.querySelectorAll(this.fieldSelector);

		// Loop all forms on the page.
		[...elements].forEach((element) => {

			const id = element.getAttribute('data-id');
			const input = document.querySelector(`#${id}`);

			element.addEventListener('es-conditional-logic-repeater-update', (event) => {
				const {
					enabled,
					behavior,
					logic,
					conditions,
				} = event.detail;

				if (conditions[0].field !== '') {
					const data = [
						behavior,
						logic,
						conditions.map((item) => {
							if (item.field !== '') {
								return [
									item.field,
									item.comparison,
									item.value,
								];
							}

							return [];
						}).filter((e) => e)
					];

					input.value = JSON.stringify(data);
				}

				if (!enabled) {
					input.value = '';
				}
			});
		});
	};
}
