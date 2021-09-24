<?php

/**
 * Template for settings form option page.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\Helpers\Components;

$globalManifest = Components::getManifest(dirname(__DIR__, 2));
$manifest = Components::getManifest(__DIR__);
$manifestSection = Components::getManifest(dirname(__DIR__, 1) . '/settings-section');

echo Components::outputCssVariablesGlobal($globalManifest); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

$componentClass = $manifest['componentClass'] ?? '';
$sectionClass = $manifestSection['componentClass'] ?? '';

$settingsDetailsPageTitle = Components::checkAttr('settingsDetailsPageTitle', $attributes, $manifest);
$settingsDetailsSubTitle = Components::checkAttr('settingsDetailsSubTitle', $attributes, $manifest);
$settingsDetailsBackLink = Components::checkAttr('settingsDetailsBackLink', $attributes, $manifest);
$settingsDetailsFormId = Components::checkAttr('settingsDetailsFormId', $attributes, $manifest);
$settingsDetailsLink = Components::checkAttr('settingsDetailsLink', $attributes, $manifest);
$settingsDetailsData = Components::checkAttr('settingsDetailsData', $attributes, $manifest);
$settingsDetailsType = Components::checkAttr('settingsDetailsType', $attributes, $manifest);

$sidebar = $settingsDetailsData['sidebar'] ?? [];
$form = $settingsDetailsData['form'] ?? '';

$layoutClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($sectionClass, $sectionClass),
	Components::selector($sectionClass, $sectionClass, '', 'with-sidebar'),
]);

?>

<div class="<?php echo \esc_attr($layoutClass); ?>">
	<div class="<?php echo \esc_attr("{$sectionClass}__sidebar"); ?>">
		<div class="<?php echo \esc_attr("{$sectionClass}__section"); ?>">
			<div class="<?php echo \esc_attr("{$sectionClass}__content {$sectionClass}--reset-spacing"); ?>">
				<?php if ($sidebar) { ?>
					<ul class="<?php echo \esc_attr("{$sectionClass}__menu"); ?>">
						<?php foreach ($sidebar as $item) { ?>
							<?php
							$label = $item['label'] ?? '';
							$value = $item['value'] ?? '';
							$icon = $item['icon'] ?? '';
							?>
							<li class="<?php echo \esc_attr("{$sectionClass}__menu-item"); ?>">
								<a
									href="<?php echo esc_url("{$settingsDetailsLink}&type={$value}"); ?>"
									class="<?php echo \esc_attr("{$sectionClass}__menu-link " . Components::selector($value === $settingsDetailsType, $sectionClass, 'menu-link', 'active')); ?>"
								>
									<span class="<?php echo \esc_attr("{$sectionClass}__menu-link-icon dashicons {$icon}"); ?> "></span>
									<?php echo esc_html($label); ?>
								</a>
							</li>
						<?php } ?>
					</ul>
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="<?php echo \esc_attr("{$sectionClass}__main"); ?>">
		<div class="<?php echo \esc_attr("{$sectionClass}__section"); ?>">
			<div class="<?php echo \esc_attr("{$sectionClass}__heading"); ?>">
				<div class="<?php echo \esc_attr("{$sectionClass}__heading-title"); ?>">
					<?php echo esc_html($settingsDetailsPageTitle); ?>
				</div>

				<div class="<?php echo \esc_attr("{$sectionClass}__actions"); ?>">
					<a href="<?php echo esc_url($settingsDetailsBackLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?>">
						<span class="<?php echo \esc_attr("{$sectionClass}__link-icon dashicons dashicons-arrow-left"); ?> "></span>
						<?php echo esc_html__('Back to forms', 'eightshift-forms'); ?>
					</a>
				</div>
			</div>
			<div class="<?php echo \esc_attr("{$sectionClass}__description {$sectionClass}__description--with-spacing"); ?>">
				<?php echo esc_html($settingsDetailsSubTitle); ?>
			</div>
			<div class="<?php echo \esc_attr("{$sectionClass}__content"); ?>">
				<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	</div>
</div>
