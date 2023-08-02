<?php

/**
 * Template for admin listing - item deails partial - used via ajax.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\Helpers\Helper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$items = $attributes['items'] ?? [];
$emptyContent = $attributes['emptyContent'] ?? '';
$integrationType = $attributes['type'] ?? '';

$sectionClass = $attributes['sectionClass'] ?? '';

?>
<div
	class="<?php echo esc_attr("{$sectionClass}__item-details js-es-admin-listing-item") ?>"
	data-integration-type="<?php echo esc_attr($integrationType); ?>"
>
	<?php
	if ($items) {
		foreach ($items as $item) {
			$editLink = $item['editLink'] ?? '';
			$viewLink = $item['viewLink'] ?? '';
			$formTitle = $item['title'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

			echo Components::render('card', [
				'cardTitle' => '<a href="' . $editLink . '">' . $formTitle . '</a>',
				'cardIndented' => true,
				'cardShowButtonsOnHover' => true,
				'cardIcon' => Helper::getProjectIcons('post'),
				'cardTrailingButtons' => [
					[
						'label' => __('Edit', 'eightshift-forms'),
						'url' => $editLink,
						'internal' => true,
					],
					[
						'label' => __('View', 'eightshift-forms'),
						'url' => $viewLink,
						'internal' => true,
					],
				],
			]);
		}
	} else { ?>
		<div class="<?php echo esc_attr("{$sectionClass}__item-details-empty") ?>">
			<?php echo esc_html($emptyContent); ?>
		</div>
	<?php } ?>
</div>
