<?php
/**
 * Template for the Paragraph Block view.
 *
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

$this->render_block_view(
  '/components/paragraph/paragraph.php',
  [
    'blockClass'  => $attributes['blockClass'] ?? '',
    'paragraph'   => $attributes['paragraph'] ?? [],
  ]
);
