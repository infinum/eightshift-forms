<?php

/**
 * The class to provide form builder json to block editor for integrations.
 *
 * @package EightshiftForms\Rest\Routes\Editor
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Editor;

use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Integrations\IntegrationSyncInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\SettingsHelper;
use WP_Query;
use WP_REST_Request;

/**
 * Class IntegrationEditorSyncDirectRoute
 */
class IntegrationEditorSyncDirectRoute extends AbstractBaseRoute
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/' . AbstractBaseRoute::ROUTE_PREFIX_INTEGRATION_EDITOR . '-sync-direct/';

	/**
	 * Instance variable for HubSpot form data.
	 *
	 * @var IntegrationSyncInterface
	 */
	protected $integrationSyncDiff;

	/**
	 * Create a new instance.
	 *
	 * @param IntegrationSyncInterface $integrationSyncDiff Inject IntegrationSyncDiff which holds sync data.
	 */
	public function __construct(IntegrationSyncInterface $integrationSyncDiff)
	{
		$this->integrationSyncDiff = $integrationSyncDiff;
	}

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
	 * Returns allowed methods for this route.
	 *
	 * @return string
	 */
	protected function getMethods(): string
	{
		return static::CREATABLE;
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
		$premission = $this->checkUserPermission();
		if ($premission) {
			return \rest_ensure_response($premission);
		}

		$formId = $request->get_param('id') ?? '';

		$output = [];
		if ($formId === 'all') {
			// Prepare query args.
			$args = [
				'post_type' => Forms::POST_TYPE_SLUG,
				'posts_per_page' => 5000, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
				'post_status' => 'any',
			];

			$theQuery = new WP_Query($args);
			while ($theQuery->have_posts()) {
				$theQuery->the_post();

				$id = \get_the_ID();

				$item = $this->integrationSyncDiff->syncFormDirect((string) $id);

				$output[$item['status']][] = \get_the_title($item['formId']);
			}

			\wp_reset_postdata();
		} else {
			$item = $this->integrationSyncDiff->syncFormDirect($formId);

			$output[$item['status']][] = \get_the_title($item['formId']);
		}

		if (isset($output['error'])) {
			$msgOutput = [
				\__('Not all forms synced with success. Please check manualy all forms with errors.', 'eightshift-forms'),
			];

			if (isset($output['success'])) {
				// translators: %s replaces form name.
				$msgOutput[] = \sprintf(\__('<br/><strong>Success:</strong><br/> %s', 'eightshift-forms'), \implode('<br/>', $output['success']));
			}

			// translators: %s replaces form name.
			$msgOutput[] = \sprintf(\__('<br/><strong>Error:</strong><br/> %s', 'eightshift-forms'), \implode('<br/>', $output['error']));

			return \rest_ensure_response(
				$this->getApiWarningOutput(
					\implode('<br/>', $msgOutput),
					$output
				)
			);
		}

		if (\count($output) === 0) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					\__('There are no forms in your list to sync.', 'eightshift-forms'),
					$output
				)
			);
		}

		return \rest_ensure_response(
			$this->getApiSuccessOutput(
				// translators: %s replaces form count number.
				\sprintf(\_n('%s form synced with success.', '%s forms synced with success.', \count($output['success']), 'eightshift-forms'), \count($output['success'])),
				$output
			)
		);
	}
}
