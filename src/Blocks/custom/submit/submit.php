<?php

/**
 * Template for the Submit Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$button = '';

// With this filder you can override default submit component and provide your own.
$filterNameComponent = UtilsHooksHelper::getFilterName(['block', 'submit', 'component']);
if (has_filter($filterNameComponent)) {
	$button = apply_filters($filterNameComponent, [
		'value' => $attributes['submitSubmitValue'] ?? '',
		'isDisabled' => $attributes['submitSubmitFieldDisabled'] ?? '',
		'attributes' => $attributes,
	]);
}

echo Helpers::render(
	'submit',
	Helpers::props('submit', $attributes, [
		'submitButtonComponent' => $button,
		'twSelectorsData' => FormsHelper::getTwSelectorsData($attributes),
	])
);
