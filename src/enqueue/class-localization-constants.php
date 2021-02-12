<?php
/**
 * Enqueue class used to define all script and style enqueue for Gutenberg blocks.
 *
 * @package Eightshift_Libs\Enqueue
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Enqueue;

use Eightshift_Forms\Hooks\Filters;
use Eightshift_Forms\Integrations\Mailchimp\Mailchimp;
use Eightshift_Forms\Integrations\Mailerlite\Mailerlite;
use Eightshift_Forms\Rest\Active_Route;

/**
 * Handles setting constants we need to add to both editor and frontend.
 */
class Localization_Constants implements Filters {

  /**
   * Key under which all localizations are held. window.${LOCALIZATION_KEY}
   *
   * @var string
   */
  const LOCALIZATION_KEY = 'eightshiftForms';

  /**
   * Some variable.
   *
   * @var Active_Route
   */
  private $dynamics_crm_route;

  /**
   * Buckaroo iDEAL route obj.
   *
   * @var Active_Route
   */
  private $buckaroo_ideal_route;

  /**
   * Buckaroo Emandate route obj.
   *
   * @var Active_Route
   */
  private $buckaroo_emandate_route;

  /**
   * Buckaroo Pay By Email route obj.
   *
   * @var Active_Route
   */
  private $buckaroo_pay_by_email_route;

  /**
   * Send email route object.
   *
   * @var Active_Route
   */
  private $send_email_route;

  /**
   * Mailchimp route object.
   *
   * @var Active_Route
   */
  private $mailchimp_route;

  /**
   * Mailchimp client implementation.
   *
   * @var Mailchimp
   */
  private $mailchimp;

  /**
   * Mailerlite route object.
   *
   * @var Active_Route
   */
  private $mailerlite_route;

  /**
   * Mailerlite client implementation.
   *
   * @var Mailerlite
   */
  private $mailerlite;

  /**
   * Create a new admin instance.
   *
   * @param Active_Route $dynamics_crm_route          Dynamics CRM route object which holds values we need to localize.
   * @param Active_Route $buckaroo_ideal_route        Buckaroo (Ideal) route object which holds values we need to localize.
   * @param Active_Route $buckaroo_emandate_route     Buckaroo (Emandate) route object which holds values we need to localize.
   * @param Active_Route $buckaroo_pay_by_email_route Buckaroo (Pay By Email) route object which holds values we need to localize.
   * @param Active_Route $send_email_route            Send Email route object which holds values we need to localize.
   * @param Active_Route $mailchimp_route             Mailchimp route object which holds values we need to localize.
   * @param Mailchimp    $mailchimp                   Mailchimp implementation.
   */
  public function __construct(
    Active_Route $dynamics_crm_route,
    Active_Route $buckaroo_ideal_route,
    Active_Route $buckaroo_emandate_route,
    Active_Route $buckaroo_pay_by_email_route,
    Active_Route $send_email_route,
    Active_Route $mailchimp_route,
    Mailchimp $mailchimp,
    Active_Route $mailerlite_route,
    Mailerlite $mailerlite
  ) {
    $this->dynamics_crm_route          = $dynamics_crm_route;
    $this->buckaroo_ideal_route        = $buckaroo_ideal_route;
    $this->buckaroo_emandate_route     = $buckaroo_emandate_route;
    $this->buckaroo_pay_by_email_route = $buckaroo_pay_by_email_route;
    $this->send_email_route            = $send_email_route;
    $this->mailchimp_route             = $mailchimp_route;
    $this->mailchimp                   = $mailchimp;
    $this->mailerlite_route             = $mailerlite_route;
    $this->mailerlite                   = $mailerlite;
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
        'isMailchimpUsed'   => has_filter( Filters::MAILCHIMP ),
        'isMailerliteUsed'  => has_filter( Filters::MAILERLITE ),
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

    if ( has_filter( Filters::MAILCHIMP ) ) {
      $localization = $this->add_mailchimp_constants( $localization );
    }

    if ( has_filter( Filters::MAILERLITE ) ) {
      $localization = $this->add_mailerlite_constants( $localization );
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
  private function add_general_constants( array $localization ): array {
    $localization[ self::LOCALIZATION_KEY ]['themes'] = apply_filters( Filters::GENERAL, 'themes' );
    return $localization;
  }


  /**
   * Localize all constants required for Dynamics CRM integration.
   *
   * @param  array $localization Existing localizations.
   * @return array
   */
  private function add_dynamics_crm_constants( array $localization ): array {
    $entities = apply_filters( Filters::DYNAMICS_CRM, 'available_entities' );
    if ( empty( $entities ) ) {
      $available_entities = [
        sprintf( esc_html__( 'No options found, please set available options in %s filter as available_entities', 'eightshift-forms' ), self::DYNAMICS_CRM ),
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
  private function add_buckaroo_constants( array $localization ): array {
    $localization[ self::LOCALIZATION_KEY ]['buckaroo'] = [
      'restUri' => [
        'ideal' => $this->buckaroo_ideal_route->get_route_uri(),
        'emandate' => $this->buckaroo_emandate_route->get_route_uri(),
        'payByEmail' => $this->buckaroo_pay_by_email_route->get_route_uri(),
      ],
    ];

    return $localization;
  }

  /**
   * Localize all constants required for Mailchimp integration.
   *
   * @param  array $localization Existing localizations.
   * @return array
   */
  private function add_mailchimp_constants( array $localization ): array {
    $localization[ self::LOCALIZATION_KEY ]['mailchimp'] = [
      'restUri' => $this->mailchimp_route->get_route_uri(),
      'audiences' => $this->fetch_mailchimp_audiences(),
    ];

    return $localization;
  }

  /**
   * Localize all constants required for Mailerlite integration.
   *
   * @param  array $localization Existing localizations.
   * @return array
   */
  private function add_mailerlite_constants( array $localization ): array {
    $localization[ self::LOCALIZATION_KEY ]['mailerlite'] = [
      'restUri' => $this->mailerlite_route->get_route_uri(),
    ];

    return $localization;
  }

  /**
   * Reads the list of audiences from Mailchimp. Used in form options to
   * select which audience does this form post to.
   *
   * @return array
   */
  private function fetch_mailchimp_audiences(): array {
    $audiences = [];

    try {
      $response = $this->mailchimp->get_all_lists();
    } catch ( \Exception $e ) {
      return $audiences;
    }

    foreach ( $response->lists as $list_obj ) {
      $audiences[] = [
        'value' => $list_obj->id,
        'label' => $list_obj->name,
      ];
    }

    return $audiences;
  }

  /**
   * Reads the list of groups from Mailerlite. Used in form options to
   * select which group does this form post to.
   *
   * @return array
   */
  private function fetch_mailerlite_groups(): array {
    $groups = [];

    try {
      $response = $this->mailerlite->get_all_groups();
    } catch ( \Exception $e ) {
      return $groups;
    }

    foreach ( $response as $list_obj ) {
      $groups[] = [
        'value' => $list_obj->id,
        'label' => $list_obj->name,
      ];
    }

    return $groups;
  }

  /**
   * Localize all constants required for Dynamics CRM integration.
   *
   * @return array
   */
  private function add_prefill_generic_multi_constants(): array {
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
