<?php

/**
 * Buckaroo integration class.
 *
 * @package EightshiftForms\Integrations
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Buckaroo;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Exception\MissingFilterInfoException;
use EightshiftForms\Integrations\Buckaroo\Exceptions\BuckarooRequestException;
use EightshiftForms\Integrations\Core\HttpClientInterface;

/**
 * Buckaroo integration class.
 */
class Buckaroo implements Filters
{

	public const TYPE_IDEAL            = 'ideal';
	public const LIVE_URI_DATA_REQUEST = 'checkout.buckaroo.nl/json/DataRequest';
	public const TEST_URI_DATA_REQUEST = 'testcheckout.buckaroo.nl/json/DataRequest';
	public const LIVE_URI_TRANSACTION  = 'checkout.buckaroo.nl/json/Transaction';
	public const TEST_URI_TRANSACTION  = 'testcheckout.buckaroo.nl/json/Transaction';

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
	protected $payType = 'ideal';

  /**
   * Return URL after payment.
   *
   * @var string
   */
	protected $returnUrl;

  /**
   * Return URL after payment cancel.
   *
   * @var string
   */
	protected $returnUrlCancel;

  /**
   * Return URL after payment error.
   *
   * @var string
   */
	protected $returnUrlError;

  /**
   * Return URL after payment reject.
   *
   * @var string
   */
	protected $returnUrlReject;

  /**
   * Set if we want to use the test URI instead of the live one.
   *
   * @var boolean
   */
	protected $isTestUri = false;

  /**
   * Set if we want to the /DataRequest endpoint instead of /Transaction.
   *
   * @var boolean
   */
	protected $isDataRequest = false;

  /**
   * HTTP client implementation obj which uses Guzzle.
   *
   * @var HttpClientInterface.
   */
	private $guzzleClient;

  /**
   * Constructs object
   *
   * @param HttpClientInterface $guzzleClient HTTP client implementation.
   */
	public function __construct(HttpClientInterface $guzzleClient)
	{
		$this->guzzleClient = $guzzleClient;
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
   * @return array
   *
   * @throws BuckarooRequestException When something is wrong with response we get from Buckaroo.
   */
	public function createEmandate(string $debtorreference, string $sequencetype, string $purchaseid, string $language, string $issuer, string $emandatereason): array
	{
		$response             = [];
		$postArray           = $this->buildPostBodyForEmandate($debtorreference, $sequencetype, $purchaseid, $language, $issuer, $emandatereason);
		$authorizationHeader = $this->generateAuthorizationHeader($postArray, $this->getBuckarooUri());

		$postResponse = $this->guzzleClient->post("https://{$this->getBuckarooUri()}", [
			'headers' => [
				'Content-Type' => 'application/json',
				'Authorization' => $authorizationHeader,
			],
			'body' => \wp_json_encode($postArray),
		]);

		$postResponseJson = json_decode((string) $postResponse->getBody(), true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new BuckarooRequestException(esc_html__('Invalid JSON in response body', 'eightshift-forms'));
		}

		if (! isset($postResponseJson['RequiredAction']['RedirectURL'])) {
			throw new BuckarooRequestException(esc_html__('Missing redirect URL in Buckaroo response', 'eightshift-forms'), $postResponseJson);
		}

		$response['redirectUrl'] = $postResponseJson['RequiredAction']['RedirectURL'];

		return $response;
	}

  /**
   * Creates a payment request.
   *
   * @param  int|float|string $donationAmount Donation amount.
   * @param  string           $invoice         Invoice name.
   * @param  string           $issuer          Issuer (bank) name.
   * @param  bool             $isRecurring    Is recurring payment.
   * @param  string           $description     Description of the payment.
   * @return array
   *
   * @throws BuckarooRequestException When something is wrong with JSON we get from Buckaroo.
   */
	public function sendPayment($donationAmount, string $invoice, string $issuer, bool $isRecurring, string $description): array
	{
		$response             = [];
		$postArray           = $this->buildPostBodyForPayment($donationAmount, $invoice, $issuer, $isRecurring, $description);
		$authorizationHeader = $this->generateAuthorizationHeader($postArray, $this->getBuckarooUri());

		$postResponse = $this->guzzleClient->post("https://{$this->getBuckarooUri()}", [
		'headers' => [
		'Content-Type' => 'application/json',
		'Authorization' => $authorizationHeader,
		],
		'body' => \wp_json_encode($postArray),
		]);

		$postResponseJson = json_decode((string) $postResponse->getBody(), true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new BuckarooRequestException(esc_html__('Invalid JSON in response body', 'eightshift-forms'));
		}

		if (! isset($postResponseJson['RequiredAction']['RedirectURL'])) {
			throw new BuckarooRequestException(esc_html__('Missing redirect URL in Buckaroo response', 'eightshift-forms'), $postResponseJson);
		}

		$response['redirectUrl'] = $postResponseJson['RequiredAction']['RedirectURL'];

		return $response;
	}

  /**
   * Sets all redirect URLs in 1 function
   *
   * @param string $redirectUrl        URL to redirect on success.
   * @param string $redirectUrlCancel URL to redirect on cancel.
   * @param string $redirectUrlError  URL to redirect on error.
   * @param string $redirectUrlReject URL to redirect on reject.
   * @return void
   */
	public function setRedirectUrls(string $redirectUrl, string $redirectUrlCancel, string $redirectUrlError, string $redirectUrlReject)
	{
		$this->setReturnUrl($redirectUrl);
		$this->setReturnUrlCancel($redirectUrlCancel);
		$this->setReturnUrlError($redirectUrlError);
		$this->setReturnUrlReject($redirectUrlReject);
	}

  /**
   * Generates the invoice name based on submitted data + salted with time (meaning it should always be unique)-
   *
   * @param  array $params Parameters from request.
   * @return string
   */
	public function generateDebtorReference(array $params)
	{
		$prefix      = 'debtor';
		$dataHash   = hash('crc32', (string) wp_json_encode($params));
		$randomHash = hash('crc32', uniqid());
		return "{$prefix}-{$dataHash}-{$randomHash}";
	}

  /**
   * Generates the invoice name based on submitted data + salted with time (meaning it should always be unique)-
   *
   * @param  array $params Parameters from request.
   * @return string
   */
	public function generateInvoiceName(array $params)
	{
		$prefix      = 'invoice';
		$dataHash   = hash('crc32', (string) wp_json_encode($params));
		$randomHash = hash('crc32', uniqid());
		return "{$prefix}-{$dataHash}-{$randomHash}";
	}

  /**
   * Generates the invoice name based on submitted data + salted with time (meaning it should always be unique)-
   *
   * @param  array $params Parameters from request.
   * @return string
   */
	public function generatePurchaseId(array $params)
	{
		$prefix      = 'purchase-id';
		$dataHash   = hash('crc32', (string) wp_json_encode($params));
		$randomHash = hash('crc32', uniqid());
		return "{$prefix}-{$dataHash}-{$randomHash}";
	}

  /**
   * Set if you need to use the test URI instead of live one.
   *
   * @return void
   */
	public function setTest(): void
	{
		$this->isTestUri = true;
	}

  /**
   * Set if you need to use the test URI instead of live one.
   *
   * @return void
   */
	public function setDataRequest(): void
	{
		$this->isDataRequest = true;
	}

  /**
   * Set's currency as uppercase 3-letter string (example: EUR)
   *
   * @param  string $currency Currency string.
   * @return void
   */
	public function setCurrency(string $currency): void
	{
		$this->currency = $currency;
	}

  /**
   * Getter for $this->currency.
   *
   * @return string
   */
	public function getCurrency(): string
	{
		return $this->currency;
	}

  /**
   * Get type of payment. Defaults to iDEAL.
   *
   * @return  string
   */
	public function getPayType()
	{
		return $this->payType;
	}

  /**
   * Set type of payment. Defaults to iDEAL.
   *
   * @param  string $payType  Type of payment. Defaults to iDEAL.
   *
   * @return  self
   */
	public function setPayType(string $payType)
	{
		$this->payType = $payType;
		return $this;
	}

  /**
   * Get return URL after payment.
   *
   * @return  string
   */
	public function getReturnUrl()
	{
		return $this->returnUrl;
	}

  /**
   * Set return URL after payment.
   *
   * @param  string $returnUrl  Return URL after payment.
   *
   * @return  self
   */
	public function setReturnUrl(string $returnUrl)
	{
		$this->returnUrl = $returnUrl;

		return $this;
	}

  /**
   * Get return URL after payment cancel.
   *
   * @return  string
   */
	public function getReturnUrlCancel()
	{
		return $this->returnUrlCancel;
	}

  /**
   * Set return URL after payment cancel.
   *
   * @param  string $returnUrlCancel  Return URL after payment cancel.
   * @return  self
   */
	public function setReturnUrlCancel(string $returnUrlCancel)
	{
		$this->returnUrlCancel = $returnUrlCancel;

		return $this;
	}

  /**
   * Get return URL after payment error.
   *
   * @return  string
   */
	public function getReturnUrlError()
	{
		return $this->returnUrlError;
	}

  /**
   * Set return URL after payment error.
   *
   * @param  string $returnUrlError  Return URL after payment error.
   *
   * @return  self
   */
	public function setReturnUrlError(string $returnUrlError)
	{
		$this->returnUrlError = $returnUrlError;

		return $this;
	}

  /**
   * Get return URL after payment reject.
   *
   * @return  string
   */
	public function getReturnUrlReject()
	{
		return $this->returnUrlReject;
	}

  /**
   * Set return URL after payment reject.
   *
   * @param  string $returnUrlReject  Return URL after payment reject.
   *
   * @return  self
   */
	public function setReturnUrlReject(string $returnUrlReject)
	{
		$this->returnUrlReject = $returnUrlReject;

		return $this;
	}

  /**
   * Generates the correct authorization header.
   *
   * @param array  $postArray   Array of post data we're sending to Buckaroo.
   * @param string $buckarooUri Buckaroo URI we're posting to.
   * @return string
   */
	private function generateAuthorizationHeader(array $postArray, string $buckarooUri): string
	{
		$this->verifyBuckarooInfoExists();
		$websiteKey = \apply_filters(self::BUCKAROO, 'websiteKey');
		$secretKey  = \apply_filters(self::BUCKAROO, 'secretKey');
		$post        = (string) \wp_json_encode($postArray);
		$md5         = md5($post, true);
		$post        = base64_encode($md5); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$uri         = strtolower(rawurlencode($buckarooUri));
		$nonce       = \wp_rand(0000000, 9999999);
		$time        = time();

		$hmac     = $websiteKey . 'POST' . $uri . $time . $nonce . $post;
		$shaHash = hash_hmac('sha256', $hmac, $secretKey, true);
		$hmac     = base64_encode($shaHash); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

		return "hmac {$websiteKey}:{$hmac}:{$nonce}:{$time}";
	}

  /**
   * Builds the body of request
   *
   * @param  int|float|string $donationAmount Donation amount.
   * @param  string           $invoice         Invoice name.
   * @param  string           $issuer          Issuer (bank) name.
   * @param  bool             $isRecurring    Is recurring payment.
   * @param  string           $description     Description of the payment.
   * @return array
   */
	private function buildPostBodyForPayment($donationAmount, string $invoice, string $issuer, bool $isRecurring, string $description): array
	{
		$this->verifyBuckarooInfoExists();

		$postArray = [
			'Currency' => $this->getCurrency(),
			'AmountDebit' => $donationAmount,
			'Invoice' => $invoice,
			'ContinueOnIncomplete' => 1,
			'Services' => [
				'ServiceList' => [],
			],
			'Description' => $description,
		];

	  // Set payment to recurring if needed.
		if ($isRecurring) {
			$postArray['StartRecurrent'] = 'True';
		}

		$serviceArray = [
			'Action' => 'Pay',
			'Name' => $this->getPayType(),
			'Parameters' => [],
		];

	  // Add issuing bank if provided as part of request.
		if (! empty($issuer)) {
			$serviceArray['Parameters'][] = [
				'Name' => 'issuer',
				'Value' => $issuer,
			];
		}

		$postArray['ReturnURL']       = $this->getReturnUrl();
		$postArray['ReturnURLCancel'] = $this->getReturnUrlCancel();
		$postArray['ReturnURLError']  = $this->getReturnUrlError();
		$postArray['ReturnURLReject'] = $this->getReturnUrlReject();

		$postArray['Services']['ServiceList'][] = $serviceArray;

		return $postArray;
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
   * @return array
   */
	private function buildPostBodyForEmandate(string $debtorreference, string $sequencetype, string $purchaseid, string $language, string $issuer, string $emandatereason): array
	{
		$this->verifyBuckarooInfoExists();

		$postArray = [
			'Currency' => $this->getCurrency(),
			'ContinueOnIncomplete' => 1,
			'Services' => [
				'ServiceList' => [],
			],
		];

		$serviceArray = [
			'Action' => 'CreateMandate',
			'Name' => $this->getPayType(),
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
		if (! empty($issuer)) {
			$serviceArray['Parameters'][] = [
				'Name' => 'debtorbankid',
				'Value' => $issuer,
			];
		}

		$postArray['ReturnURL']       = $this->getReturnUrl();
		$postArray['ReturnURLCancel'] = $this->getReturnUrlCancel();
		$postArray['ReturnURLError']  = $this->getReturnUrlError();
		$postArray['ReturnURLReject'] = $this->getReturnUrlReject();

		$postArray['Services']['ServiceList'][] = $serviceArray;

		return $postArray;
	}

  /**
   * Make sure we have the data we need defined as filters.
   *
   * @throws MissingFilterInfoException When not all required keys are set.
   *
   * @return void
   */
	private function verifyBuckarooInfoExists(): void
	{
		if (empty(\apply_filters(self::BUCKAROO, 'websiteKey'))) {
			throw MissingFilterInfoException::viewException(self::BUCKAROO, 'websiteKey');
		}

		if (empty(\apply_filters(self::BUCKAROO, 'secretKey'))) {
			throw MissingFilterInfoException::viewException(self::BUCKAROO, 'secretKey');
		}
	}

  /**
   * Get the correct url depending on if we're testing or not.
   *
   * @return string
   */
	private function getBuckarooUri(): string
	{
		return $this->isTest() ? $this->getBuckarooUriTest() : $this->getBuckarooUriLive();
	}

  /**
   * Returns correct Buckaroo live uri.
   *
   * @return string
   */
	private function getBuckarooUriLive(): string
	{
		return $this->isDataRequest() ? self::LIVE_URI_DATA_REQUEST : self::LIVE_URI_TRANSACTION;
	}

  /**
   * Returns correct Buckaroo test uri.
   *
   * @return string
   */
	private function getBuckarooUriTest(): string
	{
		return $this->isDataRequest() ? self::TEST_URI_DATA_REQUEST : self::TEST_URI_TRANSACTION;
	}

  /**
   * Check if we're running a test or not.
   *
   * @return boolean
   */
	private function isTest(): bool
	{
		return $this->isTestUri;
	}

  /**
   * Check if we're running a test or not.
   *
   * @return boolean
   */
	private function isDataRequest(): bool
	{
		return $this->isDataRequest;
	}
}
