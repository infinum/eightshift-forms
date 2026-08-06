<?php

/**
 * Template for the Utils debug field details Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\DeveloperHelpers;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

if (GeneralHelpers::isEightshiftFormsAdminPages()) {
	return;
}

if (!DeveloperHelpers::isDeveloperModeActive()) {
	return;
}

$fieldManifest = Helpers::getComponent('field');

$additionalClass = $attributes['additionalClass'] ?? '';
$componentClass = $fieldManifest['componentClass'] ?? '';

$debugClasses = Helpers::clsx([
	$additionalClass,
	Helpers::bem($componentClass, 'debug'),
]);

$output = [
	'name' => $attributes['name'] ?? '',
];

if (!array_filter(array_values($output))) {
	return;
}

?>

<div class="<?php echo esc_attr($debugClasses); ?>" role="none" aria-hidden="true" tabindex="-1">
	<?php
	foreach ($output as $key => $value) {
		if (!$value) {
			continue;
		}

		$keyName = ucfirst($key);

		echo esc_html($value);
	}
	?>
</div>
