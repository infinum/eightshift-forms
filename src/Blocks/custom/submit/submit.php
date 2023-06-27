<?php

/**
 * Template for the Submit Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Hooks\Filters;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$button = '';

$ssr = $attributes['submitSubmitServerSideRender'] ?? false;

// With this filder you can override default submit component and provide your own.
if (!$ssr) {
	$filterNameComponent = Filters::getFilterName(['block', 'submit', 'component']);
	if (has_filter($filterNameComponent)) {
		$button = apply_filters($filterNameComponent, [
			'value' => $attributes['submitSubmitValue'] ?? '',
			'isDisabled' => $attributes['submitSubmitFieldDisabled'] ?? '',
			'attributes' => $attributes,
		]);
	}
}

echo Components::render(
	'submit',
	Components::props('submit', $attributes, [
		'submitButtonComponent' => $button
	])
);
