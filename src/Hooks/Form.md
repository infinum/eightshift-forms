# Form

This document contains all actions/filters you can take on the frontend form submitting logic. Some filters are done using JavaScript and some using PHP.

# Scripts

You can manually trigger and initialize all JavaScript functions needed for the forms to run. We have exposed the global window object called `esForms`. Here you can find init function, selectors, events and much more.

**Please use this as much as possible so if we change something in our implementation your code will not break.**

## Re-init all forms JavaScript.
```js
window.esForms.init();
```

### Code example for manual init:
This will manually re-init all JS for the forms, you can use this option in combination with the global setting to disable auto-init of JS on page load.

```js
window.addEventListener('load', function () {
	if (!window?.esForms) {
		return;
	}

	const {
		formSelector
	} = window?.esForms;

	window?.esForms.init();
});
```

# Events JavaScript

Here are events that you can trigger using JavaScript to hook you custom logic in the process.

### Here are all available events:
* **esFormsBeforeFormSubmit** - Triggers after you submit a form, but before any logic is triggered.
* **esFormsAfterFormSubmit** - Triggers after the form has done the ajax response but before any logic is triggered.
* **esFormsAfterFormSubmitSuccessRedirect** - Triggers after the form has done the ajax response with redirect on success action.
* **esFormsAfterFormSubmitSuccess** - Triggers after the form has done the ajax response with default on success action.
* **esFormsAfterFormSubmitReset** - Triggers after the form has done and filed values are cleared.
* **esFormsAfterFormSubmitError** - Triggers after the form has done the ajax response with general error.
* **esFormsAfterFormSubmitErrorFatal** - Triggers after the form has done the ajax response with fatal error.
* **esFormsAfterFormSubmitErrorValidation** - Triggers after the form has done the ajax response with validation error.
* **esFormsAfterFormSubmitEnd** - Triggers after the form has done the ajax response and it is finished with the logic.
* **esFormsAfterFormEventsClear** - Triggers after the form removes all event listeners.
* **esFormsBeforeGtmDataPush** - Triggers before the GTM data is pushed.

### Code example one form:
```js
window.addEventListener('load', function () {
		if (!window?.esForms) {
		return;
	}

	const {
		formSelector
	} = window?.esForms;

	const form = document.querySelector(formSelector);

	form?.addEventListener('esFormsAfterFormSubmit', (event) => {
		// Do you logic here.
	});
});
```

### Code example multiple forms:
```js
window.addEventListener('load', function () {
	if (!window?.esForms) {
		return;
	}

	const {
		formSelector
	} = window?.esForms;

	const forms = document.querySelectorAll(formSelector);

	if (!forms.length) {
		return;
	}

	[...forms].forEach((form) => {
		form.addEventListener('esFormsAfterFormSubmit', (event) => {
			// Do you logic here.
		});
	});
});
```

## esFormsBeforeGtmDataPush

This events will trigger **only** if your form has tracking event attribute set in the form settings and will search for all `data-tracking` attributes. If you want to add custom fields keep in mind to add this attribute. Like this example:

```php
$data[] = [
	'component' => 'input',
	'inputType' => 'hidden',
	'inputId' => 'inputId',
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
	'selectId' => 'selectId',
	'selectName' => 'selectName',
	'selectValue' => '<your-key-value>',
	'selectAttrs' => [
		'data-tracking' => '<your-key-name>',
		'data-tracking-select-label' => 'true',
	]
];
```

To provide this custom attributes you can use hooks in the PHP to change the fields.
