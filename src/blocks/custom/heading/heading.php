<?php

/**
 * Template for the Heading Block view.
 *
 * @package EightshiftForms
 */

use EightshiftLibs\Helpers\Components;

echo \wp_kses_post(Components::render('heading', $attributes));
