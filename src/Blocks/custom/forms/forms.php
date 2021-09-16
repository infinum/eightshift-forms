<?php

/**
 * Template for the Forms Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$formsForm = Components::checkAttr('formsForm', $attributes, $manifest);
?>

<div class="<?php echo esc_attr($blockClass); ?>">
	<?php
	echo \apply_filters('the_content', \get_post_field('post_content', $formsForm))
	?>
</div>
