<?php

/**
 * The class register route for Oauth.
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\SettingsHelpers;
use WP_REST_Request;

/**
 * Class AbstractOauth
 */
abstract class AbstractOauth extends AbstractBaseRoute
{
	/**
	 * Dynamic name route prefix for oauth.
	 *
	 * @var string
	 */
	public const ROUTE_PREFIX_OAUTH_API = 'oauth';

	/**
	 * Method that returns rest response
	 *
	 * @param WP_REST_Request $request Data got from endpoint url.
	 *
	 * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function routeCallback(WP_REST_Request $request)
	{
		if (!$this->checkEnabledConnectionPermission()) {
			$this->redirect(\esc_html__('You do not have permission to access this page.', 'eightshift-forms'));
		}

		try {
			$code = $request->get_param('code');

			// No code parameter? Redirect to login.
			if (!$code) {
				$this->redirect(\esc_html__('Code parameter is empty.', 'eightshift-forms'));
			}

			// If code parameter is not a string, redirect to login.
			if (!\is_string($code)) {
				$this->redirect(
					\sprintf(
						/* translators: % denotes type or code parameter. */
						\esc_html__('Code parameter should be a string, %s provided.', 'eightshift-forms'),
						\gettype($code)
					)
				);
			}

			// Sanitize code parameter for security.
			$code = \sanitize_text_field($code);

			return $this->submitAction($code);
		} catch (UnverifiedRequestException $e) {
			$this->redirect(\esc_html__('Error.', 'eightshift-forms'));
		}
	}

	/**
	 * Implement submit action.
	 *
	 * @param string $code The code.
	 *
	 * @return mixed
	 */
	abstract protected function submitAction(string $code);

	/**
	 * Get the oauth type.
	 *
	 * @return string
	 */
	abstract protected function getOauthType(): string;

	/**
	 * Get the oauth allow key.
	 *
	 * @return string
	 */
	abstract protected function getOauthAllowKey(): string;

	/**
	 * Returns allowed methods for this route.
	 *
	 * @return string
	 */
	protected function getMethods(): string
	{
		return static::READABLE;
	}

	/**
	 * Redirect with error message
	 *
	 * @param string $message Message to output.
	 *
	 * @return void
	 */
	protected function redirect(string $message): void
	{
		\wp_safe_redirect(
			\add_query_arg(
				[
					'oauthMsg' => \esc_html($message),
				],
				GeneralHelpers::getSettingsGlobalPageUrl($this->getOauthType())
			)
		);
		exit;
	}

	/**
	 * Check if connection permission is enabled.
	 *
	 * @return boolean
	 */
	protected function checkEnabledConnectionPermission(): bool
	{
		return SettingsHelpers::isOptionCheckboxChecked($this->getOauthAllowKey(), $this->getOauthAllowKey());
	}
}
