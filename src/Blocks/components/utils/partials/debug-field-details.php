<?php

/**
 * Template for the Utils debug field details Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

if (UtilsGeneralHelper::isEightshiftFormsAdminPages()) {
	return;
}

$fieldManifest = Components::getComponent('field');

$componentClass = Components::classnames([
	UtilsHelper::getStateSelector('field'),
	Components::selector(true, $fieldManifest['componentClass'], 'debug'),
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
