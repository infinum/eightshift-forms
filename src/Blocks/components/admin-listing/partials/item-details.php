<?php

/**
 * Template for admin listing - item details partial - used via ajax.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\Helpers\DeveloperHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getComponent('admin-listing');
$selectorJsItem = UtilsHelper::getStateSelectorAdmin('listingItem');

$isDevMode = DeveloperHelpers::isDeveloperModeActive();

$items = $attributes['items'] ?? [];
$emptyContent = $attributes['emptyContent'] ?? '';
$integrationType = $attributes['type'] ?? '';
$additionalAttributes = $attributes['additionalAttributes'] ?? [];
$sectionClass = $attributes['sectionClass'] ?? '';

?>
<div
	class="<?php echo esc_attr("$selectorJsItem") ?> esf:flex esf:flex-col esf:gap-8"
	<?php echo wp_kses_post(Helpers::getAttrsOutput($additionalAttributes)); ?>>
	<?php
	if ($items) {
		foreach ($items as $item) {
			$id = $item['id'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$postType = $item['postType'] ?? '';
			$status = $item['status'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$viewLink = $item['viewLink'] ?? '';
			$editLink = $item['editLink'] ?? '';
			$subtitle = [];

			if ($postType) {
				$subtitle[] = ($postType === 'wp_block' ? __('Patterns', 'eightshift-forms') : ucfirst($postType));
			}

			if ($status !== 'publish') {
				$subtitle[] = '<span>' . ucfirst($status) . '</span>';
			}

			$itemTitle = get_the_title($id) ?: __('No title', 'eightshift-forms');

			echo Helpers::render('card-listing', [
				'cardListingTitle' => $itemTitle . ($isDevMode ? " ({$id})" : ''),
				'cardListingUrl' => $item['editLink'] ?? '',
				'cardListingSubTitle' => implode('<span>|</span>', $subtitle),
				'cardListingIcon' => UtilsHelper::getUtilsIcons('post'),
				'cardListingRightContent' => Helpers::ensureString([
					...($viewLink ? [
						Helpers::render('button', [
							'buttonVariant' => 'primaryGhost',
							'buttonUrl' => $viewLink,
							'buttonLabel' => __('View', 'eightshift-forms'),
						]),
					] : []),
					...($editLink ? [
						Helpers::render('button', [
							'buttonVariant' => 'primaryGhost',
							'buttonUrl' => $editLink,
							'buttonLabel' => __('Edit', 'eightshift-forms'),
						]),
					] : []),
				]),
				'additionalClass' => Helpers::clsx([
					'esf:pl-10 esf:ml-30 esf:border-l esf:border-border',
				]),
			]);
		}
	} else {
		echo Helpers::render('card-listing', [
			'cardListingSubTitle' => $emptyContent,
			'additionalClass' => Helpers::clsx([
				'esf:pl-10 esf:ml-30 esf:border-l esf:border-border',
			]),
		]);
	}
	?>
</div>
