<?php

/**
 * Template for admin listing page.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);
$manifestSection = Components::getManifest(dirname(__DIR__, 1) . '/admin-settings-section');

echo Components::outputCssVariablesGlobal(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

$componentClass = $manifest['componentClass'] ?? '';
$sectionClass = $manifestSection['componentClass'] ?? '';

$adminListingPageTitle = Components::checkAttr('adminListingPageTitle', $attributes, $manifest);
$adminListingSubTitle = Components::checkAttr('adminListingSubTitle', $attributes, $manifest);
$adminListingNewFormLink = Components::checkAttr('adminListingNewFormLink', $attributes, $manifest);
$adminListingTrashLink = Components::checkAttr('adminListingTrashLink', $attributes, $manifest);
$adminListingForms = Components::checkAttr('adminListingForms', $attributes, $manifest);
$adminListingType = Components::checkAttr('adminListingType', $attributes, $manifest);
$adminListingListingLink = Components::checkAttr('adminListingListingLink', $attributes, $manifest);

$layoutClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($sectionClass, $sectionClass),
]);

?>

<div class="<?php echo \esc_attr($layoutClass); ?>">
	<div class="<?php echo \esc_attr("{$sectionClass}__section"); ?>">
		<?php if ($adminListingPageTitle || $adminListingSubTitle) { ?>
			<div class="<?php echo \esc_attr("{$sectionClass}__heading {$sectionClass}__heading--no-spacing"); ?>">
				<div class="<?php echo \esc_attr("{$sectionClass}__heading-wrap"); ?>">
					<div class="<?php echo \esc_attr("{$sectionClass}__heading-title"); ?>">
						<?php echo \esc_html($adminListingPageTitle); ?>
					</div>

					<div class="<?php echo \esc_attr("{$sectionClass}__actions"); ?>">
						<?php if ($adminListingType !== 'trash' && $adminListingTrashLink) { ?>
							<a href="<?php echo \esc_url($adminListingTrashLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?>">
								<svg class="<?php echo \esc_attr("{$sectionClass}__link-icon"); ?>" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m5 4.75 1.712 12.14a1 1 0 0 0 .99.86h5.596a1 1 0 0 0 .99-.86L16 4.75m-12 0h13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M12.5 4.25a2 2 0 1 0-4 0" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M9 8v6m3-6v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/></svg>
								<?php echo \esc_html__('Deleted forms', 'eightshift-forms'); ?>
							</a>
						<?php } ?>
						
						<?php if ($adminListingType === 'trash' && $adminListingListingLink) { ?>
							<a href="<?php echo \esc_url($adminListingListingLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?>">
								<svg class="<?php echo \esc_attr("{$sectionClass}__link-icon"); ?>" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17 10H4m4-5-5 5 5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
								<?php echo \esc_html__('All forms', 'eightshift-forms'); ?>
							</a>
						<?php } ?>

						<?php if ($adminListingNewFormLink) { ?>
							<a href="<?php echo esc_url($adminListingNewFormLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?> <?php echo \esc_attr("{$sectionClass}__link--cta"); ?>">
								<svg class="<?php echo \esc_attr("{$sectionClass}__link-icon"); ?>" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M17.5 10a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0zM10 5.636a.75.75 0 0 1 .75.75v3h3a.75.75 0 0 1 0 1.5h-3v3a.75.75 0 1 1-1.5 0v-3h-3a.75.75 0 1 1 0-1.5h3v-3a.75.75 0 0 1 .75-.75z" fill="currentColor"/></svg>
								<?php echo \esc_html__('New form', 'eightshift-forms'); ?>
							</a>
						<?php } ?>
					</div>
				</div>

				<?php if ($adminListingSubTitle) { ?>
					<div class="<?php echo \esc_attr("{$sectionClass}__description"); ?>">
						<?php echo \esc_html($adminListingSubTitle); ?>
					</div>
				<?php } ?>
			</div>
		<?php } ?>
		<div class="<?php echo \esc_attr("{$sectionClass}__content"); ?>">
			<?php if (!$adminListingForms) {
				$emptyStateSubtitle = __('No forms (yet).', 'eightshift-forms');

				if ($adminListingType === 'trash') {
					$emptyStateSubtitle = __('Trash is empty.', 'eightshift-forms');
				}

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo Components::render('highlighted-content', [
					'highlightedContentTitle' => __('Nothing to see here', 'eightshift-forms'),
					// translators: %s will be replaced with the global settings url.
					'highlightedContentSubtitle' => $emptyStateSubtitle,
					'highlightedContentIcon' => 'empty',
				]);
			} ?>
			<?php if ($adminListingForms) { ?>
				<ul class="<?php echo \esc_attr("{$componentClass}__list"); ?>">
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
						$activeIntegrations = $form['activeIntegrations'] ?? [];

						$slug = $editLink;
						if (!$editLink) {
							$slug = '#';
						}
						?>
						<li class="<?php echo \esc_attr("{$componentClass}__list-item"); ?>">
							<div class="<?php echo esc_attr("{$componentClass}__item-intro"); ?>">
								<a href="<?php echo esc_url($slug); ?>" class="<?php echo \esc_attr("{$componentClass}__label"); ?>">
									<?php if ($postType === 'post') { ?>
										<svg class="<?php echo \esc_attr("{$componentClass}__label-icon"); ?>" width='20' height='20' viewBox='0 0 20 20' fill='none' xmlns='http://www.w3.org/2000/svg'><path d='M3.5 4h2m-2 3h2m-2 3h2m-2 3h2m-2 3h2m0-14v16' stroke='currentColor' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' fill='none'/><path d='M15.5 18.5h-11A1.5 1.5 0 0 1 3 17V3a1.5 1.5 0 0 1 1.5-1.5h11A1.5 1.5 0 0 1 17 3v14a1.5 1.5 0 0 1-1.5 1.5z' stroke='currentColor' stroke-width='1.5' stroke-linecap='round' fill='none'/><path d='M8.25 4.5h5m-5 2.5h3m-3 2.5h2' stroke='currentColor' stroke-opacity='.3' stroke-width='1.5' stroke-linecap='round' fill='none'/></svg>
									<?php } elseif ($postType === 'page') { ?>
										<svg class="<?php echo \esc_attr("{$componentClass}__label-icon"); ?>" width='20' height='20' viewBox='0 0 20 20' fill='none' xmlns='http://www.w3.org/2000/svg'><path d='m16 7-5-5v5h5z' fill='currentColor' fill-opacity='.12'/><path d='M16 6.5h-5v-5' stroke='currentColor' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' fill='none'/><path d='M14.5 18.5h-9A1.5 1.5 0 0 1 4 17V3a1.5 1.5 0 0 1 1.5-1.5h5.85a1.5 1.5 0 0 1 1.095.474l3.15 3.363A1.5 1.5 0 0 1 16 6.362V17a1.5 1.5 0 0 1-1.5 1.5z' stroke='currentColor' stroke-width='1.5' stroke-linecap='round' fill='none'/></svg>
									<?php } else { ?>
										<svg class="<?php echo \esc_attr("{$componentClass}__label-icon"); ?>" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.5 11.75h5m-5 3h5" stroke="currentColor" stroke-opacity=".12" stroke-width="1.5" stroke-linecap="round" fill="none"/><path d="M4.5 8.75h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/><circle cx="5" cy="11.75" r="1" fill="currentColor"/><circle cx="5" cy="14.75" r="1" fill="currentColor"/><path d="M1 2a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v4H1V2z" fill="currentColor" fill-opacity=".12"/><rect x="1" y="1" width="18" height="18" rx="1.5" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M4.5 3.75h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/></svg>
									<?php } ?>

									<span><?php echo $title ? esc_html($title) : esc_html($id); ?></span>

									<?php if ($activeIntegrations) { ?>
										<div class="<?php echo \esc_attr("{$componentClass}__integration"); ?>">
											<?php foreach ($activeIntegrations as $integration) { ?>
												<?php
												$label = $integration['label'] ?? '';
												$icon = $integration['icon'] ?? '';

												if (!$label || !$icon) {
													continue;
												}
												?>
												<span title="<?php echo esc_attr($label); ?>">
													<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												</span>
											<?php } ?>
										</div>
									<?php } ?>
								</a>

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
							</div>
							<div class="<?php echo \esc_attr("{$sectionClass}__actions"); ?>">
								<?php if ($editLink) { ?>
									<a href="<?php echo esc_url($editLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?>">
										<svg class="<?php echo \esc_attr("{$sectionClass}__link-icon"); ?>" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m15.5 7.5-3-3-8.665 8.184a1.5 1.5 0 0 0-.435.765l-.708 3.189a.5.5 0 0 0 .646.583l3.326-1.109a1.5 1.5 0 0 0 .586-.362L15.5 7.5z" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="m12.5 4.5 1.44-1.44a1.5 1.5 0 0 1 2.12 0l.88.88a1.5 1.5 0 0 1 0 2.12L15.5 7.5" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>
										<?php echo esc_html__('Edit', 'eightshift-forms'); ?>
									</a>
								<?php } ?>

								<?php if ($trashLink) { ?>
									<a href="<?php echo esc_url($trashLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?>">
										<svg class="<?php echo \esc_attr("{$sectionClass}__link-icon"); ?>" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m5 4.75 1.712 12.14a1 1 0 0 0 .99.86h5.596a1 1 0 0 0 .99-.86L16 4.75m-12 0h13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M12.5 4.25a2 2 0 1 0-4 0" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M9 8v6m3-6v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/></svg>
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
									<a href="<?php echo esc_url($trashRestoreLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?>">
										<svg class="<?php echo \esc_attr("{$sectionClass}__link-icon"); ?>" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.5 11A5.5 5.5 0 1 0 10 5.5H8.5m0 0L10.75 8M8.5 5.5l2.5-2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M4.5 11c0-.706.133-1.38.375-2" stroke="currentColor" stroke-opacity=".12" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
										<?php echo esc_html__('Restore', 'eightshift-forms'); ?>
									</a>
								<?php } ?>

								<?php if ($settingsLink) { ?>
									<a href="<?php echo esc_url($settingsLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?>">
										<svg class="<?php echo \esc_attr("{$sectionClass}__link-icon"); ?>" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.196 1.66c-1.813-.743-3.59-.31-4.25 0 .118 1.619-.581 4.37-4.321 2.428-.661.62-2.012 2.186-2.125 3.5 1.653.761 3.995 2.885.142 5.285.236.976.963 3.042 1.983 3.499 1.417-1 4.264-1.9 4.32 2.5.922.285 3.06.685 4.25 0-.117-1.762.567-4.728 4.25-2.5.567-.476 1.772-1.843 2.055-3.5-1.511-.928-3.627-3.285 0-5.284-.212-.834-.935-2.7-2.125-3.5-3.287 1.943-4.156-.81-4.18-2.428z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><ellipse cx="10.071" cy="10.16" rx="2.975" ry="3" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>
										<?php echo esc_html__('Settings', 'eightshift-forms'); ?>
									</a>
								<?php } ?>

								<?php if ($settingsLocationLink) { ?>
									<a href="<?php echo esc_url($settingsLocationLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?>">
										<svg class="<?php echo \esc_attr("{$sectionClass}__link-icon"); ?>" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity=".3" d="M7.5 11.75H12m-4.5 3H11m-6.5-6h5" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle opacity=".3" cx="5" cy="11.75" r="1" fill="#29A3A3"/><circle opacity=".3" cx="5" cy="14.75" r="1" fill="#29A3A3"/><path d="M19 14.125c0 2.273-2.5 4.773-2.5 4.773s-2.5-2.5-2.5-4.773a2.5 2.5 0 0 1 5 0z" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle cx="16.5" cy="14.125" r=".682" fill="#29A3A3"/><path opacity=".2" fill="#29A3A3" d="M1 1h18v5H1z"/><path d="M19 10V2.5A1.5 1.5 0 0 0 17.5 1h-15A1.5 1.5 0 0 0 1 2.5v15A1.5 1.5 0 0 0 2.5 19H13M4.5 3.75h8" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
										<?php echo esc_html__('Locations', 'eightshift-forms'); ?>
									</a>
								<?php } ?>

								<?php if ($viewLink) { ?>
									<a href="<?php echo esc_url($viewLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?>">
										<svg class="<?php echo \esc_attr("{$sectionClass}__link-icon"); ?>" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="10" cy="10" r="2.5" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M10 15c-5 0-8-3-9-5 1-2 4-5 9-5s8 3 9 5c-1 2-4 5-9 5z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
										<?php echo esc_html__('View', 'eightshift-forms'); ?>
									</a>
								<?php } ?>
							</div>
						</li>
					<?php } ?>
				</ul>
			<?php } ?>
		</div>
	</div>
</div>
