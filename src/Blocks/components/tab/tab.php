<?php

/**
 * Template for the Tab Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifestTabs = Helpers::getComponent('tabs');
$componentClass = $manifest['componentClass'] ?? '';

$tabLabel = Helpers::checkAttr('tabLabel', $attributes, $manifest);
$tabContent = Helpers::checkAttr('tabContent', $attributes, $manifest);
$tabNoBg = Helpers::checkAttr('tabNoBg', $attributes, $manifest);

$tabLabelClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass, 'label'),
	UtilsHelper::getStateSelectorAdmin('tabsItem'),
]);

$tabContentClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass, 'content'),
	Helpers::selector($tabNoBg, $componentClass, 'content', 'no-bg'),
]);

if (!$tabLabel || !$tabContent) {
	return;
}

?>

<button type="button" class="<?php echo esc_attr($tabLabelClass); ?>" data-hash="<?php echo rawurlencode($tabLabel); ?>">
	<?php echo esc_html($tabLabel); ?>
</button>

<div class="<?php echo esc_attr($tabContentClass); ?>">
	<?php echo $tabContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
	?>
</div>
