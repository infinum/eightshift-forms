<?php

/**
 * Template for the Forms Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Form\Form;
use EightshiftForms\Geolocation\SettingsGeolocation;
use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsEncryption;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);
$manifestInvalid = Helpers::getComponent('invalid');
$manifestSettings = Helpers::getSettings();

// Check if there is any reason not to render forms block.
if (!apply_filters(Form::FILTER_FORMS_BLOCK_SHOULD_RENDER, true, $attributes, $manifest)) {
	return;
}

echo Helpers::outputCssVariablesGlobal(); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped

$blockClass = isset($attributes['blockClass']) ? $attributes['blockClass'] : "{$manifestSettings['blockClassPrefix']}-{$manifest['blockName']}";

// Check formPost ID prop.
$formsFormPostId = Helpers::checkAttr('formsFormPostId', $attributes, $manifest);
$formsStyle = Helpers::checkAttr('formsStyle', $attributes, $manifest);
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

// Not published or removed at some point.
if ((!$formsFormPostId || get_post_status($formsFormPostId) !== 'publish')) {
	if (!is_user_logged_in()) {
		return;
	}

	echo Helpers::render(
		'invalid',
		[
			'heading' => __('Form cannot be found', 'eightshift-forms'),
			'text' => __('It might not be published yet or it\'s not available anymore.', 'eightshift-forms'),
		]
	);

	return;
}

$allForms = [
	$formsFormPostId,
];

if ($formsFormGeolocationAlternatives && apply_filters(SettingsGeolocation::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
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

$twClassesData = FormsHelper::getTwSelectorsData($attributes);
$twClasses = FormsHelper::getTwSelectors($twClassesData, ['forms']);

$formsClass = Helpers::classnames([
	FormsHelper::getTwBase($twClasses, 'forms', $blockClass),
	UtilsHelper::getStateSelector('forms'),
	Helpers::selector($hasGeolocation, UtilsHelper::getStateSelector('isGeoLoading')),
	$attributes['className'] ?? '',
	...$formsStyleOutput,
]);

?>

<div
	class="<?php echo esc_attr($formsClass); ?>"
	<?php
	echo $formsAttrsOutput; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
	?>>
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
			$manifest
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
			'loaderTwSelectorsData' => $twClassesData,
		])
	);
	?>
</div>

<?php

echo Helpers::outputCssVariablesInline(); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
