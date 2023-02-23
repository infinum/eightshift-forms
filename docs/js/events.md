# JavaScript events

Here are events that you can trigger using JavaScript to hook you custom logic in the process.

### Here are all available events:
* **esFormsBeforeFormSubmit** - Triggers after you submit a form, but before any logic is triggered. Hook: `Form element`. 
* **esFormsAfterFormSubmit** - Triggers after the form has done the ajax response but before any logic is triggered. Hook: `Form element`.
* **esFormsAfterFormSubmitSuccessRedirect** - Triggers after the form has done the ajax response with redirect on success action. Hook: `Form element`.
* **esFormsAfterFormSubmitSuccess** - Triggers after the form has done the ajax response with default on success action. Hook: `Form element`.
* **esFormsAfterFormSubmitReset** - Triggers after the form has done and filed values are cleared. Hook: `Form element`.
* **esFormsAfterFormSubmitError** - Triggers after the form has done the ajax response with general error. Hook: `Form element`.
* **esFormsAfterFormSubmitErrorFatal** - Triggers after the form has done the ajax response with fatal error. Hook: `Form element`.
* **esFormsAfterFormSubmitErrorValidation** - Triggers after the form has done the ajax response with validation error. Hook: `Form element`.
* **esFormsAfterFormSubmitEnd** - Triggers after the form has done the ajax response and it is finished with the logic. Hook: `Form element`.
* **esFormsAfterFormEventsClear** - Triggers after the form removes all event listeners. Hook: `Form element`.
* **esFormsBeforeGtmDataPush** - Triggers before the GTM data is pushed. Hook: `Form element`.
* **esFormsJsLoaded** - Triggers after all JS is loaded and ready to be used in the forms script. This event can be used when manually triggering forms javascript from your project. Hook: `window`.
* **esFormsJsFormLoaded** - Triggers after each JS is loaded and ready to be used in the forms script. This event can be used when manually triggering form javascript from your project. Hook: `Form element`.
* **esFormsAfterCaptchaInit** - Triggers if global init load captcha is active and after the captcha returns a response from the API.. Hook: `window`.

### Code example one form: 
```js
function initForms() {
	if (!window?.esForms) {
		return;
	}

	const {
		utils: {
			formSelector,
			EVENTS,
		},
	} = window.esForms;

	window.addEventListener(
		EVENTS.FORMS_JS_LOADED,
		() => {
			const form = document.querySelector(formSelector);

			form?.addEventListener('esFormsAfterFormSubmit', (event) => {
				// Do you logic here.
			});
		},
		{
			once: true
		}
	);
}

initForms();
```

### Code example multiple forms:
```js
function initForms() {
	if (!window?.esForms) {
		return;
	}

	const {
		utils: {
			formSelector,
			EVENTS,
		},
	} = window.esForms;

	window.addEventListener(
		EVENTS.FORMS_JS_LOADED,
		() => {
			const forms = document.querySelectorAll(formSelector);

			if (forms.length) {
				[...forms].forEach((form) => {
					form.addEventListener('esFormsAfterFormSubmit', (event) => {
						// Do you logic here.
					});
				});
			}
		},
		{
			once: true
		}
	);
}

initForms();
```

## esFormsBeforeGtmDataPush

This events will trigger **only** if your form has tracking event attribute set in the form settings and will search for all `data-tracking` attributes. If you want to add custom fields keep in mind to add this attribute. Like this example:

```php
$data[] = [
	'component' => 'input',
	'inputType' => 'hidden',
	'inputName' => 'inputName',
	'inputValue' => '<your-key-value>',
	'inputAttrs' => [
		'data-tracking' => '<your-key-name>'
	]
];
```

by default tracking js will search for the field value but in the case of select field you can provide additional data attributes that will force the tracking JS to submit label instead.

```php
$data[] = [
	'component' => 'select',
	'selectName' => 'selectName',
	'selectValue' => '<your-key-value>',
	'selectAttrs' => [
		'data-tracking' => '<your-key-name>',
		'data-tracking-select-label' => 'true',
	]
];
```

To provide this custom attributes you can use hooks in the PHP to change the fields.
