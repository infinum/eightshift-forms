<?php

/**
 * Template for the group component when you must save all items in one field.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';

$groupContent = Helpers::checkAttr('groupContent', $attributes, $manifest);
$groupName = Helpers::checkAttr('groupName', $attributes, $manifest);

$groupClass = Helpers::clsx([
	$componentClass,
	UtilsHelper::getStateSelector('group'),
	'esf:flex esf:flex-col esf:gap-15',
]);

if (!$groupName || !$groupContent) {
	return;
}

?>

<div
	class="<?php echo esc_attr($groupClass); ?>"
	data-field-id="<?php echo esc_attr($groupName); ?>"
	data-group-save-as-one-field="true">

	<?php echo $groupContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
	?>
</div>
