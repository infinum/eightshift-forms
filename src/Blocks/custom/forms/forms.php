<?php

/**
 * Template for the Forms Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Form\Form;
use EightshiftForms\Helpers\Encryption;
use EightshiftForms\Helpers\Helper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);
$manifestInvalid = Components::getComponent('invalid');
$manifestSettings = Components::getSettings();

echo Components::outputCssVariablesGlobal(); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped

$blockClass = isset($attributes['blockClass']) ? $attributes['blockClass'] : "{$manifestSettings['blockClassPrefix']}-{$manifest['blockName']}";

// Check formPost ID prop.
$formsFormPostId = Components::checkAttr('formsFormPostId', $attributes, $manifest);
$formsStyle = Components::checkAttr('formsStyle', $attributes, $manifest);
$formsServerSideRender = Components::checkAttr('formsServerSideRender', $attributes, $manifest);
$formsFormGeolocation = Components::checkAttr('formsFormGeolocation', $attributes, $manifest);
$formsFormGeolocationAlternatives = Components::checkAttr('formsFormGeolocationAlternatives', $attributes, $manifest);

$formsStyleOutput = [];
if ($formsStyle && gettype($formsStyle) === 'array') {
	$formsStyleOutput = array_map(
		static function ($item) use ($blockClass) {
			return Components::selector(true, $blockClass, '', $item);
		},
		$formsStyle
	);
}

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

$allForms = [
	$formsFormPostId,
];

if ($formsFormGeolocationAlternatives) {
	$allForms = [
		...$allForms,
		...array_map(
			static function ($item) {
				return $item['formId'];
			},
			$formsFormGeolocationAlternatives
		),
	];
}

$formAttrs = [];
$hasGeolocation = false;

if ($formsFormGeolocation || $formsFormGeolocationAlternatives) {
	$hasGeolocation = true;
	$formAttrs[Helper::getStateAttribute('formGeolocation')] = Encryption::encryptor(wp_json_encode([
		'id' => $formsFormPostId,
		'geo' => $formsFormGeolocation,
		'alt' => $formsFormGeolocationAlternatives,
	]));
}

$formsAttrsOutput = '';
if ($formAttrs) {
	foreach ($formAttrs as $key => $value) {
		$formsAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

$formsClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	Helper::getStateSelector('forms'),
	Components::selector($hasGeolocation, Helper::getStateSelector('isGeoLoading')),
	$attributes['className'] ?? '',
	...$formsStyleOutput,
]);

?>

<div class="<?php echo esc_attr($formsClass); ?>" <?php echo $formsAttrsOutput; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>>
	<?php
	foreach ($allForms as $formId) {
		// Convert blocks to array.
		$blocks = parse_blocks(get_the_content(null, false, $formId));

		// Bailout if it fails for some reason.
		if (!$blocks) {
			return;
		}

		$output = apply_filters(
			Form::FILTER_FORMS_BLOCK_MODIFICATIONS,
			$blocks,
			array_merge(
				$attributes,
				[
					'formsFormPostId' => $formId,
				]
			),
		);

		// Render blocks.
		foreach ($output as $block) {
			// phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
			echo apply_filters('the_content', render_block($block));
		}
	}

	echo Components::render(
		'loader',
		Components::props('loader', $attributes, [
			'loaderIsGeolocation' => true,
		])
	);
	?>
</div>

<?php

echo Components::outputCssVariablesInline(); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
