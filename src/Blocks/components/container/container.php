<?php

/**
 * Template for the Container component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$containerUse = Components::checkAttr('containerUse', $attributes, $manifest);

if (!$containerUse) {
	return;
}

$containerClass = Components::checkAttr('containerClass', $attributes, $manifest);
$containerContent = Components::checkAttr('containerContent', $attributes, $manifest);
$containerTag = Components::checkAttr('containerTag', $attributes, $manifest);

$additionalAttributes = $attributes['additionalAttributes'] ?? [];
?>

<<?php echo esc_attr($containerTag); ?>
	class="<?php echo esc_attr($containerClass); ?>"

	<?php
	foreach ($additionalAttributes as $key => $value) {
		if (!empty($key) && !empty($value)) {
			echo wp_kses_post("{$key}=" . $value . " ");
		}
	}
	?>
>
	<?php
		// phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
		echo $containerContent;
	?>
</<?php echo esc_attr($containerTag); ?>>
