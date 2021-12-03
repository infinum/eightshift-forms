<?php

/**
 * Template for the Forms Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\AdminMenus\FormSettingsAdminSubMenu;
use EightshiftForms\CustomPostType\Forms;
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
				<div class="<?php echo esc_attr("{$blockClass}__text") ?>"><?php esc_html_e('Sorry, it looks like your form is not published or it is not available anymore.', 'eightshift-forms'); ?></div>
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
