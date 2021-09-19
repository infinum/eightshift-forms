<?php

/**
 * Template for the Radio Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$blockClass = $attributes['blockClass'] ?? '';

?>

<div class="<?php echo esc_attr($blockClass); ?>">
	<?php
	echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'radio',
		Components::props('radio', $attributes, [
			'radioContent' => $innerBlockContent,
		])
	);
	?>
</div>
