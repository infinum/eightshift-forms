<?php
/**
 * Enqueue class used to define all script and style enqueue for Gutenberg blocks.
 *
 * @package Eightshift_Libs\Enqueue
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Enqueue;

use Eightshift_Libs\Manifest\Manifest_Data;
use Eightshift_Forms\Core\Config;
use Eightshift_Forms\Rest\Base_Route;

/**
 * Handles setting constants we need to add to both editor and frontend.
 */
class Localization_Constants {

  const LOCALIZATION_KEY = 'eightshiftForms';

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
   * Define all variables we need in both editor and frontend.
   *
   * @return array
   */
  public function get_localizations(): array {
    $localization = [
      self::LOCALIZATION_KEY => [
        'siteUrl' => get_site_url(),
        'isDynamicsCrmUsed' => $this->is_dynamics_crm_used(),
      ]
    ];

    if ( $this->is_dynamics_crm_used() ) {
      if (! defined( 'DYNAMICS_CRM_AVAILABLE_ENTITIES') || ! is_array( DYNAMICS_CRM_AVAILABLE_ENTITIES )) {
        $available_entities = [
          esc_html__('No options found, please set available options in DYNAMICS_CRM_AVAILABLE_ENTITIES constant as array', 'eightshift-forms' ),
        ];
      } else {
        $available_entities = DYNAMICS_CRM_AVAILABLE_ENTITIES;
      }

      $localization[ self::LOCALIZATION_KEY ]['dynamicsCrm'] = [
        'restUri' => $this->dynamics_crm_route->get_route_uri(),
        'availableEntities' => $available_entities,
      ];
    }

    return $localization;
  }

  protected function is_dynamics_crm_used() {
    return defined( 'DYNAMICS_CRM_USED' ) && DYNAMICS_CRM_USED;
  }
}
