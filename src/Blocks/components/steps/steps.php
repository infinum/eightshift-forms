<?php

/**
 * Template for the Steps Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';

$stepsTitle = Helpers::checkAttr('stepsTitle', $attributes, $manifest);
$stepsContent = Helpers::checkAttr('stepsContent', $attributes, $manifest);

$stepsClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
]);

if (!$stepsContent) {
	return;
}

?>
<div class="<?php echo esc_attr($stepsClass); ?>">
	<?php if ($stepsTitle) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__title"); ?>">
			<?php echo esc_html($stepsTitle); ?>
		</div>
	<?php } ?>

	<ul class="<?php echo esc_attr("{$componentClass}__steps"); ?>">
		<?php foreach ($stepsContent as $step) { ?>
			<li class="<?php echo esc_attr("{$componentClass}__step"); ?>">
				<span class="<?php echo esc_attr("{$componentClass}__step-inner"); ?>">
					<?php echo wp_kses_post($step); ?>
				</span>
			</li>
		<?php } ?>
	</ul>
</div>
