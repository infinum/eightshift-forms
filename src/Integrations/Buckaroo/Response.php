<?php

/**
 * Object representing a response from Buckaroo.
 *
 * @package EightshiftForms\Integrations\Buckaroo
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Buckaroo;

use EightshiftForms\Integrations\Buckaroo\Exceptions\InvalidBuckarooResponseException;

/**
 * Object representing a response from Buckaroo.
 */
class Response
{

	public const SERVICE_IDEAL        = 'ideal';
	public const SERVICE_EMANDATE     = 'emandate';
	public const SERVICE_PAY_BY_EMAIL = 'pay-by-email';
	public const SERVICE_INVALID      = 'unsupported-buckaroo-service';

	public const STATUS_CODE_SUCCESS   = 190;
	public const STATUS_CODE_ERROR     = 490;
	public const STATUS_CODE_REJECT    = 690;
	public const STATUS_CODE_CANCELLED = 890;
	public const STATUS_CODE_PENDING   = 791;
	public const STATUS_CODE_INVALID   = -1;

	public const TEST_PARAM                  = 'BRQ_TEST';
	public const PRIMARY_SERVICE_PARAM       = 'BRQ_PRIMARY_SERVICE'; // Emandate returns service as this param.
	public const PRIMARY_PAYMENT_METHOD      = 'BRQ_PAYMENT_METHOD'; // iDEAL returns service as this param.
	public const STATUS_CODE_PARAM           = 'BRQ_STATUSCODE';
	public const EMANDATE_ID_PARAM           = 'BRQ_SERVICE_EMANDATE_MANDATEID';
	public const EMANDATE_REFERENCE_ID_PARAM = 'BRQ_SERVICE_EMANDATE_REFERENCE';
	public const EMANDATE_BANK_ID_PARAM      = 'BRQ_SERVICE_EMANDATE_BANKID';
	public const EMANDATE_IBAN_PARAM         = 'BRQ_SERVICE_EMANDATE_IBAN';
	public const IDEAL_BANK_NAME_PARAM       = 'BRQ_SERVICE_IDEAL_CONSUMERISSUER';
	public const IDEAL_BANK_ID_PARAM         = 'BRQ_SERVICE_IDEAL_CONSUMERBIC';
	public const IDEAL_PAYMENT_AMOUNT_PARAM  = 'BRQ_AMOUNT';
	public const IDEAL_PAYMENT_ID_PARAM      = 'BRQ_PAYMENT';
	public const IDEAL_INVOICE_NUMBER_PARAM  = 'BRQ_INVOICENUMBER';
	public const IDEAL_IBAN_PARAM            = 'BRQ_SERVICE_IDEAL_CONSUMERIBAN';
	public const MOCK_PAY_BY_EMAIL_PARAM     = 'BRQ_MOCK_SERVICE';

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
	private $statusAsText = '';

  /**
   * Payer's bank ID.
   *
   * @var string
   */
	private $bankId = '';

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
	private $idealBankName = '';

  /**
   * Payment amount when using iDEAL.
   *
   * @var string
   */
	private $idealPaymentAmount = '';

  /**
   * Payment ID when using iDEAL.
   *
   * @var string
   */
	private $idealPaymentId = '';

  /**
   * Invoice number when using iDEAL.
   *
   * @var string
   */
	private $idealInvoiceNumber = '';

  /**
   * Emandate ID.
   *
   * @var string
   */
	private $emandateId = '';

  /**
   * Emandate reference ID.
   *
   * @var string
   */
	private $emandateReferenceId = '';

  /**
   * Check if this is a test response or not.
   *
   * @var bool
   */
	private $test;

  /**
   * Construct object.
   *
   * @param array $buckarooParams Array of Buckaroo response params.
   *
   * @throws \Exception If unable to public construct response.
   */
	public function __construct(array $buckarooParams)
	{
		if (empty($buckarooParams)) {
			throw new \Exception('Unable to public construct Buckaroo Response, empty array of params given');
		}


		$status       = $buckarooParams[self::STATUS_CODE_PARAM] ?? 'invalid';
		$this->status = is_numeric($status) ? intval($status) : self::STATUS_CODE_INVALID;

		$this->statusAsText = $this->buildStatusAsText($this->status);

	  // Detect if we have any of the known response.
		$this->service = $this->detectService($buckarooParams);

		if ($this->isIdeal()) {
			$this->bankId             = $buckarooParams[self::IDEAL_BANK_ID_PARAM] ?? '';
			$this->idealBankName      = $buckarooParams[self::IDEAL_BANK_NAME_PARAM] ?? '';
			$this->idealPaymentAmount = $buckarooParams[self::IDEAL_PAYMENT_AMOUNT_PARAM] ?? '';
			$this->idealPaymentId     = $buckarooParams[self::IDEAL_PAYMENT_ID_PARAM] ?? '';
			$this->idealInvoiceNumber = $buckarooParams[self::IDEAL_INVOICE_NUMBER_PARAM] ?? '';
			$this->iban               = $buckarooParams[self::IDEAL_IBAN_PARAM] ?? '';
		} elseif ($this->isEmandate()) {
			$this->bankId              = $buckarooParams[self::EMANDATE_BANK_ID_PARAM] ?? '';
			$this->emandateId          = $buckarooParams[self::EMANDATE_ID_PARAM] ?? '';
			$this->emandateReferenceId = $buckarooParams[self::EMANDATE_REFERENCE_ID_PARAM] ?? '';
			$this->iban                = $buckarooParams[self::EMANDATE_IBAN_PARAM] ?? '';
		}

		$this->test = isset($buckarooParams[self::TEST_PARAM]) ? filter_var($buckarooParams[self::TEST_PARAM], FILTER_VALIDATE_BOOL) : false;

		$this->validateResponse();
	}

  /**
   * Check if response is an iDEAL response.
   *
   * @return boolean
   */
	public function isIdeal(): bool
	{
		return $this->service === self::SERVICE_IDEAL;
	}

  /**
   * Check if response is an iDEAL response.
   *
   * @return boolean
   */
	public function isEmandate(): bool
	{
		return $this->service === self::SERVICE_EMANDATE;
	}

  /**
   * Check if response is an Pay By Email (Mocked) response.
   *
   * @return boolean
   */
	public function isPayByEmail(): bool
	{
		return $this->service === self::SERVICE_PAY_BY_EMAIL;
	}

  /**
   * Check if response is an success.
   *
   * @return boolean
   */
	public function isSuccess(): bool
	{
		return $this->status === self::STATUS_CODE_SUCCESS;
	}

  /**
   * Check if response is an cancel.
   *
   * @return boolean
   */
	public function isCancel(): bool
	{
		return $this->status === self::STATUS_CODE_CANCELLED;
	}

  /**
   * Get payer's IBAN.
   *
   * @return  string
   */
	public function getIban()
	{
		return $this->iban;
	}

  /**
   * Get emandate reference ID.
   *
   * @return  string
   */
	public function getEmandateReferenceId(): string
	{
		return $this->emandateReferenceId;
	}

  /**
   * Get emandate ID.
   *
   * @return  string
   */
	public function getEmandateId(): string
	{
		return $this->emandateId;
	}

  /**
   * Get invoice number when using iDEAL.
   *
   * @return  string
   */
	public function getIdealInvoiceNumber(): string
	{
		return $this->idealInvoiceNumber;
	}

  /**
   * Get payment ID when using iDEAL.
   *
   * @return  string
   */
	public function getIdealPaymentId(): string
	{
		return $this->idealPaymentId;
	}

  /**
   * Get payment amount when using iDEAL.
   *
   * @return  string
   */
	public function getIdealPaymentAmount()
	{
		return $this->idealPaymentAmount;
	}

  /**
   * Get payer's bank ID.
   *
   * @return  string
   */
	public function getBankId(): string
	{
		return $this->bankId;
	}

  /**
   * Get status code of response.
   *
   * @return  int
   */
	public function getStatus(): int
	{
		return $this->status;
	}

  /**
   * Get service type of response.
   *
   * @return  string
   */
	public function getService(): string
	{
		return $this->service;
	}

  /**
   * Get check if this is a test response or not.
   *
   * @return  bool
   */
	public function getTest(): bool
	{
		return $this->test;
	}

  /**
   * Get payer's bank name.
   *
   * @return  string
   */
	public function getIdealBankName(): string
	{
		return $this->idealBankName;
	}

  /**
   * Get status code as readable text.
   *
   * @return  string
   */
	public function getStatusAsText(): string
	{
		return $this->statusAsText;
	}

  /**
   * Detects which service this response belongs to.
   *
   * @param  int $statusCode Status code of response.
   * @return string
   */
	private function buildStatusAsText(int $statusCode): string
	{
		switch ($statusCode) {
			case self::STATUS_CODE_SUCCESS:
				$this->statusAsText = esc_html__('Success', 'eightshift-forms');
				break;
			case self::STATUS_CODE_ERROR:
				$this->statusAsText = esc_html__('Error', 'eightshift-forms');
				break;
			case self::STATUS_CODE_CANCELLED:
				$this->statusAsText = esc_html__('Payment Cancelled', 'eightshift-forms');
				break;
			case self::STATUS_CODE_REJECT:
				$this->statusAsText = esc_html__('Payment Rejected', 'eightshift-forms');
				break;
			case self::STATUS_CODE_PENDING:
				$this->statusAsText = esc_html__('Payment Pending', 'eightshift-forms');
				break;
			case self::STATUS_CODE_INVALID:
				$this->statusAsText = esc_html__('Invalid status', 'eightshift-forms');
				break;
			default:
				$this->statusAsText = esc_html__('Unknown', 'eightshift-forms');
		}

		return $this->statusAsText;
	}

  /**
   * Detects which service this response belongs to.
   *
   * @param array $buckarooParams Array of Buckaroo params.
   * @return string
   */
	private function detectService(array $buckarooParams): string
	{
		if (isset($buckarooParams[self::PRIMARY_PAYMENT_METHOD]) && $buckarooParams[self::PRIMARY_PAYMENT_METHOD] === self::SERVICE_IDEAL) {
			return self::SERVICE_IDEAL;
		}

		if (isset($buckarooParams[self::PRIMARY_SERVICE_PARAM]) && $buckarooParams[self::PRIMARY_SERVICE_PARAM] === self::SERVICE_EMANDATE) {
			return self::SERVICE_EMANDATE;
		}

		if (isset($buckarooParams[self::MOCK_PAY_BY_EMAIL_PARAM]) && $buckarooParams[self::MOCK_PAY_BY_EMAIL_PARAM] === self::SERVICE_PAY_BY_EMAIL) {
			return self::SERVICE_PAY_BY_EMAIL;
		}

		return self::SERVICE_INVALID;
	}

  /**
   * Validates that we've successfully built the response.
   *
   * @return bool
   *
   * @throws InvalidBuckarooResponseException When we're unable to validate response.
   */
	private function validateResponse(): bool
	{
		if (! $this->isCancel() && $this->service === self::SERVICE_INVALID) {
			throw new InvalidBuckarooResponseException(esc_html__('Unable to build Buckaroo response, invalid service.', 'eightshift-forms'));
		}

		if ($this->status === self::STATUS_CODE_INVALID) {
			throw new InvalidBuckarooResponseException(esc_html__('Unable to build Buckaroo response, invalid status code.', 'eightshift-forms'));
		}

		if (! $this->isPayByEmail() && $this->isSuccess() && empty($this->bankId)) {
			throw new InvalidBuckarooResponseException(esc_html__('Unable to build Buckaroo response, unable to locate bank ID.', 'eightshift-forms'));
		}

		if ($this->isIdeal() && empty($this->idealBankName)) {
			throw new InvalidBuckarooResponseException(esc_html__('Unable to build Buckaroo response, unable to locate bank.', 'eightshift-forms'));
		}

		if ($this->isIdeal() && empty($this->idealPaymentAmount)) {
			throw new InvalidBuckarooResponseException(esc_html__('Unable to build Buckaroo response, unable to locate payment amount.', 'eightshift-forms'));
		}

		if (! $this->isCancel() && $this->isIdeal() && empty($this->idealPaymentId)) {
			throw new InvalidBuckarooResponseException(esc_html__('Unable to build Buckaroo response, unable to locate payment ID.', 'eightshift-forms'));
		}

		if ($this->isIdeal() && empty($this->idealInvoiceNumber)) {
			throw new InvalidBuckarooResponseException(esc_html__('Unable to build Buckaroo response, unable to locate invoice number.', 'eightshift-forms'));
		}

		if ($this->isEmandate() && empty($this->emandateId)) {
			throw new InvalidBuckarooResponseException(esc_html__('Unable to build Buckaroo response, unable to locate emandate ID.', 'eightshift-forms'));
		}

		if ($this->isEmandate() && $this->isSuccess() && empty($this->emandateReferenceId)) {
			throw new InvalidBuckarooResponseException(esc_html__('Unable to build Buckaroo response, unable to locate emandate reference ID.', 'eightshift-forms'));
		}

		return true;
	}
}
