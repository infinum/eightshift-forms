<?php

/**
 * Template for the Form success / error message component.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use EightshiftLibs\Helpers\Components;

$blockClass = $attributes['blockClass'] ?? '';
$message = $attributes['message'] ?? '';
$messageType = $attributes['type'] ?? 'success';
$theme = $attributes['theme'] ?? '';

$componentClass = 'form-message';

$blockClasses = Components::classnames([
	$componentClass,
	"js-{$componentClass}",
	"js-{$componentClass}--{$messageType}",
	"{$componentClass}__type--{$messageType}",
	'is-form-message-hidden',
	! empty($blockClass) ? "{$blockClass}__{$componentClass}" : '',
	! empty($theme) ? "{$componentClass}__theme--{$theme}" : '',
]);

?>

<div class="<?php echo esc_attr($blockClasses); ?>">
	<?php echo esc_html($message); ?>
</div>
