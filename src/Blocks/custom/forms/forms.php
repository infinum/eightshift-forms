<?php

/**
 * Template for the Forms Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\AdminMenus\FormSettingsAdminSubMenu;
use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Geolocation\Geolocation;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Manifest\Manifest;
use EightshiftForms\Settings\Settings\SettingsSettings;

$manifest = Components::getManifest(__DIR__);
$manifestInvalid = Components::getManifest(dirname(__DIR__, 2) . '/components/invalid');

echo Components::outputCssVariablesGlobal(); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped

$checkStyleEnqueue = $this->isCheckboxOptionChecked(SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_STYLE_KEY, SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY);

$blockClass = $attributes['blockClass'] ?? '';

// Check formPost ID prop.
$formsFormPostId = Components::checkAttr('formsFormPostId', $attributes, $manifest);
$formsStyle = Components::checkAttr('formsStyle', $attributes, $manifest);
$formsServerSideRender = Components::checkAttr('formsServerSideRender', $attributes, $manifest);
$formsFormDataTypeSelector = Components::checkAttr('formsFormDataTypeSelector', $attributes, $manifest);
$formsFormGeolocation = Components::checkAttr('formsFormGeolocation', $attributes, $manifest);
$formsFormGeolocationAlternatives = Components::checkAttr('formsFormGeolocationAlternatives', $attributes, $manifest);
$formsConditionalTagsRules = Components::checkAttr('formsConditionalTagsRules', $attributes, $manifest);
$formsDownloads = Components::checkAttr('formsDownloads', $attributes, $manifest);
$formsSuccessRedirectVariation = Components::checkAttr('formsSuccessRedirectVariation', $attributes, $manifest);

// Override form ID in case we use geo location but use this feature only on frontend.
if (!$formsServerSideRender) {
	$formsFormPostId = apply_filters(Geolocation::GEOLOCATION_IS_USER_LOCATED, $formsFormPostId, $formsFormGeolocation, $formsFormGeolocationAlternatives);
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
		$formsClassNotSet = Components::selector($blockClass, $blockClass, 'not-set');
		?>
			<div class="<?php echo esc_attr($formsClass); ?> <?php echo esc_attr($formsClassNotSet); ?>">
				<img class="<?php echo esc_attr("{$blockClass}__not-set-image") ?>" src="<?php echo esc_url(apply_filters(Manifest::MANIFEST_ITEM, 'cover.jpg')); ?>" />
				<div class="<?php echo esc_attr("{$blockClass}__not-set-text") ?>"><?php esc_html_e('Please select form to show from the blocks sidebar.', 'eightshift-forms'); ?></div>
			</div>
		<?php

		return;
	}

	// Not published or removed at somepoint.
	if (get_post_status($formsFormPostId) !== 'publish') {
		echo Components::render(
			'invalid',
			[
				'heading' => __('Form cannot be found', 'eightshift-forms'),
				'text' => __('It might not be published yet or it\'s not available anymore.', 'eightshift-forms'),
			]
		);

		return;
	}
}

?>

<div class="<?php echo esc_attr($formsClass); ?>">
	<?php if (is_user_logged_in() && !is_admin()) { ?>
		<div class="<?php echo esc_attr("{$blockClass}__edit-wrap") ?>">
			<?php if (current_user_can(Forms::POST_CAPABILITY_TYPE)) { ?>
				<a class="<?php echo esc_attr("{$blockClass}__edit-link") ?>" href="<?php echo esc_url(Helper::getFormEditPageUrl($formsFormPostId)) ?>" title="<?php esc_html_e('Edit form', 'eightshift-forms'); ?>">
					<span class="<?php echo esc_attr("{$blockClass}__edit-link-icon dashicons dashicons-edit"); ?> "></span>
				</a>
			<?php } ?>

			<?php if (current_user_can(FormSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY)) { ?>
				<a class="<?php echo esc_attr("{$blockClass}__edit-link") ?>" href="<?php echo esc_url(Helper::getSettingsPageUrl($formsFormPostId)) ?>" title="<?php esc_html_e('Edit settings', 'eightshift-forms'); ?>">
					<span class="<?php echo esc_attr("{$blockClass}__edit-link-icon dashicons dashicons-admin-settings"); ?> "></span>
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
		if ($block['blockName'] === Components::getSettingsNamespace() . '/form-selector') {
			$blocks[$key]['attrs']['formSelectorFormPostId'] = $formsFormPostId;

			if (isset($block['innerBlocks'])) {
				foreach ($block['innerBlocks'] as $innerKey => $innerBlock) {
					$blockName = Helper::getBlockNameDetails($innerBlock['blockName'])['name'];

					$blocks[$key]['innerBlocks'][$innerKey]['attrs']["{$blockName}FormSuccessRedirectVariation"] = $formsSuccessRedirectVariation;
					$blocks[$key]['innerBlocks'][$innerKey]['attrs']["{$blockName}FormDownloads"] = $formsDownloads;
					$blocks[$key]['innerBlocks'][$innerKey]['attrs']["{$blockName}FormType"] = $blockName;
					$blocks[$key]['innerBlocks'][$innerKey]['attrs']["{$blockName}FormPostId"] = $formsFormPostId;
					$blocks[$key]['innerBlocks'][$innerKey]['attrs']["{$blockName}FormDataTypeSelector"] = $formsFormDataTypeSelector;
					$blocks[$key]['innerBlocks'][$innerKey]['attrs']["{$blockName}FormServerSideRender"] = $formsServerSideRender;
					$blocks[$key]['innerBlocks'][$innerKey]['attrs']["{$blockName}FormDisabledDefaultStyles"] = $checkStyleEnqueue;
					$blocks[$key]['innerBlocks'][$innerKey]['attrs']["{$blockName}FormConditionalTags"] = wp_json_encode($formsConditionalTagsRules);
					$blocks[$key]['innerBlocks'][$innerKey]['attrs']["blockSsr"] = $formsServerSideRender;

					if (isset($innerBlock['innerBlocks'])) {
						foreach ($innerBlock['innerBlocks'] as $inKey => $inBlock) {
							$name = Helper::getBlockNameDetails($inBlock['blockName'])['name'];

							switch ($name) {
								case 'submit':
									$blocks[$key]['innerBlocks'][$innerKey]['innerBlocks'][$inKey]['attrs']["{$name}SubmitServerSideRender"] = $formsServerSideRender;
									$blocks[$key]['innerBlocks'][$innerKey]['innerBlocks'][$inKey]['attrs']["blockSsr"] = $formsServerSideRender;
									break;
								case 'phone':
								case 'country':
									$blocks[$key]['innerBlocks'][$innerKey]['innerBlocks'][$inKey]['attrs'][Components::kebabToCamelCase("{$name}-{$name}FormPostId")] = $formsFormPostId;
									break;
							}
						}
					}
				}
			}
		}
	}

	// Render blocks.
	foreach ($blocks as $block) {
		// phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
		echo apply_filters('the_content', render_block($block));
	}
	?>
</div>

<?php

echo Components::outputCssVariablesInline(); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
