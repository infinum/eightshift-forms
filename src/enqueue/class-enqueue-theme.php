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
   * Register all the hooks
   *
   * @return void
   */
  public function register() {
    parent::register();
  }

  /**
   * Create a new admin instance.
   *
   * @param Manifest_Data $manifest Inject manifest which holds data about assets from manifest.json.
   */
  public function __construct( Manifest_Data $manifest, Base_Route $dynamics_crm_route ) {
    $this->manifest = $manifest;
    $this->dynamics_crm_route = $dynamics_crm_route;
  }

  /**
   * Get localizations
   *
   * @return array
   */
  public function get_localizations(): array {
    return [
      'eightshiftForms' => [
        'siteUrl' => get_site_url(),
        'dynamicsCrmRestUri' => $this->dynamics_crm_route->get_route_uri(),
      ]
    ];
  }
}
