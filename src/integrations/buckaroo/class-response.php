<?php
/**
 * Object representing a response from Buckaroo.
 *
 * @package Eightshift_Forms\Buckaroo
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Buckaroo;

/**
 * Object representing a response from Buckaroo.
 */
class Response {

  const SERVICE_IDEAL        = 'ideal';
  const SERVICE_EMANDATE     = 'emandate';
  const SERVICE_PAY_BY_EMAIL = 'pay-by-email';
  const SERVICE_INVALID      = 'unsupported-buckaroo-service';

  const STATUS_CODE_SUCCESS   = 190;
  const STATUS_CODE_ERROR     = 490;
  const STATUS_CODE_REJECT    = 690;
  const STATUS_CODE_CANCELLED = 890;
  const STATUS_CODE_PENDING   = 791;
  const STATUS_CODE_INVALID   = -1;

  const TEST_PARAM                  = 'BRQ_TEST';
  const PRIMARY_SERVICE_PARAM       = 'BRQ_PRIMARY_SERVICE'; // Emandate returns service as this param.
  const PRIMARY_PAYMENT_METHOD      = 'BRQ_PAYMENT_METHOD'; // iDEAL returns service as this param.
  const STATUS_CODE_PARAM           = 'BRQ_STATUSCODE';
  const EMANDATE_ID_PARAM           = 'BRQ_SERVICE_EMANDATE_MANDATEID';
  const EMANDATE_REFERENCE_ID_PARAM = 'BRQ_SERVICE_EMANDATE_REFERENCE';
  const EMANDATE_BANK_ID_PARAM      = 'BRQ_SERVICE_EMANDATE_BANKID';
  const EMANDATE_IBAN_PARAM         = 'BRQ_SERVICE_EMANDATE_IBAN';
  const IDEAL_BANK_NAME_PARAM       = 'BRQ_SERVICE_IDEAL_CONSUMERISSUER';
  const IDEAL_BANK_ID_PARAM         = 'BRQ_SERVICE_IDEAL_CONSUMERBIC';
  const IDEAL_PAYMENT_AMOUNT_PARAM  = 'BRQ_AMOUNT';
  const IDEAL_PAYMENT_ID_PARAM      = 'BRQ_PAYMENT';
  const IDEAL_INVOICE_NUMBER_PARAM  = 'BRQ_INVOICENUMBER';
  const IDEAL_IBAN_PARAM            = 'BRQ_SERVICE_IDEAL_CONSUMERIBAN';
  const MOCK_PAY_BY_EMAIL_PARAM     = 'BRQ_MOCK_SERVICE';

  /**
   * Service type of response.
   *
   * @var string
   */
  private $service = '';

  /**
   * Status code of response.
   *
   * @var int
   */
  private $status;

  /**
   * Status code as readable text.
   *
   * @var string
   */
  private $status_as_text = '';

  /**
   * Payer's bank ID.
   *
   * @var string
   */
  private $bank_id = '';

  /**
   * Payer's IBAN.
   *
   * @var string
   */
  private $iban = '';

  /**
   * Payer's bank name.
   *
   * @var string
   */
  private $ideal_bank_name = '';

  /**
   * Payment amount when using iDEAL.
   *
   * @var string
   */
  private $ideal_payment_amount = '';

  /**
   * Payment ID when using iDEAL.
   *
   * @var string
   */
  private $ideal_payment_id = '';

  /**
   * Invoice number when using iDEAL.
   *
   * @var string
   */
  private $ideal_invoice_number = '';

  /**
   * Emandate ID.
   *
   * @var string
   */
  private $emandate_id = '';

  /**
   * Emandate reference ID.
   *
   * @var string
   */
  private $emandate_reference_id = '';

  /**
   * Check if this is a test response or not.
   *
   * @var bool
   */
  private $test;

  /**
   * Construct object.
   *
   * @param array $buckaroo_params Array of Buckaroo response params.
   *
   * @throws \Exception If unable to construct response.
   */
  public function __construct( array $buckaroo_params ) {
    if ( empty( $buckaroo_params ) ) {
      throw new \Exception( 'Unable to construct Buckaroo Response, empty array of params given' );
    }

    $status       = $buckaroo_params[ self::STATUS_CODE_PARAM ] ?? 'invalid';
    $this->status = is_numeric( $status ) ? intval( $status ) : self::STATUS_CODE_INVALID;

    $this->status_as_text = $this->build_status_as_text( $this->status );

    // Detect if we have any of the known response.
    $this->service = $this->detect_service( $buckaroo_params );

    if ( $this->is_ideal() ) {
      $this->bank_id              = $buckaroo_params[ self::IDEAL_BANK_ID_PARAM ] ?? '';
      $this->ideal_bank_name      = $buckaroo_params[ self::IDEAL_BANK_NAME_PARAM ] ?? '';
      $this->ideal_payment_amount = $buckaroo_params[ self::IDEAL_PAYMENT_AMOUNT_PARAM ] ?? '';
      $this->ideal_payment_id     = $buckaroo_params[ self::IDEAL_PAYMENT_ID_PARAM ] ?? '';
      $this->ideal_invoice_number = $buckaroo_params[ self::IDEAL_INVOICE_NUMBER_PARAM ] ?? '';
      $this->iban                 = $buckaroo_params[ self::IDEAL_IBAN_PARAM ] ?? '';
    } elseif ( $this->is_emandate() ) {
      $this->bank_id               = $buckaroo_params[ self::EMANDATE_BANK_ID_PARAM ] ?? '';
      $this->emandate_id           = $buckaroo_params[ self::EMANDATE_ID_PARAM ] ?? '';
      $this->emandate_reference_id = $buckaroo_params[ self::EMANDATE_REFERENCE_ID_PARAM ] ?? '';
      $this->iban                  = $buckaroo_params[ self::EMANDATE_IBAN_PARAM ] ?? '';
    }

    $this->test = isset( $buckaroo_params[ self::TEST_PARAM ] ) ? filter_var( $buckaroo_params[ self::TEST_PARAM ], FILTER_VALIDATE_BOOL ) : false;

    $this->validate_response();
  }

  /**
   * Check if response is an iDEAL response.
   *
   * @return boolean
   */
  public function is_ideal(): bool {
    return $this->service === self::SERVICE_IDEAL;
  }

  /**
   * Check if response is an iDEAL response.
   *
   * @return boolean
   */
  public function is_emandate(): bool {
    return $this->service === self::SERVICE_EMANDATE;
  }

  /**
   * Check if response is an Pay By Email (Mocked) response.
   *
   * @return boolean
   */
  public function is_pay_by_email(): bool {
    return $this->service === self::SERVICE_PAY_BY_EMAIL;
  }

  /**
   * Check if response is an success.
   *
   * @return boolean
   */
  public function is_success(): bool {
    return $this->status === self::STATUS_CODE_SUCCESS;
  }

  /**
   * Check if response is an cancel.
   *
   * @return boolean
   */
  public function is_cancel(): bool {
    return $this->status === self::STATUS_CODE_CANCELLED;
  }

  /**
   * Get payer's IBAN.
   *
   * @return  string
   */
  public function get_iban() {
    return $this->iban;
  }

  /**
   * Get emandate reference ID.
   *
   * @return  string
   */
  public function get_emandate_reference_id(): string {
     return $this->emandate_reference_id;
  }

  /**
   * Get emandate ID.
   *
   * @return  string
   */
  public function get_emandate_id(): string {
     return $this->emandate_id;
  }

  /**
   * Get invoice number when using iDEAL.
   *
   * @return  string
   */
  public function get_ideal_invoice_number(): string {
     return $this->ideal_invoice_number;
  }

  /**
   * Get payment ID when using iDEAL.
   *
   * @return  string
   */
  public function get_ideal_payment_id(): string {
     return $this->ideal_payment_id;
  }

  /**
   * Get payment amount when using iDEAL.
   *
   * @return  string
   */
  public function get_ideal_payment_amount() {
     return $this->ideal_payment_amount;
  }

  /**
   * Get payer's bank ID.
   *
   * @return  string
   */
  public function get_bank_id(): string {
     return $this->bank_id;
  }

  /**
   * Get status code of response.
   *
   * @return  int
   */
  public function get_status(): int {
     return $this->status;
  }

  /**
   * Get service type of response.
   *
   * @return  string
   */
  public function get_service(): string {
     return $this->service;
  }

  /**
   * Get check if this is a test response or not.
   *
   * @return  bool
   */
  public function get_test(): bool {
    return $this->test;
  }

  /**
   * Get payer's bank name.
   *
   * @return  string
   */
  public function get_ideal_bank_name(): string {
    return $this->ideal_bank_name;
  }

  /**
   * Get status code as readable text.
   *
   * @return  string
   */
  public function get_status_as_text(): string {
    return $this->status_as_text;
  }

  /**
   * Detects which service this response belongs to.
   *
   * @param  int $status_code Status code of response.
   * @return string
   */
  private function build_status_as_text( int $status_code ): string {
    switch ( $status_code ) {
      case self::STATUS_CODE_SUCCESS:
        $this->status_as_text = esc_html__( 'Success', 'eightshift-forms' );
            break;
      case self::STATUS_CODE_ERROR:
        $this->status_as_text = esc_html__( 'Error', 'eightshift-forms' );
            break;
      case self::STATUS_CODE_CANCELLED:
        $this->status_as_text = esc_html__( 'Payment Cancelled', 'eightshift-forms' );
            break;
      case self::STATUS_CODE_REJECT:
        $this->status_as_text = esc_html__( 'Payment Rejected', 'eightshift-forms' );
            break;
      case self::STATUS_CODE_PENDING:
        $this->status_as_text = esc_html__( 'Payment Pending', 'eightshift-forms' );
            break;
      case self::STATUS_CODE_INVALID:
        $this->status_as_text = esc_html__( 'Invalid status', 'eightshift-forms' );
            break;
      default:
        $this->status_as_text = esc_html__( 'Unknown', 'eightshift-forms' );
    }

    return $this->status_as_text;
  }

  /**
   * Detects which service this response belongs to.
   *
   * @param array $buckaroo_params Array of Buckaroo params.
   * @return string
   */
  private function detect_service( array $buckaroo_params ): string {
    if ( isset( $buckaroo_params[ self::PRIMARY_PAYMENT_METHOD ] ) && $buckaroo_params[ self::PRIMARY_PAYMENT_METHOD ] === self::SERVICE_IDEAL ) {
      return self::SERVICE_IDEAL;
    }

    if ( isset( $buckaroo_params[ self::PRIMARY_SERVICE_PARAM ] ) && $buckaroo_params[ self::PRIMARY_SERVICE_PARAM ] === self::SERVICE_EMANDATE ) {
      return self::SERVICE_EMANDATE;
    }

    if ( isset( $buckaroo_params[ self::MOCK_PAY_BY_EMAIL_PARAM ] ) && $buckaroo_params[ self::MOCK_PAY_BY_EMAIL_PARAM ] === self::SERVICE_PAY_BY_EMAIL ) {
      return self::SERVICE_PAY_BY_EMAIL;
    }

    return self::SERVICE_INVALID;
  }

  /**
   * Validates that we've successfully built the response.
   *
   * @return bool
   *
   * @throws Invalid_Buckaroo_Response_Exception When we're unable to validate response.
   */
  private function validate_response(): bool {
    if ( ! $this->is_cancel() && $this->service === self::SERVICE_INVALID ) {
      throw new Invalid_Buckaroo_Response_Exception( esc_html__( 'Unable to build Buckaroo response, invalid service.', 'eightshift-forms' ) );
    }

    if ( $this->status === self::STATUS_CODE_INVALID ) {
      throw new Invalid_Buckaroo_Response_Exception( esc_html__( 'Unable to build Buckaroo response, invalid status code.', 'eightshift-forms' ) );
    }

    if ( ! $this->is_pay_by_email() && $this->is_success() && empty( $this->bank_id ) ) {
      throw new Invalid_Buckaroo_Response_Exception( esc_html__( 'Unable to build Buckaroo response, unable to locate bank ID.', 'eightshift-forms' ) );
    }

    if ( $this->is_ideal() && empty( $this->ideal_bank_name ) ) {
      throw new Invalid_Buckaroo_Response_Exception( esc_html__( 'Unable to build Buckaroo response, unable to locate bank.', 'eightshift-forms' ) );
    }

    if ( $this->is_ideal() && empty( $this->ideal_payment_amount ) ) {
      throw new Invalid_Buckaroo_Response_Exception( esc_html__( 'Unable to build Buckaroo response, unable to locate payment amount.', 'eightshift-forms' ) );
    }

    if ( ! $this->is_cancel() && $this->is_ideal() && empty( $this->ideal_payment_id ) ) {
      throw new Invalid_Buckaroo_Response_Exception( esc_html__( 'Unable to build Buckaroo response, unable to locate payment ID.', 'eightshift-forms' ) );
    }

    if ( $this->is_ideal() && empty( $this->ideal_invoice_number ) ) {
      throw new Invalid_Buckaroo_Response_Exception( esc_html__( 'Unable to build Buckaroo response, unable to locate invoice number.', 'eightshift-forms' ) );
    }

    if ( $this->is_emandate() && empty( $this->emandate_id ) ) {
      throw new Invalid_Buckaroo_Response_Exception( esc_html__( 'Unable to build Buckaroo response, unable to locate emandate ID.', 'eightshift-forms' ) );
    }

    if ( $this->is_emandate() && $this->is_success() && empty( $this->emandate_reference_id ) ) {
      throw new Invalid_Buckaroo_Response_Exception( esc_html__( 'Unable to build Buckaroo response, unable to locate emandate reference ID.', 'eightshift-forms' ) );
    }

    return true;
  }
}
