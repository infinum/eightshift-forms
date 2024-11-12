/**
 * Enrichment class.
 */
export class Corvus {
	init(data) {
		const {
			url,
			params,
		} = data;

		// Create a form element.
		const form = document.createElement('form');
		form.method = 'POST';
		form.action = url;

		// Populate hidden fields with parameters
		Object.entries(params).forEach(([key, value]) => {
			const hiddenField = document.createElement('input');
			hiddenField.type = 'text';
			hiddenField.name = key;
			hiddenField.value = String(value);
			form.appendChild(hiddenField);
		});

		// Append form to body, submit it, then remove it
		document.body.appendChild(form);
		form.submit();
		document.body.removeChild(form);
	}
}
