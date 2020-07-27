<?php
/**
 * The Theme/Frontend Enqueue specific functionality.
 *
 * @package Eightshift_Forms\Enqueue
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Enqueue;

use Eightshift_Forms\Rest\Base_Route;
use Eightshift_Libs\Enqueue\Enqueue_Theme as Lib_Enqueue_Theme;
use Eightshift_Libs\Manifest\Manifest_Data;

/**
 * Class Enqueue
 */
class Enqueue_Theme extends Lib_Enqueue_Theme {

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
   * Get localizations.
   *
   * @return array
   */
  public function get_localizations(): array {
    return $this->localization_constants->get_localizations();
  }
}
