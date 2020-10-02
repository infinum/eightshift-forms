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
  public function __construct( Manifest_Data $manifest, Base_Route $dynamics_crm_route, Base_Route $buckaroo_route, Base_Route $send_email_route ) {
    $this->manifest           = $manifest;
    $this->dynamics_crm_route = $dynamics_crm_route;
    $this->buckaroo_route     = $buckaroo_route;
    $this->send_email_route   = $send_email_route;
  }

  /**
   * Define all variables we need in both editor and frontend.
   *
   * @return array
   */
  public function get_localizations(): array {
    $localization = [
      self::LOCALIZATION_KEY => [
        'siteUrl'           => get_site_url(),
        'isDynamicsCrmUsed' => has_filter( Filters::DYNAMICS_CRM ),
        'isBuckarooUsed'    => has_filter( Filters::BUCKAROO ),
        'hasThemes'         => has_filter( Filters::GENERAL ),
        'content' => [
          'formLoading' => esc_html__( 'Form is submitting, please wait.', 'eightshift-forms' ),
          'formSuccess' => esc_html__( 'Form successfully submitted.', 'eightshift-forms' ),
        ],
        'sendEmail' => [
          'restUri' => $this->send_email_route->get_route_uri(),
        ],
        'internalServerError' => esc_html__( 'Internal server error', 'eightshift-forms' ),
      ],
    ];

    if ( has_filter( Filters::GENERAL ) ) {
      $localization = $this->add_general_constants( $localization );
    }

    if ( has_filter( Filters::DYNAMICS_CRM ) ) {
      $localization = $this->add_dynamics_crm_constants( $localization );
    }

    if ( has_filter( Filters::BUCKAROO ) ) {
      $localization = $this->add_buckaroo_constants( $localization );
    }

    if ( has_filter( Filters::PREFILL_GENERIC_MULTI ) ) {
      $localization[ self::LOCALIZATION_KEY ]['prefill']['multi'] = $this->add_prefill_generic_multi_constants();
    }

    return $localization;
  }

  /**
   * Localize all constants required for Dynamics CRM integration.
   *
   * @param  array $localization Existing localizations.
   * @return array
   */
  protected function add_general_constants( array $localization ): array {
    $localization[ self::LOCALIZATION_KEY ]['themes'] = apply_filters( Filters::GENERAL, 'themes' );
    return $localization;
  }


  /**
   * Localize all constants required for Dynamics CRM integration.
   *
   * @param  array $localization Existing localizations.
   * @return array
   */
  protected function add_dynamics_crm_constants( array $localization ): array {
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

    return $localization;
  }

  /**
   * Localize all constants required for Buckaroo integration.
   *
   * @param  array $localization Existing localizations.
   * @return array
   */
  protected function add_buckaroo_constants( array $localization ): array {
    $localization[ self::LOCALIZATION_KEY ]['buckaroo'] = [
      'restUri' => $this->buckaroo_route->get_route_uri(),
    ];

    return $localization;
  }


  /**
   * Localize all constants required for Dynamics CRM integration.
   *
   * @return array
   */
  protected function add_prefill_generic_multi_constants(): array {
    $prefill_multi = apply_filters( Filters::PREFILL_GENERIC_MULTI, [] );

    if ( ! is_array( $prefill_multi ) ) {
      return [];
    }

    $prefill_multi_formatted = [];
    foreach ( $prefill_multi as $source_name => $prefill_multi_source ) {
      if ( isset( $prefill_multi_source['data'] ) ) {
        unset( $prefill_multi_source['data'] );
      }

      $prefill_multi_formatted[] = $prefill_multi_source;
    }

    return $prefill_multi_formatted;
  }
}
