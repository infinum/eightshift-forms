<?php

/**
 * Buckaroo integration class.
 *
 * @package EightshiftForms\Integrations
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Buckaroo;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Exception\Missing_Filter_Info_Exception;
use EightshiftForms\Integrations\Buckaroo\Exceptions\Buckaroo_Request_Exception;
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
   * @throws Buckaroo_Request_Exception When something is wrong with response we get from Buckaroo.
   */
	public function create_emandate(string $debtorreference, string $sequencetype, string $purchaseid, string $language, string $issuer, string $emandatereason): array
	{
		$response             = [];
		$post_array           = $this->build_post_body_for_emandate($debtorreference, $sequencetype, $purchaseid, $language, $issuer, $emandatereason);
		$authorization_header = $this->generate_authorization_header($post_array, $this->get_buckaroo_uri());

		$post_response = $this->guzzleClient->post("https://{$this->get_buckaroo_uri()}", [
		'headers' => [
		'Content-Type' => 'application/json',
		'Authorization' => $authorization_header,
		],
		'body' => \wp_json_encode($post_array),
		]);

		$post_response_json = json_decode((string) $post_response->getBody(), true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new Buckaroo_Request_Exception(esc_html__('Invalid JSON in response body', 'eightshift-forms'));
		}

		if (! isset($post_response_json['RequiredAction']['RedirectURL'])) {
			throw new Buckaroo_Request_Exception(esc_html__('Missing redirect URL in Buckaroo response', 'eightshift-forms'), $post_response_json);
		}

		$response['redirectUrl'] = $post_response_json['RequiredAction']['RedirectURL'];

		return $response;
	}

  /**
   * Creates a payment request.
   *
   * @param  int|float|string $donation_amount Donation amount.
   * @param  string           $invoice         Invoice name.
   * @param  string           $issuer          Issuer (bank) name.
   * @param  bool             $is_recurring    Is recurring payment.
   * @param  string           $description     Description of the payment.
   * @return array
   *
   * @throws Buckaroo_Request_Exception When something is wrong with JSON we get from Buckaroo.
   */
	public function send_payment($donation_amount, string $invoice, string $issuer, bool $is_recurring, string $description): array
	{
		$response             = [];
		$post_array           = $this->build_post_body_for_payment($donation_amount, $invoice, $issuer, $is_recurring, $description);
		$authorization_header = $this->generate_authorization_header($post_array, $this->get_buckaroo_uri());

		$post_response = $this->guzzleClient->post("https://{$this->get_buckaroo_uri()}", [
		'headers' => [
		'Content-Type' => 'application/json',
		'Authorization' => $authorization_header,
		],
		'body' => \wp_json_encode($post_array),
		]);

		$post_response_json = json_decode((string) $post_response->getBody(), true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new Buckaroo_Request_Exception(esc_html__('Invalid JSON in response body', 'eightshift-forms'));
		}

		if (! isset($post_response_json['RequiredAction']['RedirectURL'])) {
			throw new Buckaroo_Request_Exception(esc_html__('Missing redirect URL in Buckaroo response', 'eightshift-forms'), $post_response_json);
		}

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
	public function setRedirectUrls(string $redirect_url, string $redirect_url_cancel, string $redirect_url_error, string $redirect_url_reject)
	{
		$this->set_return_url($redirect_url);
		$this->set_return_url_cancel($redirect_url_cancel);
		$this->set_return_url_error($redirect_url_error);
		$this->set_return_url_reject($redirect_url_reject);
	}

  /**
   * Generates the invoice name based on submitted data + salted with time (meaning it should always be unique)-
   *
   * @param  array $params Parameters from request.
   * @return string
   */
	public function generate_debtor_reference(array $params)
	{
		$prefix      = 'debtor';
		$data_hash   = hash('crc32', (string) wp_json_encode($params));
		$random_hash = hash('crc32', uniqid());
		return "{$prefix}-{$data_hash}-{$random_hash}";
	}

  /**
   * Generates the invoice name based on submitted data + salted with time (meaning it should always be unique)-
   *
   * @param  array $params Parameters from request.
   * @return string
   */
	public function generate_invoice_name(array $params)
	{
		$prefix      = 'invoice';
		$data_hash   = hash('crc32', (string) wp_json_encode($params));
		$random_hash = hash('crc32', uniqid());
		return "{$prefix}-{$data_hash}-{$random_hash}";
	}

  /**
   * Generates the invoice name based on submitted data + salted with time (meaning it should always be unique)-
   *
   * @param  array $params Parameters from request.
   * @return string
   */
	public function generate_purchase_id(array $params)
	{
		$prefix      = 'purchase-id';
		$data_hash   = hash('crc32', (string) wp_json_encode($params));
		$random_hash = hash('crc32', uniqid());
		return "{$prefix}-{$data_hash}-{$random_hash}";
	}

  /**
   * Set if you need to use the test URI instead of live one.
   *
   * @return void
   */
	public function set_test(): void
	{
		$this->isTestUri = true;
	}

  /**
   * Set if you need to use the test URI instead of live one.
   *
   * @return void
   */
	public function set_data_request(): void
	{
		$this->isDataRequest = true;
	}

  /**
   * Set's currency as uppercase 3-letter string (example: EUR)
   *
   * @param  string $currency Currency string.
   * @return void
   */
	public function set_currency(string $currency): void
	{
		$this->currency = $currency;
	}

  /**
   * Getter for $this->currency.
   *
   * @return string
   */
	public function get_currency(): string
	{
		return $this->currency;
	}

  /**
   * Get type of payment. Defaults to iDEAL.
   *
   * @return  string
   */
	public function get_pay_type()
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
	public function set_pay_type(string $payType)
	{
		$this->payType = $payType;
		return $this;
	}

  /**
   * Get return URL after payment.
   *
   * @return  string
   */
	public function get_return_url()
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
	public function set_return_url(string $returnUrl)
	{
		$this->returnUrl = $returnUrl;

		return $this;
	}

  /**
   * Get return URL after payment cancel.
   *
   * @return  string
   */
	public function get_return_url_cancel()
	{
		return $this->returnUrlCancel;
	}

  /**
   * Set return URL after payment cancel.
   *
   * @param  string $returnUrlCancel  Return URL after payment cancel.
   * @return  self
   */
	public function set_return_url_cancel(string $returnUrlCancel)
	{
		$this->returnUrlCancel = $returnUrlCancel;

		return $this;
	}

  /**
   * Get return URL after payment error.
   *
   * @return  string
   */
	public function get_return_url_error()
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
	public function set_return_url_error(string $returnUrlError)
	{
		$this->returnUrlError = $returnUrlError;

		return $this;
	}

  /**
   * Get return URL after payment reject.
   *
   * @return  string
   */
	public function get_return_url_reject()
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
	public function set_return_url_reject(string $returnUrlReject)
	{
		$this->returnUrlReject = $returnUrlReject;

		return $this;
	}

  /**
   * Generates the correct authorization header.
   *
   * @param array  $post_array   Array of post data we're sending to Buckaroo.
   * @param string $buckaroo_uri Buckaroo URI we're posting to.
   * @return string
   */
	private function generate_authorization_header(array $post_array, string $buckaroo_uri): string
	{
		$this->verify_buckaroo_info_exists();
		$website_key = \apply_filters(self::BUCKAROO, 'website_key');
		$secret_key  = \apply_filters(self::BUCKAROO, 'secret_key');
		$post        = (string) \wp_json_encode($post_array);
		$md5         = md5($post, true);
		$post        = base64_encode($md5); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$uri         = strtolower(rawurlencode($buckaroo_uri));
		$nonce       = \wp_rand(0000000, 9999999);
		$time        = time();

		$hmac     = $website_key . 'POST' . $uri . $time . $nonce . $post;
		$sha_hash = hash_hmac('sha256', $hmac, $secret_key, true);
		$hmac     = base64_encode($sha_hash); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

		return "hmac {$website_key}:{$hmac}:{$nonce}:{$time}";
	}

  /**
   * Builds the body of request
   *
   * @param  int|float|string $donation_amount Donation amount.
   * @param  string           $invoice         Invoice name.
   * @param  string           $issuer          Issuer (bank) name.
   * @param  bool             $is_recurring    Is recurring payment.
   * @param  string           $description     Description of the payment.
   * @return array
   */
	private function build_post_body_for_payment($donation_amount, string $invoice, string $issuer, bool $is_recurring, string $description): array
	{
		$this->verify_buckaroo_info_exists();

		$post_array = [
		'Currency' => $this->get_currency(),
		'AmountDebit' => $donation_amount,
		'Invoice' => $invoice,
		'ContinueOnIncomplete' => 1,
		'Services' => [
		'ServiceList' => [],
		],
		'Description' => $description,
		];

	  // Set payment to recurring if needed.
		if ($is_recurring) {
			$post_array['StartRecurrent'] = 'True';
		}

		$service_array = [
		'Action' => 'Pay',
		'Name' => $this->get_pay_type(),
		'Parameters' => [],
		];

	  // Add issuing bank if provided as part of request.
		if (! empty($issuer)) {
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
   * @return array
   */
	private function build_post_body_for_emandate(string $debtorreference, string $sequencetype, string $purchaseid, string $language, string $issuer, string $emandatereason): array
	{
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
		if (! empty($issuer)) {
			$service_array['Parameters'][] = [
			'Name' => 'debtorbankid',
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
   * Make sure we have the data we need defined as filters.
   *
   * @throws Missing_Filter_Info_Exception When not all required keys are set.
   *
   * @return void
   */
	private function verify_buckaroo_info_exists(): void
	{
		if (empty(\apply_filters(self::BUCKAROO, 'website_key'))) {
			throw Missing_Filter_Info_Exception::view_exception(self::BUCKAROO, 'website_key');
		}

		if (empty(\apply_filters(self::BUCKAROO, 'secret_key'))) {
			throw Missing_Filter_Info_Exception::view_exception(self::BUCKAROO, 'secret_key');
		}
	}

  /**
   * Get the correct url depending on if we're testing or not.
   *
   * @return string
   */
	private function get_buckaroo_uri(): string
	{
		return $this->is_test() ? $this->get_buckaroo_uri_test() : $this->get_buckaroo_uri_live();
	}

  /**
   * Returns correct Buckaroo live uri.
   *
   * @return string
   */
	private function get_buckaroo_uri_live(): string
	{
		return $this->isDataRequest() ? self::LIVE_URI_DATA_REQUEST : self::LIVE_URI_TRANSACTION;
	}

  /**
   * Returns correct Buckaroo test uri.
   *
   * @return string
   */
	private function get_buckaroo_uri_test(): string
	{
		return $this->isDataRequest() ? self::TEST_URI_DATA_REQUEST : self::TEST_URI_TRANSACTION;
	}

  /**
   * Check if we're running a test or not.
   *
   * @return boolean
   */
	private function is_test(): bool
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
