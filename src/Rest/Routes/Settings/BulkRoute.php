<?php

/**
 * The class to provide form builder json to block editor for integrations.
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\Config\Config;
use EightshiftForms\Entries\EntriesHelper;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Integrations\IntegrationSyncInterface;
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Transfer\TransferInterface;
use WP_REST_Request;

/**
 * Class BulkRoute
 */
class BulkRoute extends AbstractBaseRoute
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'bulk';

	/**
	 * Instance variable for HubSpot form data.
	 *
	 * @var IntegrationSyncInterface
	 */
	protected $integrationSyncDiff;

	/**
	 * Instance variable of TransferInterface data.
	 *
	 * @var TransferInterface
	 */
	protected $transfer;

	/**
	 * Create a new instance.
	 *
	 * @param IntegrationSyncInterface $integrationSyncDiff Inject IntegrationSyncDiff which holds sync data.
	 * @param TransferInterface $transfer Inject TransferInterface which holds transfer methods.
	 */
	public function __construct(
		IntegrationSyncInterface $integrationSyncDiff,
		TransferInterface $transfer
	) {
		$this->integrationSyncDiff = $integrationSyncDiff;
		$this->transfer = $transfer;
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
		$permission = $this->checkUserPermission(Config::CAP_SETTINGS);
		if ($permission) {
			return \rest_ensure_response($permission);
		}

		$debug = [
			'request' => $request,
		];

		$params = $this->prepareSimpleApiParams($request, $this->getMethods());

		$ids = isset($params['ids']) ? \json_decode($params['ids'], true) : [];

		if (!$ids) {
			return \rest_ensure_response(
				ApiHelpers::getApiErrorPublicOutput(
					\__('There are no selected forms.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$type = $params['type'] ?? '';
		if (!$type) {
			return \rest_ensure_response(
				ApiHelpers::getApiErrorPublicOutput(
					\__('Action type is missing.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$output = [];

		switch ($type) {
			case 'sync':
				$output = $this->sync($ids);
				break;
			case 'delete':
				$output = $this->delete($ids);
				break;
			case 'restore':
				$output = $this->restore($ids);
				break;
			case 'delete-permanently':
				$output = $this->deletePermanently($ids);
				break;
			case 'duplicate':
				$output = $this->duplicate($ids);
				break;
			case 'duplicate-entry':
				$output = $this->duplicateEntry($ids);
				break;
			case 'delete-entry':
				$output = $this->deleteEntry($ids);
				break;
		}

		switch ($output['status']) {
			case 'success':
				return \rest_ensure_response(
					ApiHelpers::getApiSuccessPublicOutput(
						$output['msg'] ?? \esc_html__('Success', 'eightshift-forms'),
						$output['data'] ?? [],
						$debug
					)
				);
			case 'warning':
				return \rest_ensure_response(
					ApiHelpers::getApiWarningPublicOutput(
						$output['msg'] ?? \esc_html__('Warning', 'eightshift-forms'),
						$output['data'] ?? [],
						$debug
					)
				);
			default:
				return \rest_ensure_response(
					ApiHelpers::getApiErrorPublicOutput(
						$output['msg'] ?? \esc_html__('Error', 'eightshift-forms'),
						$output['data'] ?? [],
						$debug
					)
				);
		}
	}

	/**
	 * Output message based on the output data.
	 *
	 * @param array<string, mixed> $details Output data.
	 * @param string $type Type of action.
	 *
	 * @return array<string, mixed>
	 */
	private function output(array $details, string $type): array
	{
		$error = $details['error'] ?? [];
		$success = $details['success'] ?? [];
		$skip = $details['skip'] ?? [];

		$msg = '';
		$internalType = 'forms';

		switch ($type) {
			case 'sync':
				$msg = \esc_html__('synced', 'eightshift-forms');
				$internalType = 'forms';
				break;
			case 'delete':
				$msg = \esc_html__('deleted', 'eightshift-forms');
				$internalType = 'forms';
				break;
			case 'restore':
				$msg = \esc_html__('restored', 'eightshift-forms');
				$internalType = 'forms';
				break;
			case 'delete-permanently':
				$msg = \esc_html__('deleted permanently', 'eightshift-forms');
				$internalType = 'forms';
				break;
			case 'duplicate':
				$msg = \esc_html__('duplicate', 'eightshift-forms');
				$internalType = 'forms';
				break;
			case 'delete-entry':
				$msg = \esc_html__('deleted', 'eightshift-forms');
				$internalType = 'entries';
				break;
			case 'duplicate-entry':
				$msg = \esc_html__('duplicate', 'eightshift-forms');
				$internalType = 'entries';
				break;
		}

		if (!$details) {
			return [
				'status' => 'error',
				// translators: %s replaces form msg type.
				'msg' => \sprintf(\esc_html__('There are no %1$s in your list to %2$s.', 'eightshift-forms'), $internalType, $msg),
			];
		}

		if (\count($details) > 1) {
			$msgOutput = [
				// translators: %s replaces type.
				\sprintf(\esc_html__('Not all items were %s with success. Please check the following log.', 'eightshift-forms'), $internalType),
			];

			if ($error) {
				// translators: %s replaces error list.
				$msgOutput[] = \sprintf(\wp_kses_post(\__('<br/><strong>Error:</strong><br/> %s', 'eightshift-forms')), \implode('<br/>', $error));
			}

			if ($success) {
				// translators: %s replaces success list.
				$msgOutput[] = \sprintf(\wp_kses_post(\__('<br/><strong>Success:</strong><br/> %s', 'eightshift-forms')), \implode('<br/>', $success));
			}

			if ($skip) {
				// translators: %s replaces skip list.
				$msgOutput[] = \sprintf(\wp_kses_post(\__('<br/><strong>Skip:</strong><br/> %s', 'eightshift-forms')), \implode('<br/>', $skip));
			}

			return [
				'status' => 'warning',
				'msg' => \implode('<br/>', $msgOutput),
			];
		}

		if ($success) {
			return [
				'status' => 'success',
				// translators: %s replaces form msg type.
				'msg' => \sprintf(\esc_html__('Success, all selected items were %s.', 'eightshift-forms'), $msg),
			];
		}

		if ($skip) {
			return [
				'status' => 'warning',
				'msg' => \esc_html__('Warning, all selected items were skipped.', 'eightshift-forms'),
			];
		}

		return [
			'status' => 'error',
			'msg' => \esc_html__('There was an error on all selected items.', 'eightshift-forms'),
		];
	}

	/**
	 * Sync forms but Ids.
	 *
	 * @param array<int> $ids Form Ids.
	 *
	 * @return array<int>
	 */
	private function sync(array $ids): array
	{
		$output = [];

		foreach ($ids as $id) {
			$title = \get_the_title($id);

			if (!$title) {
				// translators: %s replaces form id.
				$title = \sprintf(\esc_html__('Form %s', 'eightshift-forms'), $id);
			}

			// Prevent non-sync forms from syncing like mailer.
			if (!GeneralHelpers::canIntegrationUseSync(GeneralHelpers::getFormTypeById((string) $id))) {
				$output['skip'][] = $title;
				continue;
			}

			$item = $this->integrationSyncDiff->syncFormDirect((string) $id);

			$output[$item['status']][] = $title;
		}

		return $this->output($output, 'sync');
	}

	/**
	 * Delete forms by Ids.
	 *
	 * @param array<int> $ids Form Ids.
	 *
	 * @return array<int>
	 */
	private function delete(array $ids): array
	{
		$output = [];

		foreach ($ids as $id) {
			$title = \get_the_title($id);

			if (!$title) {
				// translators: %s replaces form id.
				$title = \sprintf(\esc_html__('Item %s', 'eightshift-forms'), $id);
			}

			$action = \wp_trash_post((int) $id);

			if ($action) {
				$output['success'][] = $title;
			} else {
				$output['error'][] = $title;
			}
		}

		return $this->output($output, 'delete');
	}

	/**
	 * Delete permanently forms by Ids.
	 *
	 * @param array<int> $ids Form Ids.
	 *
	 * @return array<int>
	 */
	private function deletePermanently(array $ids): array
	{
		$output = [];

		foreach ($ids as $id) {
			$title = \get_the_title($id);

			if (!$title) {
				// translators: %s replaces form id.
				$title = \sprintf(\esc_html__('Item %s', 'eightshift-forms'), $id);
			}

			$action = \wp_delete_post((int) $id, true);

			if ($action) {
				$output['success'][] = $title;
			} else {
				$output['error'][] = $title;
			}
		}

		return $this->output($output, 'delete-permanently');
	}

	/**
	 * Delete entry by Ids.
	 *
	 * @param array<int> $ids Form Ids.
	 *
	 * @return array<int>
	 */
	private function deleteEntry(array $ids): array
	{
		$output = [];

		foreach ($ids as $id) {
			$title = \get_the_title($id);

			if (!$title) {
				// translators: %s replaces form id.
				$title = \sprintf(\esc_html__('Entry %s', 'eightshift-forms'), $id);
			}

			$action = EntriesHelper::deleteEntry((string) $id);

			if ($action) {
				$output['success'][] = $title;
			} else {
				$output['error'][] = $title;
			}
		}

		return $this->output($output, 'delete-entry');
	}

	/**
	 * Restore forms by Ids.
	 *
	 * @param array<int> $ids Form Ids.
	 *
	 * @return array<int>
	 */
	private function restore(array $ids): array
	{
		$output = [];

		foreach ($ids as $id) {
			$title = \get_the_title($id);

			if (!$title) {
				// translators: %s replaces form id.
				$title = \sprintf(\esc_html__('Item %s', 'eightshift-forms'), $id);
			}

			$action = \wp_update_post([
				'ID' => (int) $id,
				'post_status' => 'draft',
			]);

			if ($action) {
				$output['success'][] = $title;
			} else {
				$output['error'][] = $title;
			}
		}

		return $this->output($output, 'restore');
	}

	/**
	 * Duplicate forms by Ids.
	 *
	 * @param array<int> $ids Form Ids.
	 *
	 * @return array<int>
	 */
	private function duplicate(array $ids): array
	{
		$output = [];

		foreach ($ids as $id) {
			$title = \get_the_title($id);
			$type = \get_post_type($id);

			if (!$title) {
				// translators: %s replaces form id.
				$title = \sprintf(\esc_html__('Item %s', 'eightshift-forms'), $id);
			}

			$export = $this->transfer->getExportCpt((string) $id, $type);

			$action  = $this->transfer->getImportByFormArray($export, false);

			if ($action) {
				$output['success'][] = $title;
			} else {
				$output['error'][] = $title;
			}
		}

		return $this->output($output, 'duplicate');
	}

	/**
	 * Duplicate entry by Ids.
	 *
	 * @param array<int> $ids Entry Ids.
	 *
	 * @return array<int>
	 */
	private function duplicateEntry(array $ids): array
	{
		$output = [];

		foreach ($ids as $id) {
			$title = \get_the_title($id);

			if (!$title) {
				// translators: %s replaces form id.
				$title = \sprintf(\esc_html__('Entry %s', 'eightshift-forms'), $id);
			}

			$entry = EntriesHelper::getEntry((string) $id);

			$action  = EntriesHelper::setEntry($entry['entryValue'] ?? [], $entry['formId'] ?? '');

			if ($action) {
				$output['success'][] = $title;
			} else {
				$output['error'][] = $title;
			}
		}

		return $this->output($output, 'duplicate-entry');
	}
}
