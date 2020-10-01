<?php
/**
 * Template for the Select Block view.
 *
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

use Eightshift_Forms\Helpers\Components;

error_log(print_r($attributes, true));
$attributes['innerBlockContent'] = $inner_block_content;

echo \wp_kses_post( Components::render( 'select', $attributes ) );
