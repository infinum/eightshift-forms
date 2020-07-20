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
      ]
    ];

    if ( $this->is_form_type_used( Config::DYNAMICS_CRM_METHOD )) {
      $localization[ self::LOCALIZATION_KEY ]['dynamicsCrm'] = [
        'restUri' => $this->dynamics_crm_route->get_route_uri(),
        'availableEntities' => DYNAMICS_CRM_AVAILABLE_ENTITIES
      ];
    }

    return $localization;
  }

  /**
   * Detects if a specific form type should be used in a project, defined by the EIGHTSHIFT_FORMS_USED_METHODS const
   * (preferably in wp-config.php)
   *
   * @param  string $form_type Form type name.
   * @return boolean
   */
  protected function is_form_type_used( string $form_type ) {
    return defined( 'EIGHTSHIFT_FORMS_USED_METHODS' ) && isset( array_flip( EIGHTSHIFT_FORMS_USED_METHODS )[ $form_type ] );
  }
}
