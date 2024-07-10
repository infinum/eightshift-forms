<?php

/**
 * Template for the result output item block view.
 *
 * @package EightshiftFormsAddonComputedFields
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$resultOutputItemName = Helpers::checkAttr('resultOutputItemName', $attributes, $manifest);
$resultOutputItemValue = Helpers::checkAttr('resultOutputItemValue', $attributes, $manifest);

if (!$resultOutputItemName || !$resultOutputItemValue) {
	return;
}

$resultAttrs = [
	UtilsHelper::getStateAttribute('resultOutputItemKey') => esc_attr($resultOutputItemName),
	UtilsHelper::getStateAttribute('resultOutputItemValue') => esc_attr($resultOutputItemValue),
];

$resultAttrsOutput = '';
foreach ($resultAttrs as $key => $value) {
	$resultAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
}

$resultClass = Helpers::classnames([
	Helpers::selector($blockClass, $blockClass),
	UtilsHelper::getStateSelector('isHidden'),
	UtilsHelper::getStateSelector('resultOutputItem'),
]);

$data = isset($_GET[UtilsHelper::getStateSuccessRedirectUrlKey('data')]) ? json_decode(esFormsDecryptor(sanitize_text_field(wp_unslash($_GET[UtilsHelper::getStateSuccessRedirectUrlKey('data')]))), true) : [];

?>

<div class="<?php echo esc_attr($resultClass); ?>" <?php echo $resultAttrsOutput; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>>
	<?php echo $renderContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>
</div>
