<?php

/**
 * The class to provide integration items from cache.
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Mailchimp
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Mailchimp;

use EightshiftForms\AdminMenus\FormSettingsAdminSubMenu;
use EightshiftForms\Integrations\Mailchimp\MailchimpClientInterface;
use EightshiftForms\Integrations\Mailchimp\SettingsMailchimp;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use WP_REST_Request;

/**
 * Class IntegrationItemsMailchimpRoute
 */
class IntegrationItemsMailchimpRoute extends AbstractBaseRoute
{
	/**
	 * Instance variable for Mailchimp data.
	 *
	 * @var MailchimpClientInterface
	 */
	protected $mailchimpClient;

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/integration-items-mailchimp';

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return self::ROUTE_SLUG;
	}

	/**
	 * Create a new instance that injects classes
	 *
	 * @param MailchimpClientInterface $mailchimpClient Inject HubSpot which holds HubSpot connect data.
	 */
	public function __construct(MailchimpClientInterface $mailchimpClient)
	{
		$this->mailchimpClient = $mailchimpClient;
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
		return static::READABLE;
	}

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
		if (! \current_user_can(FormSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY)) {
			\rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('You don\'t have enough permissions to perform this action!', 'eightshift-forms'),
			]);
		}

		// Check if Mailchimp global settings is valid.
		$isGlobalSettingsValid = \apply_filters(SettingsMailchimp::FILTER_SETTINGS_GLOBAL_NAME, false);

		if (!$isGlobalSettingsValid) {
			\rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Global not configured', 'eightshift-forms'),
			]);
		}

		$items = $this->mailchimpClient->getItems();

		if (!$items) {
			\rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Items missing', 'eightshift-forms'),
			]);
		}

		$items = \array_values(\array_map(
			static function ($item) {
				return [
					'label' => $item['title'],
					'value' => $item['id'],
				];
			},
			$items
		));

		// Exit with success.
		return \rest_ensure_response([
			'code' => 200,
			'status' => 'success',
			'data' => [
				[
					'label' => '',
					'value' => '',
				],
				...$items,
			],
		]);
	}
}
