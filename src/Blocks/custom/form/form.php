<?php

/**
 * Template for the Form Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$formPostId = Components::checkAttr('formPostId', $attributes, $manifest);

$formClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
]);

?>

<div class="<?php echo esc_attr($formClass); ?>">
	<?php
	echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'form',
		Components::props('form', $attributes, [
			'formContent' => $innerBlockContent,
			'formPostId' => $formPostId,
		])
	);
	?>
</div>
