<?php

/**
 * The class register route for versions migration endpoint
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\AdminMenus\FormSettingsAdminSubMenu;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Migration\SettingsMigration;
use EightshiftForms\Settings\Settings\Settings;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Troubleshooting\SettingsFallback;
use EightshiftForms\Validation\ValidatorInterface;
use WP_REST_Request;

/**
 * Class MigrationRoute
 */
class MigrationRoute extends AbstractBaseRoute
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	protected $validator;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 */
	public function __construct(
		ValidatorInterface $validator
	) {
		$this->validator = $validator;
	}

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/migration';

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

		$params = $request->get_body_params();

		if (!isset($params['type'])) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Migration version type key was not provided.', 'eightshift-forms'),
			]);
		}

		$type = $params['type'];

		switch ($params['type']) {
			case SettingsMigration::VERSION_2_3:
				$success = $this->getMigration2To3();
				break;
			default:
				$success = false;
				break;
		}

		if (!$success) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Version type key is not valid.', 'eightshift-forms'),
			]);
		}

		return \rest_ensure_response([
			'code' => 200,
			'status' => 'success',
			// translators: %s will be replaced with the migration type.
			'message' => \sprintf(\esc_html__('Migration %s successfully done!', 'eightshift-forms'), $type),
		]);
	}

	/**
	 * Migration version 2-3.
	 *
	 * @return boolean
	 */
	private function getMigration2To3(): bool
	{
		$config = [
			'options' => [
				'new' => SettingsFallback::SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY,
				'use' => SettingsFallback::SETTINGS_FALLBACK_USE_KEY,
				'old' => 'troubleshooting-fallback-email',
			],
		];

		// Migrate global fallback.
		$globalFallback = $this->getOptionValue($config['options']['old']);

		if ($globalFallback) {
			\update_option($this->getSettingsName($config['options']['new']), $globalFallback);
			\update_option($this->getSettingsName($config['options']['use']), $config['options']['use']);
			\delete_option($this->getSettingsName($config['options']['old']));
		}

		// Migrate each integration fallback.
		foreach (Filters::ALL as $key => $value) {
			if ($value['type'] !== Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION) {
				continue;
			}

			$globalIntegrationFallback = $this->getOptionValue($config['options']['old'] . '-' . $key);

			if ($globalIntegrationFallback) {
				\update_option($this->getSettingsName($config['options']['new'] . '-' . $key), $globalIntegrationFallback);
				\delete_option($this->getSettingsName($config['options']['old'] . '-' . $key));
			}
		}


		return true;
	}
}
