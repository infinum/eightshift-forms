<?php

/**
 * Template for admin settings page.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);
$manifestSection = Components::getComponent('admin-settings-section');
$manifestUtils = Components::getComponent('utils');

echo Components::outputCssVariablesGlobal(); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped

$componentName = $manifest['componentName'] ?? '';
$componentClass = $manifest['componentClass'] ?? '';
$sectionClass = $manifestSection['componentClass'] ?? '';

$adminSettingsPageTitle = Components::checkAttr('adminSettingsPageTitle', $attributes, $manifest);
$adminSettingsSubTitle = Components::checkAttr('adminSettingsSubTitle', $attributes, $manifest);
$adminSettingsBackLink = Components::checkAttr('adminSettingsBackLink', $attributes, $manifest);
$adminSettingsFormEditLink = Components::checkAttr('adminSettingsFormEditLink', $attributes, $manifest);
$adminSettingsSidebar = Components::checkAttr('adminSettingsSidebar', $attributes, $manifest);
$adminSettingsForm = Components::checkAttr('adminSettingsForm', $attributes, $manifest);
$adminSettingsType = Components::checkAttr('adminSettingsType', $attributes, $manifest);
$adminSettingsIsGlobal = Components::checkAttr('adminSettingsIsGlobal', $attributes, $manifest);
$adminSettingsNotice = Components::checkAttr('adminSettingsNotice', $attributes, $manifest);

$layoutClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($sectionClass, $sectionClass),
	Components::selector($sectionClass, $sectionClass, '', 'with-sidebar'),
]);

if (!$adminSettingsSidebar || !$adminSettingsForm) {
	return;
}

?>

<div class="<?php echo esc_attr($layoutClass); ?>">
	<?php if ($adminSettingsNotice) { ?>
		<div class="<?php echo esc_attr("{$sectionClass}__notice"); ?>">
			<?php
				echo Components::render(
					'notice',
					[
						'noticeContent' => $adminSettingsNotice,
					],
					'',
					true
				);
			?>
		</div>
	<?php } ?>
	<div class="<?php echo esc_attr("{$sectionClass}__sidebar"); ?>">
		<div class="<?php echo esc_attr("{$sectionClass}__section {$sectionClass}__section--clean"); ?>">
			<a href="<?php echo esc_url($adminSettingsBackLink); ?>" class="<?php echo esc_attr("{$sectionClass}__link"); ?>">
				<?php echo $manifestUtils['icons']['arrowLeft']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
				<?php echo esc_html__('All forms', 'eightshift-forms'); ?>
			</a>
		</div>

		<?php
		echo Components::renderPartial( // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
			'component',
			$componentName,
			'sidebar-section',
			[
				'items' => $adminSettingsSidebar,
				'sectionClass' => $sectionClass,
				'adminSettingsType' => $adminSettingsType,
			]
		);
		?>
	</div>
	<div class="<?php echo esc_attr("{$sectionClass}__main"); ?>">
		<div class="<?php echo esc_attr("{$sectionClass}__section"); ?>">
			<div class="<?php echo esc_attr("{$sectionClass}__heading"); ?>">
				<div class="<?php echo esc_attr("{$sectionClass}__heading-wrap"); ?>">
					<div class="<?php echo esc_attr("{$sectionClass}__heading-title"); ?>">
						<?php echo esc_html($adminSettingsPageTitle); ?>
					</div>

					<?php if (!$adminSettingsIsGlobal) { ?>
						<div class="<?php echo esc_attr("{$sectionClass}__actions"); ?>">
							<a href="<?php echo esc_url($adminSettingsFormEditLink); ?>" class="<?php echo esc_attr("{$sectionClass}__link"); ?> <?php echo esc_attr("{$sectionClass}__link--cta"); ?>">
								<?php echo $manifestUtils['icons']['edit']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
								<?php echo esc_html__('Edit form', 'eightshift-forms'); ?>
							</a>
						</div>
					<?php } ?>
				</div>

				<?php if ($adminSettingsSubTitle) { ?>
					<div class="<?php echo esc_attr("{$sectionClass}__description"); ?>">
						<?php echo esc_html($adminSettingsSubTitle); ?>
					</div>
				<?php } ?>
			</div>
			<div class="<?php echo esc_attr("{$sectionClass}__content"); ?>">
				<?php echo $adminSettingsForm; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
			</div>
		</div>
	</div>
</div>
