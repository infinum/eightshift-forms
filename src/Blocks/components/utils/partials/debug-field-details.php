<?php

/**
 * Template for the Utils debug field details Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

if (UtilsGeneralHelper::isEightshiftFormsAdminPages()) {
	return;
}

if (!UtilsDeveloperHelper::isDeveloperModeActive()) {
	return;
}

$fieldManifest = Helpers::getComponent('field');

$componentClass = Helpers::classnames([
	UtilsHelper::getStateSelector('field'),
	Helpers::selector(true, $fieldManifest['componentClass'], 'debug'),
]);

$output = [
	'name' => $attributes['name'] ?? '',
];

if (!array_filter(array_values($output))) {
	return;
}

?>

<div class="<?php echo esc_attr($componentClass); ?>">
	<?php
	foreach ($output as $key => $value) {
		if (!$value) {
			continue;
		}

		$keyName = ucfirst($key);

		echo esc_html("{$keyName}: {$value}");
	}
	?>
</div>
