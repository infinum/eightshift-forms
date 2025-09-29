# Change Log for the Eightshift Forms

All notable changes to this project will be documented in this file.

This projects adheres to [Semantic Versioning](https://semver.org/) and [Keep a CHANGELOG](https://keepachangelog.com/).

## [8.5.0]

### Added

- Option to force locale for the form.
- Form field error will hide once user interacts with the field.
- Admin settings for global settings timeout.
- `getFormDetails` helper function.

### Fixed

- Top Admin bar issue in admin.
- Top Admin bar issue for PHP 8.5+.

## [8.4.1]

### Fixed

- Small bugs fixes.

## [8.4.0]

### Added

- Option to exclude files from confirmation email.
- Additional checks for files uploaded and files sent to the backend because of async uploads.

### Changed

- File names are now send to email and integration as it was uploaded.
- Updated dependencies to the latest version.

### Fixed

- Broken file upload for importing forms in admin.
- Activity logs now sending files also.

## [8.3.0]

### Added

- Option to disable scroll to field on focus for A11y.

### Fixed

- Broken sorting for entries listing.

## [8.2.1]

### Fixed

- Broken validation for the forms with steps.

## [8.2.0]

### Fixed

- Conditional tags logic for the forms.
- Result output item logic for the forms.

### Added

- New filter for adding additional addon blocks.

## [8.1.4]

### Added

- Option to skip form reset on success.

### Fixed

- Single submit logic for the forms on rating and input number field.

## [8.1.3]

### Fixed

- Talentlyft fix for custom answer.

## [8.1.2]

### Fixed

- CloudFront header is now used for security feature.

## [8.1.1]

### Fixed

- Security feature now uses CloudFront header if enabled.
- Security feature now uses Cloudflare header if enabled.

## [8.1.0]

### Added

- `AbortSignal` support to Form submit, to prevent race conditions for continuos submit scenarios
- JS scroll to fields, steps and global messages now uses additional offset css variable that developer can use for adjusting the offset.
- Added all external integrations response codes to fallback settings.
- Better handling of data in the exceptions in JS.

## [8.0.3]

### Fixed

- Wrong data output from public API on mailer integration.
- Hidden select options are now properly handled.
- Broken API response on conditional tags form fields.

## [8.0.2]

### Fixed

- Public API endpoints protection.

## [8.0.1]

### Fixed

- Geolocation cookie is now set with secure flag.

## [8.0.0]

### Added

- Activity logs for all forms are now stored in the database.
- Activity logs are now auto-deleted after 30 days by default.
- Activity logs are can have multiple log levels and all responses can be stored and checked.
- All Cron jobs are not disabled if the feature is not enabled.
- Captcha is will retry if the response is is browser-error.

### Removed

- Manual import feature, this feature is now moved to activity logs listing.
- Forms-utils library is now removed from the project.

### Fixed

- Additional security checks for the form data.

### Changed

- All API endpoint are now returning the correct response headers based on the request type and status code.
- Fallback emails are now connected to the activity logs and differently outputted.

## [7.0.4]

### Fixed

- Wrong frontend style dependency handle.

## [7.0.3]

### Fixed

- Security IP function now uses CloudFront header if enabled.

## [7.0.2]

### Added

- Auto-delete entries with retention interval.

## [7.0.1]

### Added

- `esFormRenderForm` helper function now supports `formsVariation` attribute.

## [7.0.0]

### Updated

- Updated dependencies to the latest version.

### Changed

- All form field are now tab-able and you can pass the full flow without any mouse interaction
- Select/country/phone, choices are now updated to the latest version with better A11y.
- File field is now tab-able and can be used with only keyboard.
- Rating field is fully new and re-built with a different approach using radio buttons.
- Country and Phone fields can now be preselected from admin using country code
- Select field will now auto open a dropdown if you focus on it.
- Once you submit a form and you get an error, the form will scroll up to the first error (nothing new here), but what is new is that it will focus your cursor on the field so you can start typing (no necessary steps needed), or it will open a dropdown.
- All field have necessary aria tags.

### Added

- New compliance fields for Talentlyft integration.
- Workable integration now supports multiple tags based on the country code.
- Workable integration now supports multiple statuses.
- Talentlyft integration now supports multiple statuses.
- On conditional tags when field is hidden, the value will be reset to empty.

### Removed

- Read-only checkbox field.

## [6.9.0]

### Changed

- Updated dependencies to the latest version.
- Bundle flow to prebuild cache.
- Fixing all migration issues.

## [6.8.1]

### Added

- New entries listing for all entries in one place.

## [6.8.0]

### Added

- New CloudFront feature for geolocation.

## [6.7.4]

### Fixed

- Broken Conditional tags JSON output.

## [6.7.3]

### Fixed

- Result output item value can now be 0.

## [6.7.2]

### Fixed

- Broken deployment workflow.

## [6.7.1]

### Fixed

- Conditional logic for results and conditional tags.

## [6.7.0]

### Added

- Tab component now has no background option for settings.
- Multilingual support for validation labels both in admin and frontend.
- All frontend labels are now translatable
- All frontend labels AI translations for 15 available languages.
- Missing frontend facing labels.
- Fallback email will ignore keys are now displayed in the settings.

### Fixed

- If you have geolocation alternatives on some forms you can now turn off the geolocation feature and the output will not break.

### Removed

- Caching for Validation labels.

## [6.6.0]

### Changed

- Restructured the way how allowed blocks are handled and how form block limits work in WordPress 6.8+.

## [6.5.1]

### Added

- Pagination for fetching tags from NationBuilder integration.

## [6.5.0]

### Added

- New option on phone field to toggle what type of value to show on the frontend.
- Mailer sender conditional logic now supports negation.
- Choices.js for select now output 50 items when searching.

## [6.4.1]

### Fixed

- `esFormRenderForm` helper function bug.

## [6.4.0]

### Added

- Filter for loader component to be able to add svg icon for loading

### Changed

- Loader bar will no longer use loader svg but plain css loader with loading animation only triggered when needed.

## [6.3.3]

### Updated

- GitHub actions to the latest version.

## [6.3.2]

### Added

- new filter for modifying country list data set.

## [6.3.1]

### Fixed

- Sync issue when initially manually setting field state that should be able to change from the editor.
- Mailerlite sync issue with hidden field.

## [6.3.0]

### Added

- Option to have multiple recipients for the email based on the field value and conditional logic.

### Fixed

- Phone field will now reset to the initial value once submitted.

## [6.2.0]

### Changed

- Assets builder is now using Bun.

## [6.1.8]

### Fixed

- Input custom range field will not be outputted if the field type has been changed.

## [6.1.7]

### Fixed

- Issue with Result output submit reset shortcode.
- Forcing hidden mandatory css field to have important tag.

## [6.1.6]

### Change

- Broken `NationBuilder` integration will now send error if the email exists.

## [6.1.5]

### Fixed

- Broken `NationBuilder`integration key for email.

## [6.1.4]

### Fixed

- Broken `PayCek`integration.

## [6.1.3]

### Added

- Nationbuilder now supports tags.
- Range slider now supports custom input value with single submit option.

### Fixed

- Disabled button in admin.

## [6.1.2]

### Added

- Ability for range slider to have a custom input value on the side.

## [6.1.1]

### Fixed

- `NationBuilder` expired token issue.

## [6.1.0]

### Added

- `Success` and `Error` buttons for admin.
- New `NationBuilder` integration.
- `NationBuilder` cron job.
- OAuth implementation for `NationBuilder` integration and future integrations.
- `Mailer` can now send empty field values based on the admin settings.
- New filter `esFormsIntegrationsMailerBodyTemplate` for filtering HTML body of the email before sending.

### Changed

- `Clearbit` cron job internal logic.

### Removed

- Clearbit `setQueue` filter.
- HubSpot `getContactProperties` filter.
- HubSpot `postContactProperties` filter.

## [6.0.0]

### Fixed

- multiple integrations for new fields types.

### Removed

- Legacy variation data passed to the success redirect url.
- `useLegacyTnxPageFeature` filter.
- Function to load option value based on the config setting.

### Added

- New helper function for getting the field value `getParamValue`.

## [5.9.10]

### Fixed

- `Workable` integration fix for broken checkbox field
- Fixed sync for integration diff.

## [5.9.9]

### Added

- Ability to output file name to `email`, `redirect_url` and `entries`.

### Changed

- Internal way we handle params that can be an array.
- `mailer` is now faster due to the new way we process params.

## [5.9.8]

### Fixed

- Conditional tags inner logic.

## [5.9.7]

### Fixed

- Conditional tags for `select`, `select multiple`, `country` and `country multiple` fields.

## [5.9.6]

### Added

- `esFormsResetSteps` JS event.

## [5.9.5]

### Added

- Added Custom Name key to JS state.

### Fixed

- Custom name key will now be set correctly in the state.

## [5.9.4]

### Fixed

- `Airtable` integration fix for more than 100 records.

## [5.9.3]

### Fixed

- Broken form submission sending fallback email for success.

## [5.9.2]

### Fixed

- GTM data layer data is sent correctly now.
- `radio`, `checkbox` and `select-option` in editor now shows selected state.

## [5.9.1]

### Added

- public helper to update entry.

## [5.9.0]

### Changed

- internal simplification of the form php classes for integrations.
- `prePostParams` filter is now loaded in the abstract method and not in the integration client classes.
- All JavaScript events are now loaded on window.

### Added

- 4 new JavaScript events for enrichment.
- new JavaScript event for field change.
- new JavaScript event for after result output is processed.
- Payment forms will now have loading spinner on submit.
- Enrichment will setup the data in PHP earlier in the process so it can be used in filters and entries.
- Ability to ignore fallback email for specific key.

## [5.8.3]

### Fixed

- Mailer `tnx email` will now parse email response tags correctly.
- Form file field will now reset the value after the form is submitted.

## [5.8.2]

### Fixed

- form submission for phone bugfix.
- increment ID bugfix for length.

### Changed

- Corvus integration now supports API key per store ID.

### Added

- Corvus integration setting for setting IBAN and subscription values.
- Corvus integration order number will now use increment ID or entry ID based on the settings.
- Paycek integration order number will now use increment ID or entry ID based on the settings.
- Paycek now sends formId and order number as GET parameters for callback URL.

## [5.8.1]

### Changed

- location of increment settings and helpers.

## [5.8.0]

### Added

- `increment ID` option for entries so you can use them to track successful submissions or use it as an order number.
- `incrementId` key is now available in the email response tags.

### Changed

- `entries` listing now has the new listing page with better UX and table layout.

## [5.7.1]

### Added

- `prePostParams` filter on Corvus and Paycek integrations.

## [5.7.0]

### Changed

- Forms are now supporting multiple instances of the same form on the same page.
- `enrichment` prefill on URL now has totally new logic and ways or prefilling the fields.
- `JS` internal logic for setting up values on field.

### Added

- new country order list filter to provide a custom `priority` order for the countries.
- additional protection for checking if route is private or public for settings.
- `country` field now supports multiple values, min/max count validation and ability to prefill the field value.
- `phone` field now supports initial selected value both for country picker and number.
- `field` now support additional `suffix` content that will be displayed after the field input.
- `radio and checkbox` fields now support one `input` field that can be used as `other` option and this value will be sent with the main values.
- new `enrichment` prefill option to save all fields defined as `smart` and prefilled all forms that have that field.

### Removed

- ability for phone/country field to `sync` changes to the other field.

### Fixed

- broken checkboxes and radios if there are multiple items with the same name.
- missing email confirmation for `Corvus`, `Calculator` and `Paycek` integrations.
- success redirect url will no longer send empty encrypted value.

## [5.6.6]

### Added

- 2 new response tags for mailer `mailerTimestamp` and `mailerTimestampHuman`.
- ability for subject to process response tags.

### Changes

- Moments API will not fetch only 100 items per request.

## [5.6.5]

### Fixed

- Submit filter now uses correct attribute for disabled state.

## [5.6.4]

### Fixed

- Usage condition for Clearbit cron job.

## [5.6.3]

### Fixed

- Broken Use conditions on settings

## [5.6.2]

### Fixed

- Broken Talentlyft integration if we have custom fields.

## [5.6.1]

### Fixed

- Wrong filter callback for Debug options.

## [5.6.0]

### Added

- Prevention of multiple form submissions from the same user until the API response is processed.

### Changed

- Decoupled Clearbit integration from sync submit and moved it to job queue.

## [5.5.1]

### Added

- Req class on the form fields that are required.

## [5.5.0]

### Added

- Locations ajax for results output listing.
- Option to customize Date and Time fields Preview and Output format.
- Result Outputs can now be exported and imported using settings.
- Settings entries list now has better UX in settings.
- `getFormUsageLocation` helper now supports custom post type.
- HubSpot integration now works using `v3` API for all data except form submission as it is not supported.
- Corvus Pay integration now supports IBAN payments.
- TalentLyft integration now supports additional statuses.

### Fixed

- Input Range status variable will output max 2 decimals.
- Input Range settings pickers broken up/down buttons.
- Corvus Pay integration fix with `Require complete` option.

### Removed

- Any Server Side Render for the forms and results output.
- Result Output global settings option to setup custom frontend URL.

## [5.4.3]

### Added

- Req class on the form fields that are required.

## [5.4.2]

### Updated

- `@infinum/eightshift-forms-utils` to the latest version `3.0.7`.

### Fixed

- Language issues implemented in WP `6.7`.

## [5.4.1]

### Updated

- Admin JS and CSS scripts are now loaded only on the forms page.

## [5.4.0]

### Updated

- `@infinum/eightshift-forms-utils` to the latest version `3.0.6`.
- `brain/monkey` to the latest version `2.6.2`.
- `php-parallel-lint/php-parallel-lint` to the latest version `1.4.0`.
- `php-stubs/wordpress-stubs` to the latest version `6.6.2`.
- minimal PHP version to `8.3`.

## [5.3.2]

### Fixed

- Broken single submit feature on some integrations.

## [5.3.1]

### Fixed

- Limit Talentlyft integration to only `published` forms.

## [5.3.0]

### Added

- New Integration for `PayCek payment`.

## [5.2.0]

### Added

- New Integration for `CorvusPay`.
- New helper to get forms usage locations - `getFormUsageLocation`.

### Fixed

- Broken conditional tags for none form fields.

## [5.1.10]

### Added

- New feature to allow the form to be submitted only once per user.
- New feature to allow the form to be visible only when the user is logged in.

## [5.1.9]

### Added

- Mailer now supports few new email response keys: `mailerPostTitle`, `mailerPostUrl`, `mailerPostId`, `mailerFormId` and `mailerFormTitle`.

## [5.1.8]

### Fixed

- TalentLyft integration bugfix

## [5.1.7]

### Fixed

- TalentLyft integration custom fields not being saved correctly.

## [5.1.6]

### Fixed

- TalentLyft integration not displaying label for radios and checkboxes.

## [5.1.5]

### Fixed

- Wrong function type hint for `getCptLimits` function.

## [5.1.4]

### Fixed

- Fixing broken setting key for hide form.

## [5.1.3]

### Fixed

- Do not show the form in the admin settings link if the user is missing cap.

## [5.1.2]

### Fixed

- Wrong function type hint for `getCptLimits` function.

## [5.1.1]

### Changed

- Forms Custom post types will no longer appear in the search results using regular WP search query.
- Results output will no longer appear in the admin sidebar menu as a top-level item.
- Forms Custom post types will no longer be registered as a standard rest API endpoints for security reasons.

### Updated

- `@infinum/eightshift-forms-utils` to the latest version `3.0.4`.

### Added

- public helper `esFormsGetComponentsRender` now supports Tailwind classes.

## [5.1.0]

### Added

- New TalentLyft integration.

### Updated

- `@infinum/eightshift-forms-utils` to the latest version `3.0.3`.

## [5.0.10]

### Fixed

- Broken Mailchimp integration if only email is sent.

## [5.0.9]

### Added

- New way of handling Tailwind classes for forms.

### Fixed

- Rating field is now disabled if the parameter is set to true.
- Input number is now able to use standard tab navigation.
- Rating field now supports tab navigation.

### Removed

- Any unnecessary server side renders for the forms.

## [5.0.8]

### Added

- Additional Tailwind selectors for forms fields.
- Range field not supports progress CSS variable.

## [5.0.7]

### Added

- Implemented a new "value type" attribute to the country field, allowing for the dynamic definition of option values.
- Adapted the Countries class to support the new "value type" attribute.
- Filter to allow adding custom Tailwind classes to all form elements.

## [5.0.6]

### Fixed

- Bypass captcha flag now works correctly on all forms.

## [5.0.5]

### Added

- new integration filter `overridePostRequest` to override the post request data.
- new public helper `getFormsGetCountryList` to get the list of countries.

## [5.0.4]

### Fixed

- Result output configuration for missing link URL.

## [5.0.3]

### Fixed

- Added field validation to the output of the error msg.
- Additional fixes for legacy output data.

## [5.0.2]

### Added

- new integration filter `afterCustomResultOutputProcess`.

### Changed

- Removing copy for result output setting.

### Fixed

- JSON decode issue with the result output setting.

## [5.0.1]

### Changed

- Updated npm packages to the latest version.

## [5.0.0]

### Added

- Result output now supports single view and has link in the admin listing.
- Result output item now supports fetching data from encrypted URL.
- New filter `beforeSuccessResponse` for the forms.
- New variations setting and way of handing form data submission.
- Form fallback debug settings now supports original data and request url in the email.
- New email response keys `mailerSuccessRedirectUrl`, `mailerEntryId`, `mailerEntryUrl` that can be used in any email.
- New `esFormsROISF` shortcode that can be used inside the result output item to show the form again.
- Feature to hide form after submission.

### Removed

- Multiple filter not in use anymore: `successRedirectVariation`, `successRedirectVariationOptions`, `preResponseAddonData`, `preResponseSuccessRedirectData`, `resultOutputItems`, `resultOutputParts`.

### Changed

- Conditional tags logic is now transferred to utils so it can be used in other features.
- Forms specific settings that should be available only after form is submitted is not fetched on the form success submission and not hardcoded in the DOM.
- Result output is now styled with the new design for better UX.
- Result output now supports comparator for the conditional logic.
- Form submission is now handled with new and optimized way of handling data and multiple features.
- `esFormsROIP` shortcode now supports variation data submission.

### Fixed

- Entries listing is no longer breaking if the value is to long.
- Stylelint errors from the new setup.
- All form fields are now additionally checked for correct data structure on submission.

## [4.0.7]

### Fixed

- removed unnecessary email fallback.

## [4.0.6]

### Updated

- `Eightshift-forms-utils` to the latest version `2.0.2`.
- result output item now supports conditional logic with both single and duplicate values.

### Added

- email fallback now supports request IP sent from the server, the data is anonymized.
- new form setting that will hide form once it is submitted with success.
- new `esFormsROISF` shortcode that can be used inside the result output item to show the form again.

## [4.0.5]

### Updated

- `Eightshift-forms-utils` to the latest version `2.0.1`.

### Fixed

- Countries get list pulled the data from the wrong manifest cache file.

## [4.0.4]

### Fixed

- Updating .js files for the new eslint rules.

### Updated

- `@infinum/eightshift-frontend-libs` to the latest version `12.1.5`.

## [4.0.3]

### Changed

- Countries list is now loaded from php file and not from the manifest json.

### Removed

- Countries list from cache.

### Added

- Translations for the countries list for 14 languages.

## [4.0.2]

### Fixed

- Geolocation added additional check for sent headers.

## [4.0.1]

### Fixed

- Changing the main Manifest cache function for setting up initial cache.

## [4.0.0]

### Updated

- `Eightshift-forms-utils` to the latest version `2.0.0`.
- `@infinum/eightshift-libs` to the latest version `8.0.0`.
- `@infinum/eightshift-frontend-libs` to the latest version `12.0.0`.

### Removed

- All `Data` are not loaded from utils lib.
- Top bar no longer supports listing all forms items for faster loading.
- `src/Exception/MissingFilterInfoException.php` because it is not used anymore.

### Added

- New `Caching` service for manifest data and new options to flush cache from the settings.
- Input number validation for allowed keys.
- WP Rocket cache options settings.

### Fixed

- Custom post type labels are not translatable.

### Changed

- Filter `script_dependency_theme` is now `script_dependency_theme_captcha`.

## [3.1.13]

### Fixed

- `Airtable` integration fix for more than 100 records.

## [3.1.12]

### Fixed

- Geolocation will now work even if forms is configured but global setting is disabled.
- HubSpot Client fixes for additional params broken after security fixes on the Chrome browser.

### Added

- Two new filters for encryption and decryption of the data.

## [3.1.11]

### Changed

- Updated the `@infinum/eightshift-forms-utils` to the latest version.

### Added

- Two new filters for encryption and decryption of the data.

## [3.1.10]

### Fixed

- Airtable integration now supports multiple select fields.
- Moments form validation of input fields limiting the number of characters to 1000.
- Calculator will no longer break reCaptcha validation.
- Single submit with global msg disabled will no longer output the empty success message.
- Single submit now supports input type number.

### Added

- Security feature now supports calculator rate limiting separate setting.

## [3.1.9]

### Removed

- Reverted changes for enrichment prefill hidden fields.

## [3.1.8]

### Added

- New filer `es_forms_block_form_custom_class_selector` for adding custom class to the form block.
- New type attribute to the `esFormsROIP` shortcode.

## [3.1.7]

### Updated

- Strauss to the latest version.

### Added

- Ability for checkboxes and radios when used as a "show as" option to select to have placeholder text.
- Check for the enrichment if locale storage is available.

### Fixed

- Google reCaptcha will now throw an error if not set up correctly.
- Multiple JS errors when array or objects are missing.
- Google reCaptcha will now send a fallback email if there is an issue with the validation only.
- Cleaned up unnecessary data from fallback e-mails.
- Enrichment will now only prefill visible fields.

## [3.1.6]

### Updated

- Eightshift-forms-utils to the latest version `1.3.6`.
- Updated create entries helper to check if the database table exists.
- Updated validator to load email tld manifest data from cache.

### Fixed

- Fix the shortcode for displaying the output of the calculations.

## [3.1.5]

### Fixed

- reCaptcha will now output the correct message on the frontend in any case.

### Added

- reCaptcha will now send fallback email if there is an issue with the validation.
- Security feature now supports option to ignore IPs.

### Updated

- Eightshift-forms-utils to the latest version `1.3.5`.
- License updated.
- Deploy scripts.

## [3.1.4]

### Fixed

- single submit on calculation forms will no longer reset the form on submit.

## [3.1.3]

### Fixed

- single submit on calculation forms will now send all form data on submit.
- range current state will now update on initial load.
- issue with not syncing correctly form fields with escape characters.

## [3.1.2]

### Fixed

- issue with multiple select fields not being saved correctly in Moments integration.
- single submit logic for the forms.

## [3.1.1]

### Fixed

- issue with multiple select fields not being saved correctly.

## [3.1.0]

### Added

- `range` field for the forms.
- `singleSubmit` attribute on all fields to allow only one submit per form to be used as calculation form.
- `Result output` custom post type.
- Blocks for the result output.
- Calculator form type and necessary filters.
- Forms can now use `single submit` option to send data without submit button.
- Setting for single form to hide global msg on submit success.
- `esFormsRangeCurrent` shortcode to output the current value of the range field.
- `esFormsROIP` shortcode to output the result output part.

### Changed

- `Input` fields now output correct types for e-mail and URL fields, so the experience on mobile devices should be much better.
- Admin listing URLs can now support additional types.
- All icons are now used from utils lib.

### Fixed

- JS errors when missing data.
- Broken URLs for admin listing when using custom post types.

### Removed

- Unnecessary options in the `rating` field.

## [3.0.6]

### Fixed

- issue with multiple select fields not being saved correctly in Moments integration.

## [3.0.5]

### Fixed

- File upload validation will no longer break if API returns invalid type.
- issue with multiple select fields not being saved correctly.

## [3.0.4]

### Added

- New support page in the settings for easier access to server configuration issues.
- Options to send empty fields to entires.

### Changed

- Block Editor select option will auto close on selection.

### Fixed

- File upload validation will no longer break the API returns invalid type.
- Validation pattern will no longer break if value is array.

## [3.0.3]

### Added

- Ability to send fallback email if there has been validation error for `validationMissingMandatoryParams`, `validationFileUploadProcessError` and `validationSecurity`.
- New fallback processing validation fallback function.

### Changed

- The security validation feature will no longer trigger count files and step requests.
- Entries now have ability to save empty fields based on the settings.

### Fixed

- Typo for fallback function.
- Custom mailer now supports saving entires.

## [3.0.2]

### Added

- New `dynamic` block and component to allow dynamic form fields.
- Integration form picker now supports clear option.
- Airtable integration now supports dynamic, connected tables.
- Airtable integration now supports multiple select fields.
- Mailer now supports custom response tag `mailerSuccessRedirectUrl`.
- Locations use the URL to the settings page and top admin bar.

### Fixed

- Integration sync fix when you unset attributes set in the manifest as default.
- Forms JS breaking on the frontend when API response returns JSON but, the response is not an object.
- SCSS linter errors.

### Updated

- `@infinum/eightshift-frontend-libs` to the latest version.
- `@infinum/eightshift-forms-utils` to the latest version.
- `husky` to the latest version.
- `webpack` to the latest version.
- `webpack-cli` to the latest version.
- `reactflow` to the latest version.

## [3.0.0]

### Added

- Extracting for helpers to new library for easier usage across add-ons.
- Creating new addon options for new potential projects.
- Forms listing filter will allow you to filter form by type.
- Forms settings will now follow the used theme admin color scheme.
- New Dashboard settings page where you can toggle options you want to use in the project.
- New checkbox toggle state for the settings pages.
- Conditional logic for all integration forms.
- Conditional tags for all forms.
- Multi-step multiflow forms can now be created.
- Over 30 new filters for the forms and integrations.
- Automatic diff between integration forms an internal forms.
- Documentation page in the settings.
- Migrations page where you can migrate the data for the options that were changed in the major versions.
- Debug options with multiple options for debugging.
- Forms can now store entires in the database.
- New form fields for country, rating, date, time, multi-select, and phone number with country picker.
- New security options for the forms.
- Google reCaptcha now supports invisible reCaptcha and business version.
- Top admin bar option for easier access to the settings and forms.
- Ability to delete/sync/duplicate multiple forms at once from admin listing.
- Forms now supports multiple languages using WPML plugin.
- Forms now supports RTL languages.
- Forms now supports Cloudflare setup.
- Import/Export forms and settings for easier migration between projects.
- New integrations for Pipedrive, Jira, Moments, Airtable.
- Enrichment now supports remembering the last used field values from local storage or URL.
- Email validation now supports top level domain validation.
- Forms are now faster and more secure.

### Changed

- Visual styling for all settings pages with tabs, better copy, and a lot of UX/UI improvements.
- Fallback emails are no longer in the troubleshooting class but as a standalone class.
- Your forms will now show only integrations in the settings set in the Block editor.
- Geolocation will now load forms faster and even work with the cookieless setup with caching options.
- Forms are now compatible with latest WordPress version and PHP 8+.

### Fixed

- Optimized loading of all settings pages.
- Issue with attributes escaping on the PHP8+.

### Removed

- `ES_DEVELOP_MODE` constant because you can configure everything from the settings page.

## [2.2.0]

### Changed

- HubSpot internal logic for api auth. Switching from API Key to Private App.

## [2.1.0]

### Fixed

- Issue with the hidden field and legacy items in the integration settings.

### Changed

- MailerLite default status to active

## [2.0.1]

### Fixed

- Hubspot fix so the input hidden is not displayed on the frontend.

## [2.0.0]

### Added

- custom form params are now in one place and used as an enum for PHP and JS.
- server errors will no longer produce a fatal error on the form but will output the message to the user, which is also translatable.
- option to remove all unnecessary custom params set on the form before the final integration post, so we don't send unnecessary stuff.
- new admin setting sidebar title for grouping the sections
- new troubleshooting section that contains debugging options: skip validation, form reset on submit, output log.
- new fallback email fields for all integrations; this will send an email with all details if there is an integration issue.
- `es_forms_geolocation_db_location` filter to specify the location of the geolocation database in your project.
- `es_forms_geolocation_phar_location ` filter to specify the location of the geolocation database in your project.
- new filter `es_forms_troubleshooting_output_log` provides the ability to output internal logs to an external source.
- new toggle button in troubleshooting settings will enable you to skip captcha validation.
- geolocation license copy
- new sortable option to all integration fields.

### Changed

- all JS global variables for frontend and backend are now using the same name.
- internal custom field for actions is now called es-form-action.
- filter for setting http request from `httpRequestArgs ` to `httpRequestTimeout` because it is used only to set timeout.
- Greenhouse integration from `wp_remote_post ` to regular `Curl` because of the issues while sending the attachments. You are now only limited on the amount of memory your server can send.
- form will now throw an error if form-ID or type is missing in the request.
- all remote requests are now outputted via helper for easier and more predictable output.
- converting from internal geolocation logic to libs abstract class logic.
- updating libs.
- `ES_GEOLOCATION` global constant to `ES_GEOLOCATION_IP`.

### Fixed

- all wrong text domains are changed from `eightshift-form` to `eightshift-forms`.
- Active campaign body was set wrong and was not working.
- Active campaign setting info copy for setting api key and url.
- customSuccess label is now translatable from settings.
- validator will now skip the input type hidden because there is no need for that.
- Greenhouse timeout issue on large files.
- wrong mime type for google docs file format `.docx`.
- internal filter naming for functions

### Removed

- `ES_DEVELOP_MODE_SKIP_VALIDATION ` because it is used from admin now.
- `ES_LOG_MODE ` because it is used from admin now.
- `es_forms_geolocation_user_location` filter.

## [1.4.0]

### Fixed

- preselected values for custom select.
- added additional corrections for localStorage.
- missing attribute from component to form block manifest.json.
- updating libs and frontend libs.
- fixing loading of js.
- fixing way the settings are passed to the js.
- fixing linting issues.

### Removed

- removing unnecessary style and scripts.

### Added

- new field hidden attributes for hiding fields from dom.
- hidden field for hubspot integration.
- preselected value field for hubspot integration.
- enabled field for hubspot integration.
- new custom form type used to provide custom form action location.
- options in forms block to define action.
- option to send form submit to external url if form action is set.
- new tracking class for storing url tags and detecting tags from get param.
- new option to store url tracking tags to localStorage for later usage.
- new filters for tracking.
- local and global file upload allowed types.

## [1.3.0]

### Fixed

- logic for scroll to top and scroll to first error.
- Mailchimp integration merge fields.

### Changed

- Logic behind the form Js initialization with the option to avoid domReady.

### Added

- Method to remove all event listeners on demand.
- New event when all event listeners are removed.
- Filter for updating http_request_args.
- Better internal logging for integrations.

## [1.2.4]

### Added

- Option to provide checkbox unchecked value.
- New filter to allow filtering of the formDataTypeSelector attribute during form component renders.

### Fixed

- Greenhouse integration checkbox true/false unchecked value.

## [1.2.3]

### Fixed

- Geolocation hook condition to be able to disable on filter.

## [1.2.2]

### Fixed

- Internal build process for GH actions.

## [1.2.1]

### Fixed

- Internal links to support WP multisite.

## [1.2.0]

### Added

- passing get parameters to the backend to process and get what we need.
- New Greenhouse field that gets data from the get parameter and pass it to the api.

### Fixed

- Broken validation for file type.
- Validation for input type to detect the type and validate accordingly.

## [1.1.1]

### Fixed

- Option to show WP-CLI command.
- Mailchimp integration total number of list items to show.

## [1.1.0]

### Added

- Option to use string templates in mailer subject and other fields.

## [1.0.0]

- Initial production release.

[8.5.0]: https://github.com/infinum/eightshift-forms/compare/8.4.1...8.5.0
[8.4.1]: https://github.com/infinum/eightshift-forms/compare/8.4.0...8.4.1
[8.4.0]: https://github.com/infinum/eightshift-forms/compare/8.3.0...8.4.0
[8.3.0]: https://github.com/infinum/eightshift-forms/compare/8.2.1...8.3.0
[8.2.1]: https://github.com/infinum/eightshift-forms/compare/8.2.0...8.2.1
[8.2.0]: https://github.com/infinum/eightshift-forms/compare/8.1.4...8.2.0
[8.1.4]: https://github.com/infinum/eightshift-forms/compare/8.1.3...8.1.4
[8.1.3]: https://github.com/infinum/eightshift-forms/compare/8.1.2...8.1.3
[8.1.2]: https://github.com/infinum/eightshift-forms/compare/8.1.1...8.1.2
[8.1.1]: https://github.com/infinum/eightshift-forms/compare/8.1.0...8.1.1
[8.1.0]: https://github.com/infinum/eightshift-forms/compare/8.0.3...8.1.0
[8.0.3]: https://github.com/infinum/eightshift-forms/compare/8.0.2...8.0.3
[8.0.2]: https://github.com/infinum/eightshift-forms/compare/8.0.1...8.0.2
[8.0.1]: https://github.com/infinum/eightshift-forms/compare/8.0.0...8.0.1
[8.0.0]: https://github.com/infinum/eightshift-forms/compare/7.0.4...8.0.0
[7.0.4]: https://github.com/infinum/eightshift-forms/compare/7.0.3...7.0.4
[7.0.3]: https://github.com/infinum/eightshift-forms/compare/7.0.2...7.0.3
[7.0.2]: https://github.com/infinum/eightshift-forms/compare/7.0.1...7.0.2
[7.0.1]: https://github.com/infinum/eightshift-forms/compare/7.0.0...7.0.1
[7.0.0]: https://github.com/infinum/eightshift-forms/compare/6.9.0...7.0.0
[6.9.0]: https://github.com/infinum/eightshift-forms/compare/6.8.1...6.9.0
[6.8.1]: https://github.com/infinum/eightshift-forms/compare/6.8.0...6.8.1
[6.8.0]: https://github.com/infinum/eightshift-forms/compare/6.7.4...6.8.0
[6.7.4]: https://github.com/infinum/eightshift-forms/compare/6.7.3...6.7.4
[6.7.3]: https://github.com/infinum/eightshift-forms/compare/6.7.2...6.7.3
[6.7.2]: https://github.com/infinum/eightshift-forms/compare/6.7.1...6.7.2
[6.7.1]: https://github.com/infinum/eightshift-forms/compare/6.7.0...6.7.1
[6.7.0]: https://github.com/infinum/eightshift-forms/compare/6.6.0...6.7.0
[6.6.0]: https://github.com/infinum/eightshift-forms/compare/6.5.1...6.6.0
[6.5.1]: https://github.com/infinum/eightshift-forms/compare/6.5.0...6.5.1
[6.5.0]: https://github.com/infinum/eightshift-forms/compare/6.4.1...6.5.0
[6.4.1]: https://github.com/infinum/eightshift-forms/compare/6.4.0...6.4.1
[6.4.0]: https://github.com/infinum/eightshift-forms/compare/6.3.3...6.4.0
[6.3.3]: https://github.com/infinum/eightshift-forms/compare/6.3.2...6.3.3
[6.3.2]: https://github.com/infinum/eightshift-forms/compare/6.3.1...6.3.2
[6.3.1]: https://github.com/infinum/eightshift-forms/compare/6.3.0...6.3.1
[6.3.0]: https://github.com/infinum/eightshift-forms/compare/6.2.0...6.3.0
[6.2.0]: https://github.com/infinum/eightshift-forms/compare/6.1.8...6.2.0
[6.1.8]: https://github.com/infinum/eightshift-forms/compare/6.1.7...6.1.8
[6.1.7]: https://github.com/infinum/eightshift-forms/compare/6.1.6...6.1.7
[6.1.6]: https://github.com/infinum/eightshift-forms/compare/6.1.5...6.1.6
[6.1.5]: https://github.com/infinum/eightshift-forms/compare/6.1.4...6.1.5
[6.1.4]: https://github.com/infinum/eightshift-forms/compare/6.1.3...6.1.4
[6.1.3]: https://github.com/infinum/eightshift-forms/compare/6.1.2...6.1.3
[6.1.2]: https://github.com/infinum/eightshift-forms/compare/6.1.1...6.1.2
[6.1.1]: https://github.com/infinum/eightshift-forms/compare/6.1.0...6.1.1
[6.1.0]: https://github.com/infinum/eightshift-forms/compare/6.0.0...6.1.0
[6.0.0]: https://github.com/infinum/eightshift-forms/compare/5.9.10...6.0.0
[5.9.10]: https://github.com/infinum/eightshift-forms/compare/5.9.9...5.9.10
[5.9.9]: https://github.com/infinum/eightshift-forms/compare/5.9.8...5.9.9
[5.9.8]: https://github.com/infinum/eightshift-forms/compare/5.9.7...5.9.8
[5.9.7]: https://github.com/infinum/eightshift-forms/compare/5.9.6...5.9.7
[5.9.6]: https://github.com/infinum/eightshift-forms/compare/5.9.5...5.9.6
[5.9.5]: https://github.com/infinum/eightshift-forms/compare/5.9.4...5.9.5
[5.9.4]: https://github.com/infinum/eightshift-forms/compare/5.9.3...5.9.4
[5.9.3]: https://github.com/infinum/eightshift-forms/compare/5.9.2...5.9.3
[5.9.2]: https://github.com/infinum/eightshift-forms/compare/5.9.1...5.9.2
[5.9.1]: https://github.com/infinum/eightshift-forms/compare/5.9.0...5.9.1
[5.9.0]: https://github.com/infinum/eightshift-forms/compare/5.8.3...5.9.0
[5.8.3]: https://github.com/infinum/eightshift-forms/compare/5.8.2...5.8.3
[5.8.2]: https://github.com/infinum/eightshift-forms/compare/5.8.1...5.8.2
[5.8.1]: https://github.com/infinum/eightshift-forms/compare/5.8.0...5.8.1
[5.8.0]: https://github.com/infinum/eightshift-forms/compare/5.7.1...5.8.0
[5.7.1]: https://github.com/infinum/eightshift-forms/compare/5.7.0...5.7.1
[5.7.0]: https://github.com/infinum/eightshift-forms/compare/5.6.6...5.7.0
[5.6.6]: https://github.com/infinum/eightshift-forms/compare/5.6.5...5.6.6
[5.6.5]: https://github.com/infinum/eightshift-forms/compare/5.6.4...5.6.5
[5.6.4]: https://github.com/infinum/eightshift-forms/compare/5.6.3...5.6.4
[5.6.3]: https://github.com/infinum/eightshift-forms/compare/5.6.2...5.6.3
[5.6.2]: https://github.com/infinum/eightshift-forms/compare/5.6.1...5.6.2
[5.6.1]: https://github.com/infinum/eightshift-forms/compare/5.6.0...5.6.1
[5.6.0]: https://github.com/infinum/eightshift-forms/compare/5.5.1...5.6.0
[5.5.1]: https://github.com/infinum/eightshift-forms/compare/5.5.0...5.5.1
[5.5.0]: https://github.com/infinum/eightshift-forms/compare/5.4.3...5.5.0
[5.4.3]: https://github.com/infinum/eightshift-forms/compare/5.4.2...5.4.3
[5.4.2]: https://github.com/infinum/eightshift-forms/compare/5.4.1...5.4.2
[5.4.1]: https://github.com/infinum/eightshift-forms/compare/5.4.0...5.4.1
[5.4.0]: https://github.com/infinum/eightshift-forms/compare/5.3.2...5.4.0
[5.3.2]: https://github.com/infinum/eightshift-forms/compare/5.3.1...5.3.2
[5.3.1]: https://github.com/infinum/eightshift-forms/compare/5.3.0...5.3.1
[5.3.0]: https://github.com/infinum/eightshift-forms/compare/5.2.0...5.3.0
[5.2.0]: https://github.com/infinum/eightshift-forms/compare/5.1.10...5.2.0
[5.1.10]: https://github.com/infinum/eightshift-forms/compare/5.1.9...5.1.10
[5.1.9]: https://github.com/infinum/eightshift-forms/compare/5.1.8...5.1.9
[5.1.8]: https://github.com/infinum/eightshift-forms/compare/5.1.7...5.1.8
[5.1.7]: https://github.com/infinum/eightshift-forms/compare/5.1.6...5.1.7
[5.1.6]: https://github.com/infinum/eightshift-forms/compare/5.1.5...5.1.6
[5.1.5]: https://github.com/infinum/eightshift-forms/compare/5.1.4...5.1.5
[5.1.4]: https://github.com/infinum/eightshift-forms/compare/5.1.3...5.1.4
[5.1.3]: https://github.com/infinum/eightshift-forms/compare/5.1.2...5.1.3
[5.1.2]: https://github.com/infinum/eightshift-forms/compare/5.1.1...5.1.2
[5.1.1]: https://github.com/infinum/eightshift-forms/compare/5.1.0...5.1.1
[5.1.0]: https://github.com/infinum/eightshift-forms/compare/5.0.10...5.1.0
[5.0.10]: https://github.com/infinum/eightshift-forms/compare/5.0.9...5.0.10
[5.0.9]: https://github.com/infinum/eightshift-forms/compare/5.0.8...5.0.9
[5.0.8]: https://github.com/infinum/eightshift-forms/compare/5.0.7...5.0.8
[5.0.7]: https://github.com/infinum/eightshift-forms/compare/5.0.6...5.0.7
[5.0.6]: https://github.com/infinum/eightshift-forms/compare/5.0.5...5.0.6
[5.0.5]: https://github.com/infinum/eightshift-forms/compare/5.0.4...5.0.5
[5.0.4]: https://github.com/infinum/eightshift-forms/compare/5.0.3...5.0.4
[5.0.3]: https://github.com/infinum/eightshift-forms/compare/5.0.2...5.0.3
[5.0.2]: https://github.com/infinum/eightshift-forms/compare/5.0.1...5.0.2
[5.0.1]: https://github.com/infinum/eightshift-forms/compare/5.0.0...5.0.1
[5.0.0]: https://github.com/infinum/eightshift-forms/compare/4.0.7...5.0.0
[4.0.7]: https://github.com/infinum/eightshift-forms/compare/4.0.6...4.0.7
[4.0.6]: https://github.com/infinum/eightshift-forms/compare/4.0.5...4.0.6
[4.0.5]: https://github.com/infinum/eightshift-forms/compare/4.0.4...4.0.5
[4.0.4]: https://github.com/infinum/eightshift-forms/compare/4.0.3...4.0.4
[4.0.3]: https://github.com/infinum/eightshift-forms/compare/4.0.2...4.0.3
[4.0.2]: https://github.com/infinum/eightshift-forms/compare/4.0.1...4.0.2
[4.0.1]: https://github.com/infinum/eightshift-forms/compare/4.0.0...4.0.1
[4.0.0]: https://github.com/infinum/eightshift-forms/compare/3.1.13...4.0.0
[3.1.13]: https://github.com/infinum/eightshift-forms/compare/3.1.12...3.1.13
[3.1.12]: https://github.com/infinum/eightshift-forms/compare/3.1.11...3.1.12
[3.1.11]: https://github.com/infinum/eightshift-forms/compare/3.1.10...3.1.11
[3.1.10]: https://github.com/infinum/eightshift-forms/compare/3.1.9...3.1.10
[3.1.9]: https://github.com/infinum/eightshift-forms/compare/3.1.8...3.1.9
[3.1.8]: https://github.com/infinum/eightshift-forms/compare/3.1.7...3.1.8
[3.1.7]: https://github.com/infinum/eightshift-forms/compare/3.1.6...3.1.7
[3.1.6]: https://github.com/infinum/eightshift-forms/compare/3.1.5...3.1.6
[3.1.5]: https://github.com/infinum/eightshift-forms/compare/3.1.4...3.1.5
[3.1.4]: https://github.com/infinum/eightshift-forms/compare/3.1.3...3.1.4
[3.1.3]: https://github.com/infinum/eightshift-forms/compare/3.1.2...3.1.3
[3.1.2]: https://github.com/infinum/eightshift-forms/compare/3.1.1...3.1.2
[3.1.1]: https://github.com/infinum/eightshift-forms/compare/3.1.0...3.1.1
[3.1.0]: https://github.com/infinum/eightshift-forms/compare/3.0.6...3.1.0
[3.0.6]: https://github.com/infinum/eightshift-forms/compare/3.0.5...3.0.6
[3.0.5]: https://github.com/infinum/eightshift-forms/compare/3.0.4...3.0.5
[3.0.4]: https://github.com/infinum/eightshift-forms/compare/3.0.3...3.0.4
[3.0.3]: https://github.com/infinum/eightshift-forms/compare/3.0.2...3.0.3
[3.0.2]: https://github.com/infinum/eightshift-forms/compare/3.0.0...3.0.2
[3.0.0]: https://github.com/infinum/eightshift-forms/compare/2.2.0...3.0.0
[2.2.0]: https://github.com/infinum/eightshift-forms/compare/2.1.0...2.2.0
[2.1.0]: https://github.com/infinum/eightshift-forms/compare/2.0.1...2.1.0
[2.0.1]: https://github.com/infinum/eightshift-forms/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/infinum/eightshift-forms/compare/1.4.0...2.0.0
[1.4.0]: https://github.com/infinum/eightshift-forms/compare/1.3.0...1.4.0
[1.3.0]: https://github.com/infinum/eightshift-forms/compare/1.2.4...1.3.0
[1.2.4]: https://github.com/infinum/eightshift-forms/compare/1.2.3...1.2.4
[1.2.3]: https://github.com/infinum/eightshift-forms/compare/1.2.2...1.2.3
[1.2.2]: https://github.com/infinum/eightshift-forms/compare/1.2.1...1.2.2
[1.2.1]: https://github.com/infinum/eightshift-forms/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/infinum/eightshift-forms/compare/1.1.1...1.2.0
[1.1.1]: https://github.com/infinum/eightshift-forms/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/infinum/eightshift-forms/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/infinum/eightshift-forms/releases/tag/1.0.0
