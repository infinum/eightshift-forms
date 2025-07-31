<?php

/**
 * The class register route for Base endpoint used on all forms.
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\DeveloperHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Security\SecurityInterface;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
use EightshiftFormsVendor\EightshiftLibs\Rest\CallableRouteInterface;
use EightshiftFormsVendor\EightshiftLibs\Rest\Routes\AbstractRoute;
use WP_REST_Request;

/**
 * Class AbstractBaseRoute
 */
abstract class AbstractBaseRoute extends AbstractRoute implements CallableRouteInterface
{
	public const R_MSG = 'message';
	public const R_CODE = 'code';
	public const R_STATUS = 'status';
	public const R_DATA = 'data';
	public const R_DEBUG = 'debug';
	public const R_DEBUG_KEY = 'debugKey';
	public const R_DEBUG_USER = 'debugUser';
	public const R_DEBUG_REQUEST = 'debugRequest';
	public const R_DEBUG_SUCCESS_ADDITIONAL_DATA = 'debugSuccessAdditionalData';

	/**
	 * Instance variable of SecurityInterface data.
	 *
	 * @var SecurityInterface
	 */
	protected $security;

	/**
	 * Create a new instance that injects classes.
	 * 
	 * @param SecurityInterface $security Security interface.
	 */
	public function __construct(SecurityInterface $security)
	{
		$this->security = $security;
	}

	/**
	 * Check if the route is admin protected.
	 *
	 * @return boolean
	 */
	abstract protected function isRouteAdminProtected(): bool;

	/**
	 * Get mandatory params.
	 *
	 * @param array<string, mixed> $params Params passed from the request.
	 *
	 * @return array<string, string>
	 */
	abstract protected function getMandatoryParams(array $params): array;

	/**
	 * Method that returns project Route namespace
	 *
	 * @return string Project namespace for REST route.
	 */
	protected function getNamespace(): string
	{
		return Config::ROUTE_NAMESPACE;
	}

	/**
	 * Method that returns project route version
	 *
	 * @return string Route version as a string.
	 */
	protected function getVersion(): string
	{
		return Config::ROUTE_VERSION;
	}

	/**
	 * Get callback arguments array
	 *
	 * @return array<string, mixed> Either an array of options for the endpoint, or an array of arrays for multiple methods.
	 */
	protected function getCallbackArguments(): array
	{
		return [
			'methods' => $this->getMethods(),
			'callback' => [$this, 'routeCallback'],
			'permission_callback' => [$this, 'permissionCallback'],
		];
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
	 * Toggle if this route requires nonce verification.
	 *
	 * @return bool
	 */
	protected function requiresNonceVerification(): bool
	{
		return false;
	}

	/**
	 * Returns security class.
	 *
	 * @return SecurityInterface
	 */
	protected function getSecurity()
	{
		return $this->security;
	}

	/**
	 * Extract params from request.
	 * Check if array then output only value that is not empty.
	 *
	 * @param WP_REST_Request $request $request Data got from endpoint url.
	 * @param string $type Request type.
	 *
	 * @return array<string, mixed>
	 */
	protected function getRequestParams(WP_REST_Request $request, string $type = self::CREATABLE): array
	{
		// Check type of request and extract params.
		switch ($type) {
			case self::CREATABLE:
				$params = $request->get_body_params();
				break;
			case self::READABLE:
				$params = $request->get_params();
				break;
			default:
				$params = [];
				break;
		}

		// Check if request maybe has json params usually sent by the Block editor.
		if ($request->get_json_params()) {
			$params = \array_merge(
				$params,
				$request->get_json_params(),
			);
		}

		return $params;
	}

	/**
	 * Get debug output.
	 *
	 * @param array<string, mixed> $data Data to use.
	 * @param array<string, mixed> $debug Debug data to use.
	 * @param WP_REST_Request $request Request to use.
	 *
	 * @return array<string, mixed>
	 */
	protected function getResponseDataOutput(
		array $data,
		array $debug,
		WP_REST_Request $request
	): array {
		$output = [];

		// Check if there are any response output keys in the data and allowed to be returned.
		foreach (UtilsHelper::getStateResponseOutputKeys() as $key) {
			if (isset($data[$key]) && !empty($data[$key])) {
				$output[$key] = $data[$key];
			}
		}

		$output[self::R_DEBUG] = [
			self::R_DEBUG_KEY => $debug[self::R_DEBUG_KEY] ?? '',
			self::R_DEBUG => $debug[self::R_DEBUG] ?? [],
			self::R_DEBUG_USER => [
				'ip' => $this->getSecurity()->getIpAddress('anonymize'),
				'forms' => Helpers::getPluginVersion(),
				'php' => \phpversion(),
				'wp' => \get_bloginfo('version'),
				'url' => \get_bloginfo('url'),
				'userAgent' => isset($_SERVER['HTTP_USER_AGENT']) ? \sanitize_text_field(\wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '',
				'time' => \wp_date('Y-m-d H:i:s'),
				'requestUrl' => Helpers::getCurrentUrl(),
			],
			self::R_DEBUG_REQUEST => [
				'body' => $request->get_body(),
				'params' => $request->get_params(),
				'method' => $request->get_method(),
				'headers' => $request->get_headers(),
				'bodyParams' => $request->get_body_params(),
				'queryParams' => $request->get_query_params(),
				'urlParams' => $request->get_url_params(),
				'route' => $request->get_route(),
			],
		];

		return $output;
	}

	/**
	 * Clean up debug output.
	 *
	 * @param array<string, mixed> $data Data to use.
	 *
	 * @return array<string, mixed>
	 */
	protected function cleanUpDebugOutput(array $data): array
	{
		$isDeveloperMode = DeveloperHelpers::isDeveloperModeActive();

		if ($isDeveloperMode && $this->checkPermission(Config::CAP_SETTINGS_GLOBAL)) {
			return $data;
		}

		unset($data[self::R_DEBUG]);

		return $data;
	}

	/**
	 * Check user permission for route action.
	 *
	 * @param string $permission Permission to check.
	 *
	 * @return bool
	 */
	protected function checkPermission(string $permission): bool
	{
		return \current_user_can($permission);
	}
}
