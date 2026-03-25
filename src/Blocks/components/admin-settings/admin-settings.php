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
$adminSettingsSubTitle = Helpers::checkAttr('adminSettingsSubTitle', $attributes, $manifest);
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

<div class="esf:grid esf:[grid-cols-15rem_1fr] esf:[grid-template-areas:'notice_notice_notice'_'sidebar_main_main'] esf:gap-x-16">
	<?php if ($adminSettingsNotice) { ?>
		<div class="esf:[grid-area:notice]">
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
	<div class="esf:[grid-area:sidebar] esf:flex esf:flex-col esf:gap-24 esf:sticky esf:top-32 esf:self-start esf:py-24">
		<div>
			<?php echo Helpers::render('button', [
				'buttonVariant' => 'button-secondary-ghost',
				'buttonLabel' => esc_html__('Forms', 'eightshift-forms'),
				'buttonIcon' => UtilsHelper::getUtilsIcons('arrowLeft'),
				'buttonUrl' => $adminSettingsBackLink,
			]); ?>
		</div>

		<?php
		// phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
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
	<div class="esf:[grid-area:main]">
		<div class="esf:h-full">
			<div class="esf:pt-24 esf:px-32">
				<div class="esf:flex esf:items-center esf:justify-between esf:min-h-14">
					<div class="esf:text-2xl esf:font-medium esf:min-h-20 esf:leading-6 esf:tracking-[-0.02em]">
						<?php echo esc_html($adminSettingsPageTitle); ?>
					</div>

					<?php if (!$adminSettingsIsGlobal) { ?>
						<div class="esf:flex esf:items-center esf:gap-8">
							<?php echo Helpers::render('button', [
								'buttonVariant' => 'button-secondary-ghost',
								'buttonLabel' => esc_html__('Edit form', 'eightshift-forms'),
								'buttonIcon' => UtilsHelper::getUtilsIcons('edit'),
								'buttonUrl' => $adminSettingsFormEditLink,
							]); ?>

							<?php echo Helpers::render('button', [
								'buttonVariant' => 'button-secondary-ghost',
								'buttonLabel' => esc_html__('Locations used', 'eightshift-forms'),
								'buttonIcon' => UtilsHelper::getUtilsIcons('location'),
								'buttonUrl' => $adminSettingsFormLocationsLink,
							]); ?>
						</div>
					<?php } ?>
				</div>

				<?php if ($adminSettingsSubTitle) { ?>
					<div class="esf:text-[0.8rem] esf:text-secondary-500 esf:mt-8">
						<?php echo esc_html($adminSettingsSubTitle); ?>
					</div>
				<?php } ?>
			</div>
			<div class="esf:overflow-x-hidden esf:p-32 esf:h-full esf:max-w-3xl">
				<?php echo $adminSettingsForm; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
				?>
			</div>
		</div>
	</div>
</div>
