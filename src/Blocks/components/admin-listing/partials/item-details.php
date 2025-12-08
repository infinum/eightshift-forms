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
	class="<?php echo esc_attr("{$sectionClass}__item-details {$selectorJsItem}") ?>"
	<?php
	foreach ($additionalAttributes as $key => $value) {
		if (!empty($key) && !empty($value)) {
			echo wp_kses_post(" {$key}='" . $value . "'");
		}
	}
	?>>
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
				$subtitle[] = '<span class="status-text">' . ucfirst($status) . '</span>';
			}

			$itemTitle = get_the_title($id) ?: __('No title', 'eightshift-forms');

			echo Helpers::render('card-inline', [
				'cardInlineTitle' => $itemTitle . ($isDevMode ? " ({$id})" : ''),
				'cardInlineTitleLink' => $item['editLink'] ?? '',
				'cardInlineSubTitle' => implode('<span>|</span>', $subtitle),
				'cardInlineIndented' => true,
				'cardInlineIcon' => UtilsHelper::getUtilsIcons('post'),
				'cardInlineRightContent' => Helpers::ensureString([
					...($viewLink ? [
						Helpers::render('submit', [
							'submitVariant' => 'ghost',
							'submitButtonAsLink' => true,
							'submitButtonAsLinkUrl' => $viewLink,
							'submitValue' => __('View', 'eightshift-forms'),
						]),
					] : []),
					...($editLink ? [
						Helpers::render('submit', [
							'submitVariant' => 'ghost',
							'submitButtonAsLink' => true,
							'submitButtonAsLinkUrl' => $editLink,
							'submitValue' => __('Edit', 'eightshift-forms'),
						]),
					] : []),
				]),
			]);
		}
	} else {
		echo Helpers::render('card-inline', [
			'cardInlineTitle' => $emptyContent,
			'cardInlineIndented' => true,
			'cardInlineInvalid' => true,
		]);
	}
	?>
</div>
