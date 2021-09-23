<?php

/**
 * Template for settings form option page.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\Helpers\Components;
use EightshiftForms\Settings\AbstractFormBuilder;
use EightshiftForms\Settings\FormOption;

$globalManifest = Components::getManifest(dirname(__DIR__, 2));
$manifest = Components::getManifest(__DIR__);
$manifestSection = Components::getManifest(dirname(__DIR__, 1) . '/settings-section');

echo Components::outputCssVariablesGlobal($globalManifest); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

$componentClass = $manifest['componentClass'] ?? '';
$sectionClass = $manifestSection['componentClass'] ?? '';

$settingsFormOptionPageTitle = Components::checkAttr('settingsFormOptionPageTitle', $attributes, $manifest);
$settingsFormOptionSubTitle = Components::checkAttr('settingsFormOptionSubTitle', $attributes, $manifest);
$settingsFormOptionBackLink = Components::checkAttr('settingsFormOptionBackLink', $attributes, $manifest);
$settingsFormOptionFormId = Components::checkAttr('settingsFormOptionFormId', $attributes, $manifest);
$settingsFormOptionLink = Components::checkAttr('settingsFormOptionLink', $attributes, $manifest);
$settingsFormOptionForm = Components::checkAttr('settingsFormOptionForm', $attributes, $manifest);

$sidebar = $settingsFormOptionForm['sidebar'] ?? [];

$layoutClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($sectionClass, $sectionClass),
	Components::selector($sectionClass, $sectionClass, '', 'with-sidebar'),
]);

$setting = isset($_GET['setting']) ? \sanitize_text_field(wp_unslash($_GET['setting'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

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
									href="<?php echo esc_url("{$settingsFormOptionLink}&setting={$value}"); ?>"
									class="<?php echo \esc_attr("{$sectionClass}__menu-link " . Components::selector($value === $setting, $sectionClass, 'menu-link', 'active')); ?>"
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
					<?php echo sprintf('%s - %s', esc_html($settingsFormOptionPageTitle), esc_html($settingsFormOptionFormId)); ?>
				</div>

				<div class="<?php echo \esc_attr("{$sectionClass}__actions"); ?>">
					<a href="<?php echo esc_url($settingsFormOptionBackLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?>">
						<span class="<?php echo \esc_attr("{$sectionClass}__link-icon dashicons dashicons-arrow-left"); ?> "></span>
						<?php echo esc_html__('Back to forms', 'eightshift-forms'); ?>
					</a>
				</div>
			</div>
			<div class="<?php echo \esc_attr("{$sectionClass}__description {$sectionClass}__description--with-spacing"); ?>">
				<?php echo esc_html($settingsFormOptionSubTitle); ?>
			</div>
			<div class="<?php echo \esc_attr("{$sectionClass}__content"); ?>">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo \apply_filters(
					AbstractFormBuilder::SETTINGS_PAGE_FORM_BUILDER,
					$settingsFormOptionForm['forms'][$setting] ?? [],
					$settingsFormOptionFormId,
					$setting === FormOption::SETTINGS_GENERAL_KEY
				);
				?>
			</div>
		</div>
	</div>
</div>
