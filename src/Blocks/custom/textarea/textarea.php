<?php

/**
 * Template for the textarea Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$blockClass = $attributes['blockClass'] ?? '';
?>

<div class="<?php echo esc_attr($blockClass); ?>">
	<?php
	echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'textarea',
		Components::props('textarea', $attributes)
	);
	?>
</div>
