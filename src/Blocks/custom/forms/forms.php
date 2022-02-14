<?php

/**
 * Template for the Forms Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\AdminMenus\FormSettingsAdminSubMenu;
use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Geolocation\Geolocation;
use EightshiftForms\Geolocation\SettingsGeolocation;
use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Manifest\Manifest;
use EightshiftForms\Settings\Settings\SettingsGeneral;

$manifest = Components::getManifest(__DIR__);
$globalManifest = Components::getManifest(dirname(__DIR__, 2));
$manifestInvalid = Components::getManifest(dirname(__DIR__, 2) . '/components/invalid');

if (!$this->isCheckboxOptionChecked(SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_SCRIPT_KEY, SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY)) {
	echo Components::outputCssVariablesGlobal($globalManifest); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

$blockClass = $attributes['blockClass'] ?? '';
$invalidClass = $manifestInvalid['componentClass'] ?? '';

// Check formPost ID prop.
$formsFormPostId = Components::checkAttr('formsFormPostId', $attributes, $manifest);
$formsStyle = Components::checkAttr('formsStyle', $attributes, $manifest);
$formsServerSideRender = Components::checkAttr('formsServerSideRender', $attributes, $manifest);
$formsFormDataTypeSelector = Components::checkAttr('formsFormDataTypeSelector', $attributes, $manifest);
$formsFormGeolocation = Components::checkAttr('formsFormGeolocation', $attributes, $manifest);
$formsFormGeolocationAlternatives = Components::checkAttr('formsFormGeolocationAlternatives', $attributes, $manifest);

// Override form ID in case we use geo location but use this feature only on frontend.
if (\apply_filters(SettingsGeolocation::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false) && !$formsServerSideRender) {
	$formsFormPostId = \apply_filters(Geolocation::GEOLOCATION_IS_USER_LOCATED, $formsFormPostId, $formsFormGeolocation, $formsFormGeolocationAlternatives);
}

$formsClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	Components::selector($formsStyle, $blockClass, '', $formsStyle),
	$attributes['className'] ?? '',
]);

// Return nothing if it is on frontend.
if (!$formsServerSideRender && (!$formsFormPostId || get_post_status($formsFormPostId) !== 'publish')) {
	return;
}

// Bailout if form post ID is missing.
if ($formsServerSideRender) {
	// Missing form ID.
	if (!$formsFormPostId) {
		$formsClassNotSet = Components::selector($blockClass, $blockClass, '', 'not-set');
		?>
			<div class="<?php echo esc_attr($formsClass); ?> <?php echo esc_attr($formsClassNotSet); ?>">
				<img class="<?php echo esc_attr("{$blockClass}__image") ?>" src="<?php echo esc_url(\apply_filters(Manifest::MANIFEST_ITEM, 'cover.png')); ?>" />
				<div class="<?php echo esc_attr("{$blockClass}__text") ?>"><?php esc_html_e('Please select form to show from the blocks sidebar.', 'eightshift-forms'); ?></div>
			</div>
		<?php

		return;
	}

	// Not published or removed at somepoint.
	if (get_post_status($formsFormPostId) !== 'publish') {
		$formsClassNotPublished = Components::selector($blockClass, $invalidClass);
		?>
			<div class="<?php echo esc_attr($formsClass); ?> <?php echo esc_attr($formsClassNotPublished); ?>">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M4.71991 1.60974C3.11997 2.29956 2 3.89096 2 5.74394C2 7.70327 3.25221 9.37013 5 9.98788V17C5 17.8284 5.67157 18.5 6.5 18.5C7.32843 18.5 8 17.8284 8 17V9.98788C9.74779 9.37013 11 7.70327 11 5.74394C11 3.78461 9.74779 2.11775 8 1.5V5.74394C8 6.57237 7.32843 7.24394 6.5 7.24394C5.67157 7.24394 5 6.57237 5 5.74394V1.5C4.90514 1.53353 4.81173 1.57015 4.71991 1.60974Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
					<path d="M13 13V16C13 17.3807 14.1193 18.5 15.5 18.5V18.5C16.8807 18.5 18 17.3807 18 16V13M13 13V10.5H14M13 13H18M18 13V10.5H17M14 10.5V5.5L13.5 3.5L14 1.5H17L17.5 3.5L17 5.5V10.5M14 10.5H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<br />
				<b><?php esc_html_e('Form cannot be found', 'eightshift-forms'); ?></b>
				<br />
				<?php esc_html_e('It might not be published yet or it\'s not available anymore.', 'eightshift-forms'); ?>
			</div>
		<?php

		return;
	}
}

?>

<div class="<?php echo esc_attr($formsClass); ?>">

	<?php if (is_user_logged_in() && !is_admin()) { ?>
		<div class="<?php echo esc_attr("{$blockClass}__edit-wrap") ?>">
			<?php if (current_user_can(Forms::POST_CAPABILITY_TYPE)) { ?>
				<a class="<?php echo esc_attr("{$blockClass}__edit-link") ?>" href="<?php echo esc_url(Helper::getFormEditPageUrl($formsFormPostId)) ?>">
					<span class="<?php echo \esc_attr("{$blockClass}__edit-link-icon dashicons dashicons-edit"); ?> "></span>
					<?php esc_html_e('Edit form', 'eightshift-forms'); ?>
				</a>
			<?php } ?>

			<?php if (current_user_can(FormSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY)) { ?>
				<a class="<?php echo esc_attr("{$blockClass}__edit-link") ?>" href="<?php echo esc_url(Helper::getSettingsPageUrl($formsFormPostId)) ?>">
					<span class="<?php echo \esc_attr("{$blockClass}__edit-link-icon dashicons dashicons-admin-settings"); ?> "></span>
					<?php esc_html_e('Edit settings', 'eightshift-forms'); ?>
				</a>
			<?php } ?>
		</div>
	<?php } ?>

	<?php
	// Convert blocks to array.
	$blocks = parse_blocks(get_the_content(null, false, $formsFormPostId));

	// Bailout if it fails for some reason.
	if (!$blocks) {
		return;
	}

	// Iterate blocks an children by passing them form ID.
	foreach ($blocks as $key => $block) {
		if ($block['blockName'] === $globalManifest['namespace'] . '/form-selector') {
			$blocks[$key]['attrs']['formSelectorFormPostId'] = $formsFormPostId;

			if (isset($block['innerBlocks'])) {
				foreach ($block['innerBlocks'] as $innerKey => $innerBlock) {
					$blockName = Components::kebabToCamelCase(explode('/', $innerBlock['blockName'])[1]);
					$blocks[$key]['innerBlocks'][$innerKey]['attrs']["{$blockName}FormPostId"] = $formsFormPostId;
					$blocks[$key]['innerBlocks'][$innerKey]['attrs']["{$blockName}FormDataTypeSelector"] = $formsFormDataTypeSelector;
					$blocks[$key]['innerBlocks'][$innerKey]['attrs']["{$blockName}FormServerSideRender"] = $formsServerSideRender;

					if (isset($innerBlock['innerBlocks'])) {
						foreach ($innerBlock['innerBlocks'] as $inKey => $inBlock) {
							$name = Components::kebabToCamelCase(explode('/', $inBlock['blockName'])[1]);

							if ($name === 'submit') {
								$blocks[$key]['innerBlocks'][$innerKey]['innerBlocks'][$inKey]['attrs']["{$name}SubmitServerSideRender"] = $formsServerSideRender;
							}
						}
					}
				}
			}
		}
	}

	// Render blocks.
	foreach ($blocks as $block) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo \apply_filters('the_content', \render_block($block));
	}
	?>
</div>
