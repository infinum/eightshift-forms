<?php

/**
 * Base route class used for build other routes.
 *
 * @package EightshiftForms\Rest
 */

declare(strict_types=1);

namespace EightshiftForms\Rest;

use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Integrations\Authorization\Hmac;
use EightshiftForms\Config\Config;
use EightshiftLibs\Rest\CallableRouteInterface;
use EightshiftLibs\Rest\Routes\AbstractRoute;

/**
 * Class BaseRoute
 */
abstract class BaseRoute extends AbstractRoute implements CallableRouteInterface, ActiveRouteInterface
{

  /**
   * Endpoint slug for the implementing class. Needs to be overriden.
   *
   * @var string
   */
	public const ENDPOINT_SLUG = 'override-me';

  /**
   * Key for the missing keys response. Used if route has required keys but not all are sent.
   *
   * @var string
   */
	public const MISSING_KEYS = 'missing-keys';

  /**
   * Missing filter key. Used if route has required filter which wasn't implemented in your project.
   *
   * @var string
   */
	public const MISSING_FILTER = 'missing-filter';

  /**
   * Method that returns rest response
   *
   * @param  \WP_REST_Request $request Data got from endpoint url.
   *
   * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
   *                                is already an instance, WP_HTTP_Response, otherwise
   *                                returns a new WP_REST_Response instance.
   */
	abstract public function routeCallback(\WP_REST_Request $request);

  /**
   * Returns the relative route uri.
   *
   * @return string
   */
	public function getRouteUri(): string
	{
		return '/wp-json/' . $this->getNamespace() . '/' . $this->getVersion() . $this->getRouteName();
	}

  /**
   * By default allow public access to route.
   *
   * @return bool
   */
	public function permissionCallback(): bool
	{
		return true;
	}

  /**
   * Method that returns project Route namespace.
   *
   * @return string Project namespace for REST route.
   */
	protected function getNamespace(): string
	{
		return Config::getProjectName();
	}

  /**
   * Method that returns project route version.
   *
   * @return string Route version as a string.
   */
	protected function getVersion(): string
	{
		return 'v1';
	}

  /**
   * Get the base url of the route
   *
   * @return string The base URL for route you are adding.
   */
	protected function getRouteName(): string
	{
		return static::ENDPOINT_SLUG;
	}

  /**
   * Get callback arguments array
   *
   * @return array Either an array of options for the endpoint, or an array of arrays for multiple methods.
   */
	protected function getCallbackArguments(): array
	{
		return [
			'methods'  => $this->getMethods(),
			'callback' => [ $this, 'routeCallback' ],
			'permission_callback' => [ $this, 'permissionCallback' ],
		];
	}

  /**
   * Returns allowed methods for this route.
   *
   * @return string|array
   */
	protected function getMethods()
	{
		return static::READABLE;
	}

  /**
   * Verifies everything is ok with request
   *
   * @param  \WP_REST_Request $request WP_REST_Request object.
   * @param  string           $requiredFilter (Optional) Filter that needs to exist to verify this request.
   *
   * @throws UnverifiedRequestException When we should abort the request for some reason.
   *
   * @return array            filtered request params.
   */
	protected function verifyRequest(\WP_REST_Request $request, string $requiredFilter = ''): array
	{

	  // If this route requires a filter defined in project, we need to make sure that is defined.
		if (! empty($requiredFilter) && ! has_filter($requiredFilter)) {
			throw new UnverifiedRequestException(
				$this->restResponseHandler('integration-not-used', [ self::MISSING_FILTER => $requiredFilter ])->data
			);
		}

		$params      = $this->sanitizeFields($request->get_query_params());
		$params      = $this->fixDotUnderscoreReplacement($params);
		$postParams = $this->sanitizeFields($request->get_body_params());

	  // Authorized routes need to provide the correct authorization hash to do anything.
		if (! empty($this->getAuthorizationSalt())) {
			$hash = $params[Hmac::AUTHORIZATION_KEY] ?? 'invalid-hash';
			unset($params[Hmac::AUTHORIZATION_KEY]);

		  // We need to URLencode all params before verifying them.
			$params = $this->urlencodeParams($params);

			if (empty($this->hmac) || ! $this->hmac->verify_hash($hash, $params, $this->getAuthorizationSalt())) {
				throw new UnverifiedRequestException(
					$this->restResponseHandler('authorization-invalid')->data
				);
			}
		}

	  // Verify nonce if submitted.
		if ($this->requiresNonceVerification()) {
			if (
				! isset($params['nonce']) ||
				! isset($params['form-unique-id']) ||
				! wp_verify_nonce($params['nonce'], $params['form-unique-id'])
			) {
				throw new UnverifiedRequestException(
					$this->restResponseHandler('invalid-nonce')->data
				);
			}
		}

	  // If captcha is used on this route and provided as part of the request, we need to confirm it's true.
		if (! empty($this->basicCaptcha) && ! $this->basicCaptcha->checkCaptchaFromRequestParams($params)) {
			throw new UnverifiedRequestException($this->restResponseHandler('wrong-captcha')->data);
		}

	  // If this route has required parameters, we need to make sure they're all provided.
		$missingParams = $this->findRequiredMissingParams($params);
		if (! empty($missingParams)) {
			throw new UnverifiedRequestException(
				$this->restResponseHandler('missing-params', $missingParams)->data
			);
		}

	  // If this route has required parameters, we need to make sure they're all provided.
		$missingPostParams = $this->findRequiredMissingParams($postParams, true);
		if (! empty($missingPostParams)) {
			throw new UnverifiedRequestException(
				$this->restResponseHandler('missing-post-params', $missingPostParams)->data
			);
		}

		return $params;
	}

  /**
   * Defines a list of required parameters which must be present in the request as GET parameters or it will error out.
   *
   * @return array
   */
	protected function getRequiredParams(): array
	{
		return [];
	}

  /**
   * Defines a list of required parameters which must be present in the request as POST parameters or it will error out.
   *
   * @return array
   */
	protected function getRequiredPostParams(): array
	{
		return [];
	}

  /**
   * Define name of the filter used for filtering required GET params.
   *
   * @return string
   */
	protected function getRequiredParamsFilter(): string
	{
		return 'invalid_get_params_filter';
	}

  /**
   * Define name of the filter used for filtering required GET params.
   *
   * @return string
   */
	protected function getRequiredPostParamsFilter(): string
	{
		return 'invalid_post_params_filter';
	}

  /**
   * URLencode all string params in request.
   *
   * @param  array $params Array of request params.
   * @return array
   */
	protected function urlencodeParams(array $params): array
	{
		return array_map(function ($param) {
			return is_string($param) ? rawurlencode($param) : $param;
		}, $params);
	}

  /**
   * Replaces all placeholders inside a string with actual content from $params (if possible). If not just
   * leave the placeholder in text.
   *
   * @param  string $haystack String in which to look for placeholders.
   * @param  array  $params   Array of params which should hold content for placeholders.
   * @return string
   */
	protected function replacePlaceholdersWithContent(string $haystack, array $params): string
	{
		$content = $haystack;

		$content = preg_replace_callback('/\[\[(?<placeholder_key>.+?)\]\]/', function ($match) use ($params) {
			$output = $match[0];
			if (isset($params[$match['placeholder_key']])) {
				$output = $params[$match['placeholder_key']];
			}

			return $output;
		}, $haystack);

		return (string) $content;
	}

  /**
   * Provide the expected salt ($this->getAuthorizationSalt()) for this route. This
   * should be some secret. For example the secret_key for accessing the 3rd party route this route is
   * handling.
   *
   * If this function returns a non-empty value, it is assumed the route requires authorization.
   *
   * @return string
   */
	protected function getAuthorizationSalt(): string
	{
		return '';
	}

  /**
   * Returns keys of irrelevant params which we don't want to send to CRM (even tho they're in form).
   *
   * @return array
   */
	protected function getIrrelevantParams(): array
	{
		return [];
	}

  /**
   * Toggle if this route requires nonce verification.
   *
   * @return bool
   */
	protected function requiresNonceVerification(): bool
	{
		return false;
	}

  /**
   * Removes some params we don't want to send to CRM from request.
   *
   * @param  array $params Params received in request.
   * @return array
   */
	protected function unsetIrrelevantParams(array $params): array
	{
		$filteredParams   = [];
		$irrelevantParams = array_flip($this->getIrrelevantParams());

		foreach ($params as $key => $param) {
			if (! isset($irrelevantParams[$key])) {
				$filteredParams[$key] = $param;
			}
		}

		return $filteredParams;
	}

  /**
   * Response handler for unknown errors
   *
   * @param  array $data (Optional) data to output.
   * @return \WP_REST_Response|WP_Error|WP_HTTP_Response|mixed
   */
	protected function restResponseHandlerUnknownError(array $data = [])
	{
		return \rest_ensure_response(
			[
			'code' => 400,
			'message' => esc_html__('Unknown error', 'eightshift-forms'),
			'data' => $data,
			]
		);
	}

  /**
   * Ensure correct response for rest using error handler function.
   *
   * @param  string $responseKey Which response to get.
   * @param  array  $data         (Optional) Data to pass to response handler.
   *
   * @return \WP_REST_Response|WP_Error|WP_HTTP_Response|mixed
   */
	protected function restResponseHandler(string $responseKey, array $data = [])
	{
		$responses = array_merge($this->routeResponses(), $this->allResponses());

		$response = $responses[$responseKey] ?? [
		'code' => 400,
		'message' => esc_html__('Undefined response', 'eightshift-forms'),
		];

		$response['data'] = $data;
		return \rest_ensure_response($response);
	}

  /**
   * Define a list of responses for this route.
   *
   * @return array
   */
	protected function routeResponses(): array
	{
		return [];
	}

  /**
   * A list of all responses.
   *
   * @return array
   */
	private function allResponses(): array
	{
		return [
			'invalid-nonce' => [
				'code' => 400,
				'message' => esc_html__('Invalid nonce.', 'eightshift-forms'),
			],
				'wrong-captcha' => [
				'code' => 429,
				'message' => esc_html__('Wrong captcha answer.', 'eightshift-forms'),
			],
				'send-email-error' => [
				'code' => 400,
				'message' => esc_html__('Error while sending an email.', 'eightshift-forms'),
			],
				'missing-params' => [
				'code' => 400,
				'message' => esc_html__('Missing one or more required GET parameters to process the request.', 'eightshift-forms'),
			],
				'missing-post-params' => [
				'code' => 400,
				'message' => esc_html__('Missing one or more required POST parameters to process the request.', 'eightshift-forms'),
			],
				'integration-not-used' => [
				'code' => 400,
				'message' => sprintf(esc_html__('This form integration is not used, please add a filter returning all necessary info.', 'eightshift-forms')),
			],
				'authorization-invalid' => [
				'code' => 400,
				'message' => sprintf(esc_html__('Unauthorized request', 'eightshift-forms')),
			],
				'invalid-email-error' => [
				'code' => 400,
				'message' => sprintf(esc_html__('Please enter a valid email.', 'eightshift-forms')),
			],

			// Buckaroo specific.
			'buckaroo-missing-keys' => [
				'code' => 400,
				'message' => esc_html__('Not all Buckaroo keys are set', 'eightshift-forms'),
			],
			'buckaroo-request-exception' => [
				'code' => 400,
				'message' => esc_html__('Error ocurred, unable to redirect to Buckaroo', 'eightshift-forms'),
			],

			// Mailchimp specific.
			'mailchimp-missing-keys' => [
				'code' => 400,
				'message' => esc_html__('Not all Mailchimp API info is set', 'eightshift-forms'),
			],

			'mailchimp-missing-list-id' => [
				'code' => 400,
				'message' => esc_html__('Please set a valid List ID in Form options in editor.', 'eightshift-forms'),
			],

			'mailchimp-missing-email' => [
				'code' => 400,
				'message' => esc_html__('Please enter your email.', 'eightshift-forms'),
			],

			// Mailerlite specific.
			'mailerlite-missing-keys' => [
				'code' => 400,
				'message' => esc_html__('Not all Mailerlite API info is set', 'eightshift-forms'),
			],

			'mailerlite-missing-group-id' => [
				'code' => 400,
				'message' => esc_html__('Please set a valid Group ID.', 'eightshift-forms'),
			],

			'mailerlite-blocked-email' => [
				'code' => 400,
				'message' => esc_html__('Provided email looks suspicious and it is flagged as spam. Please provide a different email or contact administrator.', 'eightshift-forms'),
			],

			'mailerlite-missing-email' => [
				'code' => 400,
				'message' => esc_html__('Please enter your email.', 'eightshift-forms'),
			],
		];
	}

  /**
   * Checks if all required parameters are present in request.
   *
   * @param  array $parameters Array of request parameters.
   * @param  bool  $isPost    (Optional) True if we're checking POST params instead of GET params.
   * @return array Returns array of missing parameters to pass in response.
   */
	private function findRequiredMissingParams(array $parameters, bool $isPost = false): array
	{
		$missingParams       = [];
		$requiredParamsGet  = has_filter($this->getRequiredParamsFilter()) ? apply_filters($this->getRequiredParamsFilter(), $this->getRequiredParams()) : $this->getRequiredParams();
		$requiredParamsPost = has_filter($this->getRequiredPostParamsFilter()) ? apply_filters($this->getRequiredPostParamsFilter(), $this->getRequiredPostParams()) : $this->getRequiredPostParams();
		$requiredParams      = $isPost ? $requiredParamsPost : $requiredParamsGet;

		$this->getRequiredParams();
		foreach ($requiredParams as $requiredParam) {
			if (! isset($parameters[$requiredParam])) {
				$missingParams[self::MISSING_KEYS][] = $requiredParam;
			}
		}

		return $missingParams;
	}

  /**
   * WordPress replaces dots with underscores for some reason. This is undesired behavior when we need to map
   * need record field values to existing lookup fields (we need to use @odata.bind in field's key).
   *
   * Quick and dirty fix is to replace these values back to dots after receiving them.
   *
   * @param array $params Request params.
   * @return array
   */
	private function fixDotUnderscoreReplacement(array $params): array
	{
		foreach ($params as $key => $value) {
			if (strpos($key, '@odata_bind') !== false) {
				$newKey = str_replace('@odata_bind', '@odata.bind', $key);
				unset($params[$key]);
				$params[$newKey] = $value;
			}
		}

		return $params;
	}

  /**
   * Sanitizes all received fields recursively. If a field is something we don't need to
   * sanitize then we don't touch it.
   *
   * @param  array $params Array of params.
   * @return array
   */
	private function sanitizeFields(array $params)
	{
		foreach ($params as $key => $param) {
			if (is_string($param)) {
				$params[$key] = \wp_unslash(\sanitize_text_field($param));
			} elseif (is_array($param)) {
				$params[$key] = $this->sanitizeFields($param);
			}
		}

		return $params;
	}
}
