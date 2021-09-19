<?php

/**
 * Template for the Checkbox Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

?>

<div class="<?php echo esc_attr($blockClass); ?>">
	<?php
	echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'checkbox',
		Components::props('checkbox', $attributes, [
			'checkboxContent' => $innerBlockContent
		])
	);
	?>
</div>
