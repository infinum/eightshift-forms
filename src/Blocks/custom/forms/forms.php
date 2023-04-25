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
$manifestInvalid = Components::getComponent('invalid');
$manifestUtils = Components::getComponent('utils');

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
$formsAttrs = Components::checkAttr('formsAttrs', $attributes, $manifest);

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
					<?php echo $manifestUtils['icons']['edit']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
				</a>
			<?php } ?>

			<?php if (current_user_can(FormSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY)) { ?>
				<a class="<?php echo esc_attr("{$blockClass}__edit-link") ?>" href="<?php echo esc_url(Helper::getSettingsPageUrl($formsFormPostId)) ?>" title="<?php esc_html_e('Edit settings', 'eightshift-forms'); ?>">
				<?php echo $manifestUtils['icons']['settings']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
				</a>
			<?php } ?>

			<?php if (current_user_can(Forms::POST_CAPABILITY_TYPE)) { ?>
				<a class="<?php echo esc_attr("{$blockClass}__edit-link") ?>" href="<?php echo esc_url(Helper::getSettingsGlobalPageUrl()) ?>" title="<?php esc_html_e('Edit global settings', 'eightshift-forms'); ?>">
					<?php echo $manifestUtils['icons']['dashboard']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
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

	$formsNamespace = Components::getSettingsNamespace();

	// Iterate blocks an children by passing them form ID.
	foreach ($blocks as $key => $block) {
		if ($block['blockName'] === "{$formsNamespace}/form-selector") {
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
					$blocks[$key]['innerBlocks'][$innerKey]['attrs']["{$blockName}FormAttrs"] = $formsAttrs;
					$blocks[$key]['innerBlocks'][$innerKey]['attrs']["blockSsr"] = $formsServerSideRender;

					if (isset($innerBlock['innerBlocks'])) {
						foreach ($innerBlock['innerBlocks'] as $inKey => $inBlock) {
							$nameDetails = Helper::getBlockNameDetails($inBlock['blockName']);
							$name = $nameDetails['name'];
							$namespace = $nameDetails['namespace'];

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

							// Add custom field block around none forms block to be able to use positioning.
							if ($namespace !== $formsNamespace) {
								$customUsedAttrsDiff = array_intersect_key(
									$blocks[$key]['innerBlocks'][$innerKey]['innerBlocks'][$inKey]['attrs'] ?? [],
									Components::getComponent('field')['attributes']
								);

								$customUsedAttrs = [];

								if ($customUsedAttrsDiff) {
									foreach ($customUsedAttrsDiff as $customDiffKey => $customDiffValue) {
										$customUsedAttrs["field" . ucfirst($customDiffKey)] = $customDiffValue;
									}
								}

								$blocks[$key]['innerBlocks'][$innerKey]['innerBlocks'][$inKey] = [];
								$blocks[$key]['innerBlocks'][$innerKey]['innerBlocks'][$inKey]['blockName'] = "{$formsNamespace}/field";
								$blocks[$key]['innerBlocks'][$innerKey]['innerBlocks'][$inKey]['attrs'] = array_merge(
									[
										'fieldFieldContent' => apply_filters('the_content', render_block($inBlock)),
										'fieldFieldHideLabel' => true,
										'fieldFieldUseError' => false,
									],
									$customUsedAttrs
								);
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
