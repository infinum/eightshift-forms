<?php

/**
 * Template for admin listing - item deails partial - used via ajax.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\Helpers\Helper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getComponent('admin-listing');
$manifestCustomFormAttrs = Components::getSettings()['customFormAttrs'];
$componentJsItemClass = $manifest['componentJsItemClass'] ?? '';

$items = $attributes['items'] ?? [];
$emptyContent = $attributes['emptyContent'] ?? '';
$integrationType = $attributes['type'] ?? '';
$additionalAttributes = $attributes['additionalAttributes'] ?? [];
$sectionClass = $attributes['sectionClass'] ?? '';

?>
<div
	class="<?php echo esc_attr("{$sectionClass}__item-details {$componentJsItemClass}") ?>"
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
			$editLink = $item['editLink'] ?? '';
			$viewLink = $item['viewLink'] ?? '';
			$formTitle = $item['title'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

			echo Components::render('card-inline', [
				'cardInlineTitle' => $formTitle,
				'cardInlineTitleLink' => $editLink,
				'cardInlineIndented' => true,
				'cardInlineIcon' => Helper::getProjectIcons('post'),
				'cardInlineRightContent' => Components::ensureString([
					Components::render('submit', [
						'submitVariant' => 'ghost',
						'submitButtonAsLink' => true,
						'submitButtonAsLinkUrl' => $viewLink,
						'submitValue' => \__('View', 'eightshift-forms'),
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
