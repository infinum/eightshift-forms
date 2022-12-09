# Testing Integrations - Users

> All available integrations can be found in the `Eightshift Forms > Global settings > Dashboard`.

* [] - User should be able to open each integration form and submit a valid form data.

## Errors

* [] - If there is an error user should receive an error msg under the form field and a global msg field on top of the form.
* [] - If there form validation is success but integration returns an error, user should get a user-friends msg in the global msg bar. All error labels can be found [here](https://github.com/infinum/eightshift-forms/blob/develop/src/Labels/Labels.php).
* [] - If the there is an error a default msg should be displayed from the code. `Form global settings > Validation > Messages`. These settings can't be changed per form, it applies to all forms.
* [] - If the there is an error and a global setting has a different msg set, that msg should be displayed. `Form global settings > Validation > Messages`. These settings cant be changed per form, it applies to all forms.

## Success

* [] - If the user submits everything correct and the integration returns success msg, the success msg should be displayed in the global msg bar.
* [] - If the there is an success a default msg should be displayed from the code. `Form settings > Validation > Messages`. These settings can't be changed globally, it applies to an individual form.
* [] - If the there is an success and a form setting has a different msg set, that msg should be displayed. `Form settings > Validation > Messages`. These settings can't be changed globally, it applies to an individual form.

## Success redirect
`Form settings > General`

* [] - If the from has redirect url set in form settings on success it should be redirected to that url. `Form settings > General > Submit > After submit redirect URL.`

## Tracking events
`Form settings > General > Tracking`

* [] - If the form returns and error GTM event should not be sent.
* [] - If the form returns success the GTM event should not be sent if the tracking event name is not set.  > Tracking event name.`
* [] - If the form returns success the GTM event should be sent. You can check by opening a console after before and after the submit and type `window.dataLayer` to compare if the event is sent. Also check in the GTM for the event data.

## Google reCaptcha
`Form global settings > Captcha`

* [] - Form should validate Google reCaptcha before submitting the form details if the feature is enabled in the settings. To check this open an `inspector > network tab > XHR`. After the form submit there should be `form-submit-captcha` in the list.
* [] - Form should not output additional query if captcha failed. To check this lower the threshold limit in settings `Form global settings > Captcha > Advanced` to `1`.
* [] - Form should make an additional query if captcha returns success depending on the form type.

## Geolocation
`Form global settings > Geolocation`

* [] - Form should use geolocation if the feature is enabled in the settings.
* [] - Form should show the default form if no geolocation data is set in the forms block.
* [] - Form should show the default form if geo location condition is not met.
* [] - Form should show the alternative form if geo location condition is met.
* [] - Form should show the firs alternative form if multiple geo location conditions are met at the same time.
* [] - Form should show work with all previous conditions if WP Rocket cache is used and configuration is valid.
* [] - Form should show work with all previous conditions if WP Rocket cache is purged and rebuilt.

## Enrichment
`Form global settings > Enrichment`

* [] - Form should send enrichment data if the feature is enabled in the settings.
* [] - If form enrichment is enabled you will get the `es-form-storage` key in the request if the conditions are valid. `Inspector > Network tab > XHR > form-submit-<integration> > Request tab`.
* `es-form-storage` valid conditions:
	* [] - `Form global settings > Enrichment` - populated tags in the settings and expiration time set.
	* [] - Local storage exists with key `es-storage` with the users populated params.
	* [] - Local storage key is populated only from the enabled tags set in the settings and got from the url get parameters.
	* [] - Local storage has not expired depending on the expiration key set in the settings.
* [] - If `es-form-storage` key is sent via API the data is submitted to the external integration with success. This depend on the projects map filter settings.
* [] - If features is disabled `es-form-storage` should never be sent nor the url params stored in the localStorage.
* [] - If the localStorage expiration time has passed it should be deleted.

## General

`Form global settings > General > Fields`
* [] - Form should display custom "fancy" select field if the setting is off.
* [] - Form should display default select field if the setting is on.
* [] - Form should display custom "fancy" textarea field if the setting is off. Should expand on new line.
* [] - Form should display default textarea field if the setting is on.
* [] - Form should display custom "fancy" file field if the setting is off.
* [] - Form should display default file field if the setting is on.

`Form global settings > General > Fields`
* [] - Form should use all forms default styles if the setting is off.
* [] - Form should disable all forms default styles and use custom project styles if the setting is on.
* [] - Form should use all forms default JS scripts if the setting is off.
* [] - Form should disable all forms default JS scripts and use custom project scripts if the setting is on.
* [] - Form should use forms auto init of the JS scripts if the setting is off.
* [] - Form should disable auto init for all form and use custom project scripts if the setting is on.

`Form global settings > General > Actions`
* [] - Form should scroll to first error if submit returns an error and setting is off.
* [] - Form should not scroll to first error if submit returns an error and setting is on.
* [] - Form should scroll to global msg if submit returns an success and setting is off.
* [] - Form should not scroll to global msg if submit returns an success and setting is on.

## Cache
`Form global settings > Cache`

* [] - Form should disable integration internal cache and pull new data from the external source. To check this open integrations on forms and check if date has changed under the form selector. `Form settings > <Integration> > Form selector`.
* [] - Cache button should only delete that integration cache.

