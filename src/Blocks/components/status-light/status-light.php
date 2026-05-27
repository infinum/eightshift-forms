<?php

/**
 * Template for the Status Light Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$additionalAttributes = $attributes['additionalAttributes'] ?? [];

$statusLightType = Helpers::checkAttr('statusLightType', $attributes, $manifest);

switch ($statusLightType) {
	case 'success':
		$iconColorClass = 'esf:bg-green-500';
		break;
	case 'error':
		$iconColorClass = 'esf:bg-red-500';
		break;
	case 'info':
		$iconColorClass = 'esf:bg-sky-500';
		break;
	default:
		$iconColorClass = 'esf:bg-yellow-500';
		break;
}
$iconClass = Helpers::clsx([
	'esf:w-20 esf:h-20 esf:rounded-full',
	$iconColorClass,
]);

?>

<div class="<?php echo esc_attr($iconClass); ?>"></div>
