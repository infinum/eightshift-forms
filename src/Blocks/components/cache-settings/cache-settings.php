<?php

/**
 * Template for cache settings page in admin area.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;
$componentJsClass = $manifest['componentJsClass'] ?? '';

$cacheSettingsPageTitle = Components::checkAttr('cacheSettingsPageTitle', $attributes, $manifest);
$cacheSettingsSubTitle = Components::checkAttr('cacheSettingsSubTitle', $attributes, $manifest);
$cacheSettingsAjaxAction = Components::checkAttr('cacheSettingsAjaxAction', $attributes, $manifest);
$cacheSettingsTypes = Components::checkAttr('cacheSettingsTypes', $attributes, $manifest);
$nonceField = Components::checkAttr('nonceField', $attributes, $manifest);
?>


<h1><?php echo esc_html($cacheSettingsPageTitle); ?></h1>
<p><?php echo esc_html($cacheSettingsSubTitle); ?></p>

<?php foreach ($cacheSettingsTypes as $cacheType) { ?>
	<?php
		$name = $cacheType['name'] ?? '';
		$label = $cacheType['label'] ?? '';
		$desc = $cacheType['desc'] ?? '';
	?>
	<hr />
	<h2><?php echo esc_html($label); ?></h2>
	<p><?php echo esc_html($desc); ?></p>
	<button
		class="<?php echo esc_attr("{$componentJsClass} button") ?>"
		data-action="<?php echo esc_attr($cacheSettingsAjaxAction); ?>"
		data-type="<?php echo esc_attr($name); ?>"
		data-label="<?php echo esc_attr($label); ?>"
	>
	<?php
		/* translators: %s will be replaced with button label (string). */
		echo sprintf(__('Delete %s transient cache', 'eightshift-forms'), esc_html($label));
	?>
	</button>
	<br /><br />
<?php } ?>

<?php
echo $nonceField; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
