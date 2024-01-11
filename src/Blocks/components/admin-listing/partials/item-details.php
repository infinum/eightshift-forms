<?php

/**
 * Template for admin listing - item deails partial - used via ajax.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getComponent('admin-listing');
$selectorJsItem = UtilsHelper::getStateSelectorAdmin('listingItem');

$isDevMode = UtilsDeveloperHelper::isDeveloperModeActive();

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
	?>
>
	<?php
	if ($items) {
		foreach ($items as $item) {
			$id = $item['id'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$postType = $item['postType'] ?? '';

			$itemTitle = get_the_title($id);

			echo Components::render('card-inline', [
				// translators: %1$s is the post type, %2$s is the post title.
				'cardInlineTitle' => sprintf(__('%1$s - %2$s', 'eightshift-forms'), ucfirst($postType), $itemTitle) . ($isDevMode ? " ({$id})" : ''),
				'cardInlineTitleLink' => $item['editLink'] ?? '',
				'cardInlineIndented' => true,
				'cardInlineIcon' => UtilsHelper::getUtilsIcons('post'),
				'cardInlineRightContent' => Components::ensureString([
					Components::render('submit', [
						'submitVariant' => 'ghost',
						'submitButtonAsLink' => true,
						'submitButtonAsLinkUrl' => $item['viewLink'] ?? '',
						'submitValue' => __('View', 'eightshift-forms'),
					]),
				]),
			]);
		}
	} else {
		echo Components::render('card-inline', [
			'cardInlineTitle' => $emptyContent,
			'cardInlineIndented' => true,
			'cardInlineInvalid' => true,
		]);
	}
	?>
</div>
