---
name: eightshift-forms-native-selector-token-contract
description: Use when customizing Eightshift Forms PHP Tailwind selector arrays for date/Choices controls or checking silent Flatpickr initialization failures.
---

# Enhanced-control selector tokens

Confirm the installed Forms version and inspect its enhanced-control consumer before changing selector arrays. The contracts below were verified in Forms 10.4.1.

- Ordinary PHP field class arrays can contain space-separated utility strings. Enhanced-control arrays passed directly to `classList.add(...entries)` cannot.
- Set `date.parts.picker` to individual tokens, for example `['[&_*]:transition-colors', '[&_*]:duration-300']`.
- Choices class-name arrays also need individual tokens. Preserve the plugin's structural group/state classes when replacing presentation classes; dropdown visibility depends on them.

## Calendar smoke

Use real plugin-rendered markup and compiled theme assets. Do not submit the form.

For every configured date variant, derive `formKey` from `window.esForms.state`, then inspect the named original input and its native `_flatpickr` instance:

1. `esForms.store.getStateElementLoaded(input.name, formKey) === true`.
2. `esForms.store.getStateElementCustom(input.name, formKey) === input._flatpickr`.
3. `input._flatpickr.altInput.id` is nonempty and equals the associated label's `htmlFor`.
4. The original input has no `id`, `tabIndex === -1`, and `aria-hidden="true"`.
5. Select a date with the actual calendar and compare the original input value with the plugin store value.

A visible calendar or a working date selection is insufficient: Flatpickr can swallow an exception midway through `onReady`, leaving loaded/custom-instance/ID setup incomplete.
