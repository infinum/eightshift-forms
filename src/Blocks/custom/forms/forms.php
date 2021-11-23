<?php

/**
 * Template for the Forms Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Manifest\Manifest;

$manifest = Components::getManifest(__DIR__);
$globalManifest = Components::getManifest(dirname(__DIR__, 2));

echo Components::outputCssVariablesGlobal($globalManifest); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

$blockClass = $attributes['blockClass'] ?? '';

// Check formPost ID prop.
$formsFormPostId = Components::checkAttr('formsFormPostId', $attributes, $manifest);
$formsStyle = Components::checkAttr('formsStyle', $attributes, $manifest);
$formsServerSideRender = Components::checkAttr('formsServerSideRender', $attributes, $manifest);
$formsFormTypeSelector = Components::checkAttr('formsFormTypeSelector', $attributes, $manifest);

$formsClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	Components::selector($formsStyle, $blockClass, '', $formsStyle),
	Components::selector(!$formsFormPostId, $blockClass, '', 'not-set'),
	$attributes['className'] ?? '',
]);

// If form is not published exit.
if (get_post_status($formsFormPostId) !== 'publish') {
	return;
}

?>

<div class="<?php echo esc_attr($formsClass); ?>">
	<?php
	// Bailout if form post ID is missing.
	if (!$formsFormPostId && $formsServerSideRender) {
		?>
			<img class="<?php echo esc_attr("{$blockClass}__image") ?>" src="<?php echo esc_url(\apply_filters(Manifest::MANIFEST_ITEM, 'cover.png')); ?>" />
			<div class="<?php echo esc_attr("{$blockClass}__text") ?>"><?php esc_html_e('Please select form to show from the blocks sidebar.', 'eightshift-forms'); ?></div>
		<?php
	}

	if ($formsFormPostId) {
		// Convert blocks to array.
		$blocks = parse_blocks(get_the_content(null, false, $formsFormPostId));

		// Bailout if it fails for some reason.
		if (!$blocks) {
			return;
		}

		// Encrypt.
		$formsFormPostId = (string) Helper::encryptor('encrypt', $formsFormPostId);

		// Iterate blocks an children by passing them form ID.
		foreach ($blocks as $key => $block) {
			if ($block['blockName'] === $globalManifest['namespace'] . '/form-selector') {
				$blocks[$key]['attrs']['formSelectorFormPostId'] = $formsFormPostId;

				foreach ($block['innerBlocks'] as $innerKey => $innerBlock) {
					$blockName = Components::kebabToCamelCase(explode('/', $innerBlock['blockName'])[1]);
					$blocks[$key]['innerBlocks'][$innerKey]['attrs']["{$blockName}FormPostId"] = $formsFormPostId;
					$blocks[$key]['innerBlocks'][$innerKey]['attrs']["{$blockName}FormTypeSelector"] = $formsFormTypeSelector;
				}
			}
		}

		// Render blocks.
		foreach ($blocks as $block) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo \apply_filters('the_content', \render_block($block));
		}
	}
	?>
</div>
