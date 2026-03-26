<?php

/**
 * Template for admin settings page.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

echo Helpers::outputCssVariablesGlobal(); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped

$componentName = $manifest['componentName'] ?? '';
$componentClass = $manifest['componentClass'] ?? '';

$adminSettingsPageTitle = Helpers::checkAttr('adminSettingsPageTitle', $attributes, $manifest);
$adminSettingsBackLink = Helpers::checkAttr('adminSettingsBackLink', $attributes, $manifest);
$adminSettingsFormEditLink = Helpers::checkAttr('adminSettingsFormEditLink', $attributes, $manifest);
$adminSettingsFormLocationsLink = Helpers::checkAttr('adminSettingsFormLocationsLink', $attributes, $manifest);
$adminSettingsSidebar = Helpers::checkAttr('adminSettingsSidebar', $attributes, $manifest);
$adminSettingsForm = Helpers::checkAttr('adminSettingsForm', $attributes, $manifest);
$adminSettingsType = Helpers::checkAttr('adminSettingsType', $attributes, $manifest);
$adminSettingsIsGlobal = Helpers::checkAttr('adminSettingsIsGlobal', $attributes, $manifest);
$adminSettingsNotice = Helpers::checkAttr('adminSettingsNotice', $attributes, $manifest);

if (!$adminSettingsSidebar || !$adminSettingsForm) {
	return;
}

?>

<div class="esf:flex esf:flex-row esf:gap-30 esf:-ml-20 esf:p-40">
	<?php if ($adminSettingsNotice) { ?>
		<div class="esf:flex esf:flex-col esf:gap-16">
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
	<div class="esf:flex esf:max-w-3xs esf:w-full esf:flex-col esf:gap-24 esf:sticky esf:self-start">
		<?php
		echo Helpers::render('button', [
			'buttonVariant' => 'secondaryGhost',
			'buttonLabel' => esc_html__('Forms', 'eightshift-forms'),
			'buttonIcon' => UtilsHelper::getUtilsIcons('arrowLeft'),
			'buttonUrl' => $adminSettingsBackLink,
		]);

		echo Helpers::render(
			'sidebar-section',
			[
				'items' => $adminSettingsSidebar,
				'adminSettingsType' => $adminSettingsType,
			],
			'components',
			false,
			"{$componentName}/partials"
		);
		?>
	</div>
	<div class="esf:flex esf:flex-col esf:h-full esf:gap-15 esf:flex-1">
		<div class="esf:flex esf:items-center esf:justify-between esf:min-h-14">
			<div class="esf:text-2xl esf:font-medium">
				<?php echo esc_html($adminSettingsPageTitle); ?>
			</div>

			<?php if (!$adminSettingsIsGlobal) { ?>
				<div class="esf:flex esf:items-center esf:gap-8">
					<?php echo Helpers::render('button', [
						'buttonVariant' => 'secondaryGhost',
						'buttonLabel' => esc_html__('Edit form', 'eightshift-forms'),
						'buttonIcon' => UtilsHelper::getUtilsIcons('edit'),
						'buttonUrl' => $adminSettingsFormEditLink,
					]); ?>

					<?php echo Helpers::render('button', [
						'buttonVariant' => 'secondaryGhost',
						'buttonLabel' => esc_html__('Locations used', 'eightshift-forms'),
						'buttonIcon' => UtilsHelper::getUtilsIcons('location'),
						'buttonUrl' => $adminSettingsFormLocationsLink,
					]); ?>
				</div>
			<?php } ?>
		</div>
		<div class="esf:overflow-x-hidden esf:h-full esf:max-w-xl">
			<?php echo $adminSettingsForm; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
			?>
		</div>
	</div>
</div>
