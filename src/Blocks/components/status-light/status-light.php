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
		$iconColorClass = 'esf:bg-green-500 esf:border-green-600 esf:shadow-green-700/30';
		break;
	case 'error':
		$iconColorClass = 'esf:bg-red-500 esf:border-red-600 esf:shadow-red-700/30';
		break;
	case 'info':
		$iconColorClass = 'esf:bg-sky-500 esf:border-sky-600 esf:shadow-sky-700/30';
		break;
	default:
		$iconColorClass = 'esf:bg-yellow-500 esf:border-yellow-600 esf:shadow-yellow-700/30';
		break;
}
$iconClass = Helpers::clsx([
	'esf:size-20 esf:rounded-full esf:border esf:bg-radial esf:from-white/25 esf:to-white/0 esf:inset-shadow-sm esf:inset-shadow-black/5 esf:shadow-sm',
	$iconColorClass,
]);

?>

<div class="<?php echo esc_attr($iconClass); ?>"></div>
