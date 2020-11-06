<?php
/**
 * Buckaroo integration class.
 *
 * @package Eightshift_Forms\Integrations
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Integrations\Buckaroo;

use Eightshift_Forms\Hooks\Filters;
use Eightshift_Forms\Exception\Missing_Filter_Info_Exception;
use Eightshift_Forms\Integrations\Buckaroo\Exceptions\Buckaroo_Request_Exception;
use Eightshift_Forms\Integrations\Core\Http_Client;

/**
 * Buckaroo integration class.
 */
class Buckaroo implements Filters {

  const TYPE_IDEAL            = 'ideal';
  const LIVE_URI_DATA_REQUEST = 'checkout.buckaroo.nl/json/DataRequest';
  const TEST_URI_DATA_REQUEST = 'testcheckout.buckaroo.nl/json/DataRequest';
  const LIVE_URI_TRANSACTION  = 'checkout.buckaroo.nl/json/Transaction';
  const TEST_URI_TRANSACTION  = 'testcheckout.buckaroo.nl/json/Transaction';

  /**
   * Currency of the payment
   *
   * @var string
   */
  protected $currency = 'EUR';

  /**
   * Type of payment. Defaults to iDEAL.
   *
   * @var string
   */
  protected $pay_type = 'ideal';

  /**
   * Return URL after payment.
   *
   * @var string
   */
  protected $return_url;

  /**
   * Return URL after payment cancel.
   *
   * @var string
   */
  protected $return_url_cancel;

  /**
   * Return URL after payment error.
   *
   * @var string
   */
  protected $return_url_error;

  /**
   * Return URL after payment reject.
   *
   * @var string
   */
  protected $return_url_reject;

  /**
   * Set if we want to use the test URI instead of the live one.
   *
   * @var boolean
   */
  protected $is_test_uri = false;

  /**
   * Set if we want to the /DataRequest endpoint instead of /Transaction.
   *
   * @var boolean
   */
  protected $is_data_request = false;

  /**
   * Constructs object
   *
   * @param Http_Client $guzzle_client OAuth2 client implementation.
   */
  public function __construct( Http_Client $guzzle_client ) {
    $this->guzzle_client = $guzzle_client;
  }

  /**
   * Creates a payment request.
   *
   * @param  string $debtorreference An ID that identifies the debtor to creditor, which is issued by the creditor. For example: a customer number/ID. Max. 35 characters.
   * @param  string $sequencetype    Indicates type of eMandate: one-off or recurring direct debit. 0 = recurring, 1 = one off.
   * @param  string $purchaseid      An ID that identifies the emandate with a purchase order. This will be shown in the emandate information of the customers' bank account. Max. 35 characters.
   * @param  string $language        The consumer language code in lowercase letters. For example `nl`, not `NL` or `nl-NL`.
   * @param  string $issuer          Issuer (bank) name.
   * @param  string $emandatereason  Description of the emandate.
   * @return bool
   *
   * @throws Buckaroo_Request_Exception When something is wrong with response we get from Buckaroo.
   */
  public function create_emandate( string $debtorreference, string $sequencetype, string $purchaseid, string $language, string $issuer, string $emandatereason, $maxamount = null ) {
    $response             = [];
    $post_array           = $this->build_post_body_for_emandate( $debtorreference, $sequencetype, $purchaseid, $language, $issuer, $emandatereason, $maxamount );
    $authorization_header = $this->generate_authorization_header( $post_array, $this->get_buckaroo_uri() );

    $post_response = $this->guzzle_client->post("https://{$this->get_buckaroo_uri()}", [
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => $authorization_header,
      ],
      'body' => \wp_json_encode( $post_array ),
    ]);

    $post_response_json = json_decode( (string) $post_response->getBody(), true );

    if ( json_last_error() !== JSON_ERROR_NONE ) {
      throw new Buckaroo_Request_Exception( esc_html__( 'Invalid JSON in response body', 'eightshift-forms' ) );
    }

    if ( ! isset( $post_response_json['RequiredAction']['RedirectURL'] ) ) {
      throw new Buckaroo_Request_Exception( esc_html__( 'Missing redirect URL in Buckaroo response', 'eightshift-forms' ), $post_response_json );
    }

    $response['redirectUrl'] = $post_response_json['RequiredAction']['RedirectURL'];

    return $response;
  }

  /**
   * Creates a payment request.
   *
   * @param  int|float|string $donation_amount Donation amount.
   * @param  string           $invoice Invoice name.
   * @param  string           $issuer Issuer (bank) name.
   * @return bool
   *
   * @throws Buckaroo_Request_Exception When something is wrong with JSON we get from Buckaroo.
   */
  public function send_payment( $donation_amount, string $invoice, string $issuer = '' ) {
    $response             = [];
    $post_array           = $this->build_post_body_for_payment( $donation_amount, $invoice, $issuer );
    $authorization_header = $this->generate_authorization_header( $post_array, $this->get_buckaroo_uri() );

    $post_response = $this->guzzle_client->post("https://{$this->get_buckaroo_uri()}", [
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => $authorization_header,
      ],
      'body' => \wp_json_encode( $post_array ),
    ]);

    $post_response_json = json_decode( (string) $post_response->getBody(), true );

    if ( json_last_error() !== JSON_ERROR_NONE ) {
      throw new Buckaroo_Request_Exception( esc_html__( 'Invalid JSON in response body', 'eightshift-forms' ) );
    }

    if ( ! isset( $post_response_json['RequiredAction']['RedirectURL'] ) ) {
      throw new Buckaroo_Request_Exception( esc_html__( 'Missing redirect URL in Buckaroo response', 'eightshift-forms' ), $post_response_json );
    }

    error_log(print_r($post_response_json, true));
    $response['redirectUrl'] = $post_response_json['RequiredAction']['RedirectURL'];

    return $response;
  }

  /**
   * Sets all redirect URLs in 1 function
   *
   * @param string $redirect_url        URL to redirect on success.
   * @param string $redirect_url_cancel URL to redirect on cancel.
   * @param string $redirect_url_error  URL to redirect on error.
   * @param string $redirect_url_reject URL to redirect on reject.
   * @return void
   */
  public function set_redirect_urls( string $redirect_url, string $redirect_url_cancel, string $redirect_url_error, string $redirect_url_reject ) {
    $this->set_return_url( $redirect_url );
    $this->set_return_url_cancel( $redirect_url_cancel );
    $this->set_return_url_error( $redirect_url_error );
    $this->set_return_url_reject( $redirect_url_reject );
  }

  /**
   * Generates the invoice name based on submitted data + salted with time (meaning it should always be unique)-
   *
   * @param  array $params Parameters from request.
   * @return string
   */
  public function generate_debtor_reference( array $params ) {
    $prefix      = 'debtor';
    $data_hash   = hash( 'crc32', wp_json_encode( $params ) );
    $random_hash = hash( 'crc32', uniqid() );
    return "{$prefix}-{$data_hash}-{$random_hash}";
  }

  /**
   * Generates the invoice name based on submitted data + salted with time (meaning it should always be unique)-
   *
   * @param  array $params Parameters from request.
   * @return string
   */
  public function generate_invoice_name( array $params ) {
    $prefix      = 'invoice';
    $data_hash   = hash( 'crc32', wp_json_encode( $params ) );
    $random_hash = hash( 'crc32', uniqid() );
    return "{$prefix}-{$data_hash}-{$random_hash}";
  }

  /**
   * Generates the invoice name based on submitted data + salted with time (meaning it should always be unique)-
   *
   * @param  array $params Parameters from request.
   * @return string
   */
  public function generate_purchase_id( array $params ) {
    $prefix      = 'purchase-id';
    $data_hash   = hash( 'crc32', wp_json_encode( $params ) );
    $random_hash = hash( 'crc32', uniqid() );
    return "{$prefix}-{$data_hash}-{$random_hash}";
  }

  /**
   * Set if you need to use the test URI instead of live one.
   *
   * @return void
   */
  public function set_test(): void {
    $this->is_test_uri = true;
  }

  /**
   * Set if you need to use the test URI instead of live one.
   *
   * @return void
   */
  public function set_data_request(): void {
    $this->is_data_request = true;
  }

  /**
   * Set's currency as uppercase 3-letter string (example: EUR)
   *
   * @param  string $currency Currency string.
   * @return void
   */
  public function set_currency( string $currency ): void {
    $this->currency = $currency;
  }

  /**
   * Getter for $this->currency.
   *
   * @return string
   */
  public function get_currency(): string {
    return $this->currency;
  }

  /**
   * Get type of payment. Defaults to iDEAL.
   *
   * @return  string
   */
  public function get_pay_type() {
     return $this->pay_type;
  }

  /**
   * Set type of payment. Defaults to iDEAL.
   *
   * @param  string $pay_type  Type of payment. Defaults to iDEAL.
   *
   * @return  self
   */
  public function set_pay_type( string $pay_type ) {
    $this->pay_type = $pay_type;
    return $this;
  }

  /**
   * Get return URL after payment.
   *
   * @return  string
   */
  public function get_return_url() {
    return $this->return_url;
  }

  /**
   * Set return URL after payment.
   *
   * @param  string $return_url  Return URL after payment.
   *
   * @return  self
   */
  public function set_return_url( string $return_url ) {
    $this->return_url = $return_url;

    return $this;
  }

  /**
   * Get return URL after payment cancel.
   *
   * @return  string
   */
  public function get_return_url_cancel() {
     return $this->return_url_cancel;
  }

  /**
   * Set return URL after payment cancel.
   *
   * @param  string $return_url_cancel  Return URL after payment cancel.
   * @return  self
   */
  public function set_return_url_cancel( string $return_url_cancel ) {
    $this->return_url_cancel = $return_url_cancel;

    return $this;
  }

  /**
   * Get return URL after payment error.
   *
   * @return  string
   */
  public function get_return_url_error() {
     return $this->return_url_error;
  }

  /**
   * Set return URL after payment error.
   *
   * @param  string $return_url_error  Return URL after payment error.
   *
   * @return  self
   */
  public function set_return_url_error( string $return_url_error ) {
    $this->return_url_error = $return_url_error;

    return $this;
  }

  /**
   * Get return URL after payment reject.
   *
   * @return  string
   */
  public function get_return_url_reject() {
     return $this->return_url_reject;
  }

  /**
   * Set return URL after payment reject.
   *
   * @param  string $return_url_reject  Return URL after payment reject.
   *
   * @return  self
   */
  public function set_return_url_reject( string $return_url_reject ) {
    $this->return_url_reject = $return_url_reject;

    return $this;
  }

  /**
   * Generates the correct authorization header.
   *
   * @param array  $post_array   Array of post data we're sending to Buckaroo.
   * @param string $buckaroo_uri Buckaroo URI we're posting to.
   * @return string
   */
  private function generate_authorization_header( array $post_array, string $buckaroo_uri ): string {
    $this->verify_buckaroo_info_exists();
    $website_key = \apply_filters( self::BUCKAROO, 'website_key' );
    $secret_key  = \apply_filters( self::BUCKAROO, 'secret_key' );
    $post        = \wp_json_encode( $post_array );
    $md5         = md5( $post, true );
    $post        = base64_encode( $md5 ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
    $uri         = strtolower( rawurlencode( $buckaroo_uri ) );
    $nonce       = \wp_rand( 0000000, 9999999 );
    $time        = time();

    $hmac     = $website_key . 'POST' . $uri . $time . $nonce . $post;
    $sha_hash = hash_hmac( 'sha256', $hmac, $secret_key, true );
    $hmac     = base64_encode( $sha_hash ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

    return "hmac {$website_key}:{$hmac}:{$nonce}:{$time}";
  }

  /**
   * Builds the body of request
   *
   * @param  int|float|string $donation_amount Donation amount.
   * @param  string           $invoice Invoice name.
   * @param  string           $issuer Issuer (bank) name.
   * @return array
   */
  private function build_post_body_for_payment( $donation_amount, string $invoice, string $issuer = '' ): array {
    $this->verify_buckaroo_info_exists();

    $post_array = [
      'Currency' => $this->get_currency(),
      'AmountDebit' => $donation_amount,
      'Invoice' => $invoice,
      'ContinueOnIncomplete' => 1,
      'Services' => [
        'ServiceList' => [],
      ],
    ];

    $service_array = [
      'Action' => 'Pay',
      'Name' => $this->get_pay_type(),
      'Parameters' => [],
    ];

    // Add issuing bank if provided as part of request.
    if ( ! empty( $issuer ) ) {
      $service_array['Parameters'][] = [
        'Name' => 'issuer',
        'Value' => $issuer,
      ];
    }

    $post_array['ReturnURL']       = $this->get_return_url();
    $post_array['ReturnURLCancel'] = $this->get_return_url_cancel();
    $post_array['ReturnURLError']  = $this->get_return_url_error();
    $post_array['ReturnURLReject'] = $this->get_return_url_reject();

    $post_array['Services']['ServiceList'][] = $service_array;

    return $post_array;
  }

  /**
   * Builds the body of request
   *
   * @param  string $debtorreference An ID that identifies the debtor to creditor, which is issued by the creditor. For example: a customer number/ID. Max. 35 characters.
   * @param  string $sequencetype    Indicates type of eMandate: one-off or recurring direct debit. 0 = recurring, 1 = one off.
   * @param  string $purchaseid      An ID that identifies the emandate with a purchase order. This will be shown in the emandate information of the customers' bank account. Max. 35 characters.
   * @param  string $language        The consumer language code in lowercase letters. For example `nl`, not `NL` or `nl-NL`.
   * @param  string $issuer          Issuer (bank) name.
   * @param  string $emandatereason  A description of the (purpose) of the emandate. This will be shown in the emandate information of the customers' bank account. Max 70 characters.
   * @param  string $maxamount       This is the maximum amount per SEPA Direct Debit. Debtor can change this value during the authorization process. The (altered) value will be communicated in the push message to the Merchant. This parameter is for B2B only and required if that's the case.
   * @return array
   */
  private function build_post_body_for_emandate( string $debtorreference, string $sequencetype, string $purchaseid, string $language, string $issuer, string $emandatereason, $maxamount ): array {
    $this->verify_buckaroo_info_exists();

    $post_array = [
      'Currency' => $this->get_currency(),
      'ContinueOnIncomplete' => 1,
      'Services' => [
        'ServiceList' => [],
      ],
    ];

    $service_array = [
      'Action' => 'CreateMandate',
      'Name' => $this->get_pay_type(),
      'maxamount' => 15.00,
      'Parameters' => [
        [
          'Name' => 'debtorreference',
          'Value' => $debtorreference,
        ],
        [
          'Name' => 'sequencetype',
          'Value' => $sequencetype,
        ],
        [
          'Name' => 'purchaseid',
          'Value' => $purchaseid,
        ],
        [
          'Name' => 'language',
          'Value' => $language,
        ],
        [
          'Name' => 'emandatereason',
          'Value' => $emandatereason,
        ],
      ],
    ];

    // Add issuing bank if provided as part of request.
    if ( ! empty( $issuer ) ) {
      $service_array['Parameters'][] = [
        'Name' => 'debtorbankid',
        'Value' => $issuer,
      ];
    }

    // Add maxamount if provided as part of request.
    if ( ! empty( $maxamount ) ) {
      // $service_array['Parameters'][] = [
      //   'Name' => 'maxamount',
      //   'Value' => 15.00,
      // ];
    }

    $post_array['ReturnURL']       = $this->get_return_url();
    $post_array['ReturnURLCancel'] = $this->get_return_url_cancel();
    $post_array['ReturnURLError']  = $this->get_return_url_error();
    $post_array['ReturnURLReject'] = $this->get_return_url_reject();

    $post_array['Services']['ServiceList'][] = $service_array;

    error_log(print_r($post_array, true));
    return $post_array;
  }

  /**
   * Make sure we have the data we need defined as filters.
   *
   * @throws \Missing_Filter_Info_Exception When not all required keys are set.
   *
   * @return void
   */
  private function verify_buckaroo_info_exists(): void {
    if ( empty( \apply_filters( self::BUCKAROO, 'website_key' ) ) ) {
      throw Missing_Filter_Info_Exception::view_exception( self::BUCKAROO, 'website_key' );
    }

    if ( empty( \apply_filters( self::BUCKAROO, 'secret_key' ) ) ) {
      throw Missing_Filter_Info_Exception::view_exception( self::BUCKAROO, 'secret_key' );
    }
  }

  /**
   * Get the correct url depending on if we're testing or not.
   *
   * @return string
   */
  private function get_buckaroo_uri(): string {
    return $this->is_test() ? $this->get_buckaroo_uri_test() : $this->get_buckaroo_uri_live();
  }

  /**
   * Returns correct Buckaroo live uri.
   *
   * @return string
   */
  private function get_buckaroo_uri_live(): string {
    return $this->is_data_request() ? self::LIVE_URI_DATA_REQUEST : self::LIVE_URI_TRANSACTION;
  }

  /**
   * Returns correct Buckaroo test uri.
   *
   * @return string
   */
  private function get_buckaroo_uri_test(): string {
    return $this->is_data_request() ? self::TEST_URI_DATA_REQUEST : self::TEST_URI_TRANSACTION;
  }

  /**
   * Check if we're running a test or not.
   *
   * @return boolean
   */
  private function is_test(): bool {
    return $this->is_test_uri;
  }

  /**
   * Check if we're running a test or not.
   *
   * @return boolean
   */
  private function is_data_request(): bool {
    return $this->is_data_request;
  }

}
