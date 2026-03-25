<?php

/**
 * Template for the Tabs Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$tabsContent = Helpers::checkAttr('tabsContent', $attributes, $manifest);

$tabsClass = Helpers::clsx([
	UtilsHelper::getStateSelectorAdmin('tabs'),
	'esf:flex esf:flex-wrap esf:gap-x-2 esf:gap-y-10',
]);

if (!$tabsContent) {
	return;
}

?>
<div class="<?php echo esc_attr($tabsClass); ?>">
	<?php
	echo $tabsContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
	?>
</div>
