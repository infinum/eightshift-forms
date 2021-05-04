<?php

/**
 * Template for the Paragraph Block view.
 *
 * @package EightshiftForms
 */

use EightshiftLibs\Helpers\Components;

echo \wp_kses_post(Components::render('paragraph', $attributes ?? []));
