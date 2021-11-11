# Form

This document contains all actions/filters you can take on the frontend form submitting logic. Some filters are done using JavaScript and some using PHP.

# Scripts

You can manually trigger and initialize all JavaScript functions needed for the forms to run. We have exposed the global window object called `esForms`. Here you can find init function, selectors, events and much more.

## Reinit all forms JavaScript.
```js
window.esForms.init();
```

# Events JavaScript

Here are events that you can trigger using JavaScript to hook you custom logic in the process.

### Here are all available events:
* **esFormsBeforeFormSubmit** - Triggers after you submit a form, but before any logic is triggered.
* **esFormsAfterFormSubmit** - Triggers after the form has done the ajax response but before any logic is triggered.
* **esFormsAfterFormSubmitSuccessRedirect** - Triggers after the form has done the ajax response with redirect on success action.
* **esFormsAfterFormSubmitSuccess** - Triggers after the form has done the ajax response with default on success action.
* **esFormsAfterFormSubmitError** - Triggers after the form has done the ajax response with general error.
* **esFormsAfterFormSubmitErrorFatal** - Triggers after the form has done the ajax response with fatal error.
* **esFormsAfterFormSubmitErrorValidation** - Triggers after the form has done the ajax response with validation error.
* **esFormsAfterFormSubmitEnd** - Triggers after the form has done the ajax response and it is finished with the logic.
* **esFormsBeforeGtmDataPush** - Triggers before the GTM data is pushed.

## Code example one form:
```js
import domReady from '@wordpress/dom-ready';

domReady(() => {
	const form = document.querySelector('.js-es-block-form');

	form?.addEventListener('esFormsAfterFormSubmit', (event) => {
		// Do you logic here.
	});
});
```

## Code example multiple forms:
```js
import domReady from '@wordpress/dom-ready';

domReady(() => {
	const forms = document.querySelectorAll('.js-es-block-form');

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

# Filters

Here are filters that you can override form options using PHP.

## Changing the default success redirection wait time

This filter will override our default wait time once the form returns success and it is redirected. Te time is calculated in milliseconds. *Example: 1000ms = 1s*.

**Default value:**
```php
600 // 0.6 seconds.
```

**Filter:**
```php
// Provide custom redirection timeout.
add_filter('es_forms_form_js_redirection_timeout', [$this, 'getRedirectionTimeout']);

/**
 * Provide custom redirection timeout.
 *
 * @return string
 */
public function getRedirectionTimeout(): string
{
	return '1000'; // 1 seconds.
}
```

## Changing the default success hide global message wait time

This filter will override our default wait time before the global message is removed. The time is calculated in milliseconds. *Example: 1000ms = 1s*.

**Default value:**
```php
6000 // 6 seconds.
```

**Filter:**
```php
// Provide custom hide global message timeout.
add_filter('es_forms_form_js_hide_global_message_timeout', [$this, 'getHideGlobalMessageTimeout']);

/**
 * Provide custom hide global message timeout.
 *
 * @return string
 */
public function getHideGlobalMessageTimeout(): string
{
	return '10000'; // 10 seconds.
}
```
