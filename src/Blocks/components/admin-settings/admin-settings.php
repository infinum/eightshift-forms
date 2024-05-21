<?php

/**
 * Template for admin settings page.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);
$manifestSection = Helpers::getComponent('admin-settings-section');

echo Helpers::outputCssVariablesGlobal(); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped

$componentName = $manifest['componentName'] ?? '';
$componentClass = $manifest['componentClass'] ?? '';
$sectionClass = $manifestSection['componentClass'] ?? '';

$adminSettingsPageTitle = Helpers::checkAttr('adminSettingsPageTitle', $attributes, $manifest);
$adminSettingsSubTitle = Helpers::checkAttr('adminSettingsSubTitle', $attributes, $manifest);
$adminSettingsBackLink = Helpers::checkAttr('adminSettingsBackLink', $attributes, $manifest);
$adminSettingsFormEditLink = Helpers::checkAttr('adminSettingsFormEditLink', $attributes, $manifest);
$adminSettingsFormLocationsLink = Helpers::checkAttr('adminSettingsFormLocationsLink', $attributes, $manifest);
$adminSettingsSidebar = Helpers::checkAttr('adminSettingsSidebar', $attributes, $manifest);
$adminSettingsForm = Helpers::checkAttr('adminSettingsForm', $attributes, $manifest);
$adminSettingsType = Helpers::checkAttr('adminSettingsType', $attributes, $manifest);
$adminSettingsIsGlobal = Helpers::checkAttr('adminSettingsIsGlobal', $attributes, $manifest);
$adminSettingsNotice = Helpers::checkAttr('adminSettingsNotice', $attributes, $manifest);

$layoutClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($sectionClass, $sectionClass),
	Helpers::selector($sectionClass, $sectionClass, '', 'with-sidebar'),
]);

if (!$adminSettingsSidebar || !$adminSettingsForm) {
	return;
}

?>

<div class="<?php echo esc_attr($layoutClass); ?>">
	<?php if ($adminSettingsNotice) { ?>
		<div class="<?php echo esc_attr("{$sectionClass}__notice"); ?>">
			<?php
				echo Helpers::render(
					'notice',
					[
						'noticeContent' => $adminSettingsNotice,
					],
					'components',
					true
				);
			?>
		</div>
	<?php } ?>
	<div class="<?php echo esc_attr("{$sectionClass}__sidebar"); ?>">
		<div class="<?php echo esc_attr("{$sectionClass}__section {$sectionClass}__section--clean"); ?>">
			<a href="<?php echo esc_url($adminSettingsBackLink); ?>" class="<?php echo esc_attr("{$sectionClass}__menu-link {$sectionClass}__menu-link--fit"); ?>">
				<span class="<?php echo esc_attr("{$sectionClass}__menu-link-wrap"); ?>">
					<?php
					echo UtilsHelper::getUtilsIcons('arrowLeft'), // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
					esc_html__('Forms', 'eightshift-forms');
					?>
				</span>
			</a>
		</div>

		<?php
		// phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
		echo $componentName;
		echo Helpers::render(
			'sidebar-section',
			[
				'items' => $adminSettingsSidebar,
				'sectionClass' => $sectionClass,
				'adminSettingsType' => $adminSettingsType,
			],
			'components',
			false,
			"{$componentName}/partials"
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
								<?php
									// phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
									echo UtilsHelper::getUtilsIcons('edit'),
									esc_html__('Edit form', 'eightshift-forms');
								?>
							</a>

							<a href="<?php echo esc_url($adminSettingsFormLocationsLink); ?>" class="<?php echo esc_attr("{$sectionClass}__link"); ?> <?php echo esc_attr("{$sectionClass}__link--cta"); ?>">
								<?php
									// phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
									echo UtilsHelper::getUtilsIcons('location'),
									esc_html__('Locations used', 'eightshift-forms');
								?>
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
				<?php echo $adminSettingsForm; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
				?>
			</div>
		</div>
	</div>
</div>
