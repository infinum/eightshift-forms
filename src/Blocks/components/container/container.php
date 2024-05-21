<?php

/**
 * Template for the Container component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$containerUse = Helpers::checkAttr('containerUse', $attributes, $manifest);

if (!$containerUse) {
	return;
}

$containerClass = Helpers::checkAttr('containerClass', $attributes, $manifest);
$containerContent = Helpers::checkAttr('containerContent', $attributes, $manifest);
$containerTag = Helpers::checkAttr('containerTag', $attributes, $manifest);

$additionalAttributes = $attributes['additionalAttributes'] ?? [];
?>

<<?php echo esc_attr($containerTag); ?>
	class="<?php echo esc_attr($containerClass); ?>"

	<?php
	foreach ($additionalAttributes as $key => $value) {
		if (!empty($key) && !empty($value)) {
			echo wp_kses_post(" {$key}='" . $value . "'");
		}
	}
	?>
>
	<?php
		// phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
		echo $containerContent;
	?>
</<?php echo esc_attr($containerTag); ?>>
