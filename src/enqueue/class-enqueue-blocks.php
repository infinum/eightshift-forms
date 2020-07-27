<?php
/**
 * Enqueue class used to define all script and style enqueue for Gutenberg blocks.
 *
 * @package Eightshift_Libs\Enqueue
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Enqueue;

use Eightshift_Libs\Manifest\Manifest_Data;
use Eightshift_Libs\Enqueue\Enqueue_Blocks as Lib_Enqueue_Blocks;

/**
 * Enqueue_Blocks class.
 */
class Enqueue_Blocks extends Lib_Enqueue_Blocks {

  /**
   * Create a new admin instance.
   *
   * @param Manifest_Data          $manifest               Inject manifest which holds data about assets from manifest.json.
   * @param Localization_Constants $localization_constants Injected object which holds all localizations shared between editor and frontend.
   */
  public function __construct( Manifest_Data $manifest, Localization_Constants $localization_constants ) {
    $this->manifest               = $manifest;
    $this->localization_constants = $localization_constants;
  }

  /**
   * Register all the hooks
   */
  public function register() {
    parent::register();

    add_action( 'enqueue_block_editor_assets', array( $this, 'localize_script' ) );
  }

  /**
   * Sends some data to editor.
   *
   * @return void
   */
  public function localize_script() {
    $handler = "{$this->manifest->get_config()->get_project_prefix()}-block-editor-scripts";

    foreach ( $this->get_localizations() as $object_name => $data_array ) {
      \wp_localize_script( $handler, $object_name, $data_array );
    }
  }

  /**
   * Localization array.
   *
   * @return array
   */
  public function get_localizations(): array {
    return $this->localization_constants->get_localizations();
  }
}
