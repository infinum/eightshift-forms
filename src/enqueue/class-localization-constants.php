<?php
/**
 * Enqueue class used to define all script and style enqueue for Gutenberg blocks.
 *
 * @package Eightshift_Libs\Enqueue
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Enqueue;

use Eightshift_Libs\Manifest\Manifest_Data;
use Eightshift_Forms\Rest\Base_Route;
use Eightshift_Forms\Core\Filters;

/**
 * Handles setting constants we need to add to both editor and frontend.
 */
class Localization_Constants {

  const LOCALIZATION_KEY = 'eightshiftForms';

  /**
   * Create a new admin instance.
   *
   * @param Manifest_Data $manifest           Inject manifest which holds data about assets from manifest.json.
   * @param Base_Route    $dynamics_crm_route Dynamics CRM route object which holds values we need to localize.
   */
  public function __construct( Manifest_Data $manifest, Base_Route $dynamics_crm_route ) {
    $this->manifest           = $manifest;
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
        'isDynamicsCrmUsed' => has_filter( Filters::DYNAMICS_CRM ),
        'content' => [
          'formLoading' => esc_html__( 'Form is submitting, please wait.', 'eightshift-forms' ),
          'formSuccess' => esc_html__( 'Form successfully submitted.', 'eightshift-forms' )
        ]
      ]
    ];

    if ( has_filter( Filters::DYNAMICS_CRM ) ) {
      $entities = apply_filters( Filters::DYNAMICS_CRM, 'available_entities' );
      if ( empty( $entities ) ) {
        $available_entities = [
          sprintf( esc_html__( 'No options found, please set available options in %s filter as available_entities', 'eightshift-forms' ), Filters::DYNAMICS_CRM ),
        ];
      } else {
        $available_entities = $entities;
      }

      $localization[ self::LOCALIZATION_KEY ]['dynamicsCrm'] = [
        'restUri' => $this->dynamics_crm_route->get_route_uri(),
        'availableEntities' => $available_entities,
      ];
    }

    return $localization;
  }
}
