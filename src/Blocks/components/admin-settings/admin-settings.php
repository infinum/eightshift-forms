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

$ctaLinkClass = 'esf:inline-flex esf:items-center esf:gap-8 esf:w-fit esf:py-8 esf:px-12 esf:rounded-lg esf:font-medium esf:text-accent-600 esf:bg-transparent esf:no-underline hover:esf:bg-accent-500/5 focus:esf:outline-none focus:esf:shadow-none focus-visible:esf:shadow-[0_0_0_1px_white,0_0_0_4px_color-mix(in_oklch,var(--color-accent-600)_30%,transparent)]';

?>

<div class="<?php echo esc_attr($componentClass); ?> esf:grid esf:[grid-template-columns:15rem_1fr] esf:[grid-template-areas:'notice_notice_notice'_'sidebar_main_main'] esf:gap-x-16">
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
			<a href="<?php echo esc_url($adminSettingsBackLink); ?>" class="esf:block esf:relative esf:w-fit esf:px-[0.45rem] esf:py-[0.4rem] esf:no-underline esf:text-secondary-600 esf:rounded-lg esf:transition-[color,background-color,box-shadow] esf:duration-300 hover:esf:text-secondary-600 hover:esf:bg-secondary-200 focus:esf:outline-none focus:esf:shadow-none">
				<span class="esf:relative esf:z-[2] esf:flex esf:flex-row esf:items-center esf:text-xs esf:gap-8">
					<?php
					echo UtilsHelper::getUtilsIcons('arrowLeft'), // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
					esc_html__('Forms', 'eightshift-forms');
					?>
				</span>
			</a>
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
				<div class="esf:flex esf:items-center esf:justify-between esf:min-h-[36px]">
					<div class="esf:text-2xl esf:font-medium esf:min-h-[1.25rem] esf:leading-[1.2] esf:tracking-[-0.02em]">
						<?php echo esc_html($adminSettingsPageTitle); ?>
					</div>

					<?php if (!$adminSettingsIsGlobal) { ?>
						<div class="esf:flex esf:items-center esf:gap-8">
							<a href="<?php echo esc_url($adminSettingsFormEditLink); ?>" class="<?php echo esc_attr($ctaLinkClass); ?>">
								<?php
								// phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
								echo UtilsHelper::getUtilsIcons('edit'),
								esc_html__('Edit form', 'eightshift-forms');
								?>
							</a>

							<a href="<?php echo esc_url($adminSettingsFormLocationsLink); ?>" class="<?php echo esc_attr($ctaLinkClass); ?>">
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
					<div class="esf:text-[0.8rem] esf:text-secondary-500 esf:mt-8">
						<?php echo esc_html($adminSettingsSubTitle); ?>
					</div>
				<?php } ?>
			</div>
			<div class="esf:overflow-x-hidden esf:p-32 esf:h-full">
				<?php echo $adminSettingsForm; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
				?>
			</div>
		</div>
	</div>
</div>
