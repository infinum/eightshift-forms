# Manual triggering JavaScript

You can manually trigger and initialize all JavaScript functions needed for the forms to run. We have exposed the global window object called `esForms`. Here you can find init function, selectors, events and much more.

If you manually trigger initialization please make sure you disable JavaScript init in the forms global settings.

> Please use this as much as possible so if we change something in our implementation your code will not break.

## Re-init all forms JavaScript.
```js
window.esForms.initAll();
```

### Code example for manual init:
This will manually re-init all JS for the forms, you can use this option in combination with the global setting to disable auto-init of JS on page load.

```js
function initForms() {
	if (!window?.esForms) {
		return;
	}

	const {
		utils: {
			formSelector,
			EVENTS,
		}
	} = window.esForms;

	window.addEventListener(
		EVENTS.FORMS_JS_LOADED,
		() => {
			const forms = document.querySelectorAll(formSelector);

			// Check if form selector exists and if init function is available.
			if (('initAll' in window.esForms) && forms.length) {
				window.esForms.initAll();
			}
		},
		{
			once: true
		}
	);
}

initForms();
```

