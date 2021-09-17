<?php

/**
 * The class register route for Base endpoint used on all forms.
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Config\Config;
use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Mailer\MailerInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsPluginVendor\EightshiftLibs\Rest\Routes\AbstractRoute;
use EightshiftFormsPluginVendor\EightshiftLibs\Rest\CallableRouteInterface;

/**
 * Class FormSubmitRoute
 */
abstract class AbstractBaseRoute extends AbstractRoute implements CallableRouteInterface
{

	/**
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	public $validator;

	/**
	 * Instance variable of MailerInterface data.
	 *
	 * @var MailerInterface
	 */
	public $mailer;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 * @param MailerInterface $mailer Inject MailerInterface which holds mailer methods.
	 */
	public function __construct(ValidatorInterface $validator, MailerInterface $mailer)
	{
		$this->validator = $validator;
		$this->mailer = $mailer;
	}

	/**
	 * Method that returns project Route namespace.
	 *
	 * @return string Project namespace EightshiftFormsPluginVendor\for REST route.
	 */
	protected function getNamespace(): string
	{
		return Config::getProjectRoutesNamespace();
	}

	/**
	 * Method that returns project route version.
	 *
	 * @return string Route version as a string.
	 */
	protected function getVersion(): string
	{
		return Config::getProjectRoutesVersion();
	}

	/**
	 * Returns allowed methods for this route.
	 *
	 * @return string
	 */
	protected function getMethods(): string
	{
		return static::CREATABLE;
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
	 * Sanitizes all received fields recursively. If a field is something we don't need to
	 * sanitize then we don't touch it.
	 *
	 * @param  array $params Array of params.
	 * @return array
	 */
	protected function sanitizeFields(array $params)
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
	 * WordPress replaces dots with underscores for some reason. This is undesired behavior when we need to map
	 * need record field values to existing lookup fields (we need to use @odata.bind in field's key).
	 *
	 * Quick and dirty fix is to replace these values back to dots after receiving them.
	 *
	 * @param array $params Request params.
	 * @return array
	 */
	protected function fixDotUnderscoreReplacement(array $params): array
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
		$params = $this->sanitizeFields($request->get_query_params());
		$params = $this->fixDotUnderscoreReplacement($params);
		$postParams = $this->sanitizeFields($request->get_body_params());

		// Verify nonce if submitted.
		if ($this->requiresNonceVerification()) {
			if (
				! isset($params['nonce']) ||
				! isset($params['form-unique-id']) ||
				! wp_verify_nonce($params['nonce'], $params['form-unique-id'])
			) {
				throw new UnverifiedRequestException(
					\rest_ensure_response(
						[
							'code' => 400,
							'status' => 'error',
							'message' => \esc_html__('Invalid nonce.', 'eightshift-forms'),
						]
					)->data
				);
			}
		}

		// Validate GET Params.
		$validateParams = $this->validator->validate($params);
		if (!empty($validateParams)) {
			throw new UnverifiedRequestException(
				\rest_ensure_response(
					[
						'code' => 400,
						'status' => 'error_validation',
						'message' => \esc_html__('Missing one or more required GET parameters to process the request.', 'eightshift-forms'),
						'validation' => $validateParams,
					]
				)->data
			);
		}

		// Validate POST Params.
		$validatePostParams = $this->validator->validate($postParams);
		if (!empty($validatePostParams)) {
			throw new UnverifiedRequestException(
				\rest_ensure_response(
					[
						'code' => 400,
						'status' => 'error_validation',
						'message' => \esc_html__('Missing one or more required POST parameters to process the request.', 'eightshift-forms'),
						'validation' => $validatePostParams,
					]
				)->data
			);
		}

		return [
			'get' => $params,
			'post' => $postParams,
		];
	}
}
