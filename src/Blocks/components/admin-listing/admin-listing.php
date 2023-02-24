<?php

/**
 * Template for admin listing page.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\AdminMenus\FormAdminMenu;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);
$manifestSection = Components::getComponent('admin-settings-section');
$manifestUtils = Components::getComponent('utils');

echo Components::outputCssVariablesGlobal(); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped

$componentClass = $manifest['componentClass'] ?? '';
$componentJsItemClass = $manifest['componentJsItemClass'] ?? '';
$componentJsFilterClass = $manifest['componentJsFilterClass'] ?? '';
$componentJsSyncClass = $manifest['componentJsSyncClass'] ?? '';
$sectionClass = $manifestSection['componentClass'] ?? '';

$adminListingPageTitle = Components::checkAttr('adminListingPageTitle', $attributes, $manifest);
$adminListingSubTitle = Components::checkAttr('adminListingSubTitle', $attributes, $manifest);
$adminListingNewFormLink = Components::checkAttr('adminListingNewFormLink', $attributes, $manifest);
$adminListingTrashLink = Components::checkAttr('adminListingTrashLink', $attributes, $manifest);
$adminListingForms = Components::checkAttr('adminListingForms', $attributes, $manifest);
$adminListingType = Components::checkAttr('adminListingType', $attributes, $manifest);
$adminListingListingLink = Components::checkAttr('adminListingListingLink', $attributes, $manifest);
$adminListingIntegrations = Components::checkAttr('adminListingIntegrations', $attributes, $manifest);
$adminListingIsDeveloperMode = Components::checkAttr('adminListingIsDeveloperMode', $attributes, $manifest);

$layoutClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($sectionClass, $sectionClass),
]);

?>

<div class="<?php echo esc_attr($layoutClass); ?>">
	<div class="<?php echo esc_attr("{$sectionClass}__section"); ?>">
		<?php if ($adminListingPageTitle || $adminListingSubTitle) { ?>
			<div class="<?php echo esc_attr("{$sectionClass}__heading"); ?>">
				<div class="<?php echo esc_attr("{$sectionClass}__heading-wrap"); ?>">
					<div class="<?php echo esc_attr("{$sectionClass}__heading-inner-wrap"); ?>">
						<div class="<?php echo esc_attr("{$sectionClass}__heading-title"); ?>">
							<?php echo esc_html($adminListingPageTitle); ?>
						</div>

						<div class="<?php echo esc_attr("{$sectionClass}__heading-filter {$componentJsFilterClass}"); ?>">
							<?php
							if ($adminListingIntegrations) {
								echo wp_kses_post($adminListingIntegrations);
							}
							?>
						</div>

						<?php if ($adminListingType !== 'trash' && $adminListingTrashLink) { ?>
							<a href="#" class="<?php echo esc_attr("{$sectionClass}__link {$componentJsSyncClass}"); ?>" data-id="all">
								<?php echo $manifestUtils['icons']['sync']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
								<?php echo esc_html__('Sync all forms', 'eightshift-forms'); ?>
							</a>
						<?php } ?>
					</div>

					<div class="<?php echo esc_attr("{$sectionClass}__actions"); ?>">
						<?php if ($adminListingType !== 'trash' && $adminListingTrashLink) { ?>
							<a href="<?php echo esc_url($adminListingTrashLink); ?>" class="<?php echo esc_attr("{$sectionClass}__link"); ?>">
								<?php echo $manifestUtils['icons']['trash']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
								<?php echo esc_html__('Deleted forms', 'eightshift-forms'); ?>
							</a>
						<?php } ?>
						
						<?php if ($adminListingType === 'trash' && $adminListingListingLink) { ?>
							<a href="<?php echo esc_url($adminListingListingLink); ?>" class="<?php echo esc_attr("{$sectionClass}__link"); ?>">
								<?php echo $manifestUtils['icons']['arrowLeft']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
								<?php echo esc_html__('All forms', 'eightshift-forms'); ?>
							</a>
						<?php } ?>

						<?php if ($adminListingNewFormLink) { ?>
							<a href="<?php echo esc_url($adminListingNewFormLink); ?>" class="<?php echo esc_attr("{$sectionClass}__link"); ?> <?php echo esc_attr("{$sectionClass}__link--cta"); ?>">
								<?php echo $manifestUtils['icons']['plusCircle']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
								<?php echo esc_html__('New form', 'eightshift-forms'); ?>
							</a>
						<?php } ?>
					</div>
				</div>

				<?php if ($adminListingSubTitle) { ?>
					<div class="<?php echo esc_attr("{$sectionClass}__description"); ?>">
						<?php echo esc_html($adminListingSubTitle); ?>
					</div>
				<?php } ?>
			</div>
		<?php } ?>
		<div class="<?php echo esc_attr("{$sectionClass}__content"); ?>">
			<?php if (!$adminListingForms) {
				$emptyStateSubtitle = __('No forms (yet).', 'eightshift-forms');

				if ($adminListingType === 'trash') {
					$emptyStateSubtitle = __('Trash is empty.', 'eightshift-forms');
				}

				echo Components::render('highlighted-content', [
					'highlightedContentTitle' => __('Nothing to see here', 'eightshift-forms'),
					'highlightedContentSubtitle' => $emptyStateSubtitle,
					'highlightedContentIcon' => 'empty',
				]);
			} ?>
			<?php if ($adminListingForms) { ?>
				<ul class="<?php echo esc_attr("{$componentClass}__list"); ?>">
					<?php foreach ($adminListingForms as $form) { ?>
						<?php
						$id = $form['id'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						$editLink = $form['editLink'] ?? '';
						$postType = $form['postType'] ?? '';
						$viewLink = $form['viewLink'] ?? '';
						$trashLink = $form['trashLink'] ?? '';
						$trashRestoreLink = $form['trashRestoreLink'] ?? '';
						$settingsLink = $form['settingsLink'] ?? '';
						$settingsLocationLink = $form['settingsLocationLink'] ?? '';
						$title = $form['title'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						$status = $form['status'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						$useSync = $form['useSync'] ?? false;
						$activeIntegration = $form['activeIntegration'] ?? [];

						$activeIntegrationIsActive = $activeIntegration['isActive'] ?? false;
						$activeIntegrationIsValid = $activeIntegration['isValid'] ?? false;
						$activeIntegrationIsApiValid = $activeIntegration['isApiValid'] ?? false;

						$slug = $editLink;
						if (!$editLink) {
							$slug = '#';
						}
						?>
						<li
							class="<?php echo esc_attr("{$componentClass}__list-item {$componentJsItemClass}"); ?>"
							data-integration-type="<?php echo esc_attr($activeIntegration['value'] ?? FormAdminMenu::ADMIN_MENU_FILTER_NOT_CONFIGURED) ?>"
							data-integration-is-active="<?php echo wp_json_encode($activeIntegrationIsActive); ?>"
							data-integration-is-valid="<?php echo wp_json_encode($activeIntegrationIsValid); ?>"
							data-integration-is-api-valid="<?php echo wp_json_encode($activeIntegrationIsApiValid); ?>"
						>
							<div class="<?php echo esc_attr("{$componentClass}__wrap"); ?>">
								<div class="<?php echo esc_attr("{$componentClass}__item-intro"); ?>">
									<a href="<?php echo esc_url($slug); ?>" class="<?php echo esc_attr("{$componentClass}__label"); ?>">
										<?php echo $activeIntegration['icon'] ?? $manifestUtils['icons']['listingGeneric']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
										<span>
											<?php
											if ($adminListingIsDeveloperMode) {
												echo esc_html("{$id} - {$title}");
											} else {
												echo $title ? esc_html($title) : esc_html($id);
											}
											?>
										</span>

										<?php if ($status !== 'publish') { ?>
											<span class="<?php echo esc_attr("{$componentClass}__item-status"); ?>">
												<?php echo esc_html($status); ?>
											</span>
										<?php } ?>

										<?php if ($postType) { ?>
											<span class="<?php echo esc_attr("{$componentClass}__item-post-type"); ?>">
												<?php echo esc_html($postType); ?>
											</span>
										<?php } ?>
									</a>
								</div>

								<div class="<?php echo esc_attr("{$sectionClass}__actions {$componentClass}__actions"); ?>">
									<?php if ($editLink) { ?>
										<a href="<?php echo esc_url($editLink); ?>" class="<?php echo esc_attr("{$sectionClass}__link"); ?>">
											<?php echo $manifestUtils['icons']['edit']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
											<?php echo esc_html__('Edit', 'eightshift-forms'); ?>
										</a>
									<?php } ?>

									<?php if ($trashLink) { ?>
										<a href="<?php echo esc_url($trashLink); ?>" class="<?php echo esc_attr("{$sectionClass}__link"); ?>">
											<?php echo $manifestUtils['icons']['trash']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
											<?php
											if ($adminListingType === 'trash') {
												echo esc_html__('Delete permanently', 'eightshift-forms');
											} else {
												echo esc_html__('Delete', 'eightshift-forms');
											}
											?>
										</a>
									<?php } ?>

									<?php if ($adminListingType === 'trash') { ?>
										<a href="<?php echo esc_url($trashRestoreLink); ?>" class="<?php echo esc_attr("{$sectionClass}__link"); ?>">
											<?php echo $manifestUtils['icons']['restore']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
											<?php echo esc_html__('Restore', 'eightshift-forms'); ?>
										</a>
									<?php } ?>

									<?php if ($settingsLink) { ?>
										<a href="<?php echo esc_url($settingsLink); ?>" class="<?php echo esc_attr("{$sectionClass}__link"); ?>">
											<?php echo $manifestUtils['icons']['settings']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
											<?php echo esc_html__('Settings', 'eightshift-forms'); ?>
										</a>
									<?php } ?>

									<?php if ($settingsLocationLink) { ?>
										<a href="<?php echo esc_url($settingsLocationLink); ?>" class="<?php echo esc_attr("{$sectionClass}__link"); ?>">
											<?php echo $manifestUtils['icons']['locations']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
											<?php echo esc_html__('Locations', 'eightshift-forms'); ?>
										</a>
									<?php } ?>

									<?php if ($useSync) { ?>
										<button class="<?php echo esc_attr("{$sectionClass}__link {$componentJsSyncClass}"); ?>" data-id="<?php echo esc_attr($id); ?>">
											<?php echo $manifestUtils['icons']['sync']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
											<?php echo esc_html__('Sync', 'eightshift-forms'); ?>
										</button>
									<?php } ?>

									<?php if ($viewLink) { ?>
										<a href="<?php echo esc_url($viewLink); ?>" class="<?php echo esc_attr("{$sectionClass}__link"); ?>">
											<?php echo $manifestUtils['icons']['view']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
											<?php echo esc_html__('View', 'eightshift-forms'); ?>
										</a>
									<?php } ?>
								</div>
							</div>

							<div class="<?php echo esc_attr("{$componentClass}__errors"); ?>">
								<?php if (!$activeIntegrationIsActive) { ?>
									<span class="<?php echo esc_attr("{$componentClass}__error"); ?>" title="<?php echo esc_html__('This form has inactive form type set. Go to global settings and turn on this form type.', 'eightshift-forms'); ?>">
										<?php echo $manifestUtils['icons']['warning']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
										<?php echo esc_html__('Inactive integration', 'eightshift-forms'); ?>
									</span>
								<?php }?>
								<?php if (!$activeIntegrationIsValid) { ?>
									<span class="<?php echo esc_attr("{$componentClass}__error"); ?>" title="<?php echo esc_html__('This form has invalid or missing configuration. Open the form and check your settings.', 'eightshift-forms'); ?>">
										<?php echo $manifestUtils['icons']['warning']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
										<?php echo esc_html__('Invalid form config', 'eightshift-forms'); ?>
									</span>
								<?php }?>
								<?php if (!$activeIntegrationIsApiValid) { ?>
									<span class="<?php echo esc_attr("{$componentClass}__error"); ?>" title="<?php echo esc_html__('This form has missing form fields, this can be and inactive form integration or some other error. Open the form and check your settings.', 'eightshift-forms'); ?>">
										<?php echo $manifestUtils['icons']['warning']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
										<?php echo esc_html__('Missing form fields', 'eightshift-forms'); ?>
									</span>
								<?php }?>
							</div>
						</li>
					<?php } ?>
				</ul>
			<?php } ?>
		</div>
	</div>
</div>

<?php
echo Components::render(
	'global-msg',
	Components::props('globalMsg', $attributes)
);
