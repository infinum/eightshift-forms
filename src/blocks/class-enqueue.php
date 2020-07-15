<?php
/**
 * Enqueue class used to define all script and style enqueues for Gutenberg blocks.
 *
 * @package Eightshift_Forms\Blocks
 */

namespace Eightshift_Forms\Blocks;

use Eightshift_Blocks\Enqueue as Lib_Enqueue;
use Eightshift_Libs\Assets\Manifest_Data;

/**
 * Enqueue class.
 */
class Enqueue extends Lib_Enqueue {

  /**
   * Instance variable of manifest data.
   *
   * @var object
   */
  protected $manifest;

  /**
   * Create a new admin instance that injects manifest data for use in assets registration.
   *
   * @param Manifest_Data $manifest Inject manifest which holds data about assets from manifest.json.
   */
  public function __construct( Manifest_Data $manifest ) {
    $this->manifest = $manifest;
  }

  /**
   * Method to provide projects manifest array.
   * Using this manifest you are able to provide project specific implementation of assets locations.
   *
   * @return array
   */
  public function get_project_manifest() : array {
    return $this->manifest->get_decoded_manifest_data();
  }

  /**
   * Get project name used in enqueue methods for scripts and styles.
   *
   * @return string
   */
  protected function get_project_name() : string {
    return ES_FORMS_PLUGIN_NAME;
  }

  /**
   * Get project version used in enqueue methods for scripts and styles.
   *
   * @return string
   */
  protected function get_project_version() : string {
    return ES_FORMS_PLUGIN_VERSION;
  }

    /**
   * Get block editor only script key from project manifest.json
   *
   * @return string
   */
  public function get_block_editor_script_key() : string {
    return 'esFromsApplicationBlocksEditor.js';
  }

  /**
   * Get block editor only style key from project manifest.json
   *
   * @return string
   */
  public function get_block_editor_style_key() : string {
    return 'esFromsApplicationBlocksEditor.css';
  }

  /**
   * Get block editor and frontend style key from project manifest.json
   *
   * @return string
   */
  public function get_block_style_key() : string {
    return 'esFromsApplicationBlocks.css';
  }

  /**
   * Get block frontend only script key from project manifest.json
   *
   * @return string
   */
  public function get_block_script_key() : string {
    return 'esFromsApplicationBlocks.js';
  }
}
