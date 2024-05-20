<?php

/**
 * Template for the Forms Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Form\Form;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsEncryption;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);
$manifestInvalid = Helpers::getComponent('invalid');
$manifestSettings = Helpers::getSettings();

echo Helpers::outputCssVariablesGlobal(); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped

$blockClass = isset($attributes['blockClass']) ? $attributes['blockClass'] : "{$manifestSettings['blockClassPrefix']}-{$manifest['blockName']}";

// Check formPost ID prop.
$formsFormPostId = Helpers::checkAttr('formsFormPostId', $attributes, $manifest);
$formsStyle = Helpers::checkAttr('formsStyle', $attributes, $manifest);
$formsServerSideRender = Helpers::checkAttr('formsServerSideRender', $attributes, $manifest);
$formsFormGeolocation = Helpers::checkAttr('formsFormGeolocation', $attributes, $manifest);
$formsFormGeolocationAlternatives = Helpers::checkAttr('formsFormGeolocationAlternatives', $attributes, $manifest);

$formsStyleOutput = [];
if ($formsStyle && gettype($formsStyle) === 'array') {
	$formsStyleOutput = array_map(
		static function ($item) use ($blockClass) {
			return Helpers::selector(true, $blockClass, '', $item);
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
		echo Helpers::render(
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
	$formAttrs[UtilsHelper::getStateAttribute('formGeolocation')] = UtilsEncryption::encryptor(wp_json_encode([
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

$formsClass = Helpers::classnames([
	Helpers::selector($blockClass, $blockClass),
	UtilsHelper::getStateSelector('forms'),
	Helpers::selector($hasGeolocation, UtilsHelper::getStateSelector('isGeoLoading')),
	$attributes['className'] ?? '',
	...$formsStyleOutput,
]);

?>

<div class="<?php echo esc_attr($formsClass); ?>" <?php echo $formsAttrsOutput; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>>
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
			// phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
			echo apply_filters('the_content', render_block($block));
		}
	}

	echo Helpers::render(
		'loader',
		Helpers::props('loader', $attributes, [
			'loaderIsGeolocation' => true,
		])
	);
	?>
</div>

<?php

echo Helpers::outputCssVariablesInline(); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
