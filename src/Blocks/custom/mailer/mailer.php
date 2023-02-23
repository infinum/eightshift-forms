<?php

/**
 * Template for the Mailer Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$blockClass = $attributes['blockClass'] ?? '';

?>

<div class="<?php echo esc_attr($blockClass); ?>">
	<?php
	// There is no bailout here in case of missing settings because custom form can be used only to redirecto to another page with form data.
	echo Components::render(
		'form',
		Components::props('form', $attributes, [
			'formContent' => $innerBlockContent,
		])
	);
	?>
</div>
