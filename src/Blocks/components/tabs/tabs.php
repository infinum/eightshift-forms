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
	'esf:flex esf:flex-col esf:gap-15',
]);

if (!$tabsContent) {
	return;
}

?>
<div class="<?php echo esc_attr($tabsClass); ?>">
	<?php
	echo wp_kses_post($tabsContent);
	?>
</div>
