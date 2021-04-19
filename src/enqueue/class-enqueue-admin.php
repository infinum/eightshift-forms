<?php
/**
 * The Admin Enqueue specific functionality.
 *
 * @package EightshiftForms\Enqueue
 */

declare( strict_types=1 );

namespace EightshiftForms\Enqueue;

use Eightshift_Libs\Enqueue\Enqueue_Admin as Lib_Enqueue_Admin;
use Eightshift_Libs\Manifest\Manifest_Data;

/**
 * Class Enqueue
 */
class Enqueue_Admin extends Lib_Enqueue_Admin {

  /**
   * Manifest obj.
   *
   * @var Manifest_Data
   */
  protected $manifest;

  /**
   * Object which holds all variables that need to be passed to the editor.
   *
   * @var LocalizationConstants
   */
  private $localization_constants;

  /**
   * Create a new admin instance.
   *
   * @param Manifest_Data          $manifest               Inject manifest which holds data about assets from manifest.json.
   * @param LocalizationConstants $localization_constants Injected object which holds all localizations shared between editor and frontend.
   */
  public function __construct( Manifest_Data $manifest, LocalizationConstants $localization_constants ) {
    $this->manifest               = $manifest;
    $this->localization_constants = $localization_constants;
  }

  /**
   * Get localizations.
   *
   * @return array
   */
  public function getLocalizations(): array {
    return $this->localization_constants->get_admin_localizations();
  }
}
