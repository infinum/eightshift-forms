
# Change Log for the Eightshift Forms
All notable changes to this project will be documented in this file.

This projects adheres to [Semantic Versioning](https://semver.org/) and [Keep a CHANGELOG](https://keepachangelog.com/).

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
- new fieldhidden attributes for hiding fields from dom.
- hidden field for hubspot integration.
- preselected value field for hubspot integration.
- enabled field for hubspot integration.
- new custom form type used to provide custom form action location.
- options in forms block to define action.
- option to send form submit to external url if form action is set.
- new tracking class for storing url tags and detecting tags from get param.
- new option to store url tracking tags to local storage for later usage.
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
