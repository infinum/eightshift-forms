<?php

/**
 * The class to provide form builder json to block editor for integrations.
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\Entries\EntriesHelper;
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Integrations\IntegrationSyncInterface;
use EightshiftForms\Transfer\TransferInterface;
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidatorInterface;
use WP_REST_Request;

/**
 * Class BulkRoute
 */
class BulkRoute extends AbstractSimpleFormSubmit
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
	 * @param SecurityInterface $security Inject security methods.
	 * @param ValidatorInterface $validator Inject validation methods.
	 * @param LabelsInterface $labels Inject labels.
	 * @param IntegrationSyncInterface $integrationSyncDiff Inject IntegrationSyncDiff which holds sync data.
	 * @param TransferInterface $transfer Inject TransferInterface which holds transfer methods.
	 */
	public function __construct(
		SecurityInterface $security,
		ValidatorInterface $validator,
		LabelsInterface $labels,
		IntegrationSyncInterface $integrationSyncDiff,
		TransferInterface $transfer
	) {
		$this->security = $security;
		$this->validator = $validator;
		$this->labels = $labels;
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
	 * Check if the route is admin protected.
	 *
	 * @return boolean
	 */
	protected function isRouteAdminProtected(): bool
	{
		return true;
	}

	/**
	 * Get mandatory params.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(array $params): array
	{
		return [
			'ids' => 'string',
			'type' => 'string',
		];
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $params Prepared params.
	 *
	 * @return array<string, mixed>
	 */
	protected function submitAction(array $params): array
	{
		$ids = isset($params['ids']) ? \json_decode($params['ids'], true) : [];

		if (!$ids) {
			throw new BadRequestException(
				$this->getLabels()->getLabel('bulkMissingItems'),
				[
					AbstractBaseRoute::R_DEBUG_KEY => 'bulkMissingItems',
				]
			);
		}

		$type = $params['type'] ?? '';

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
				return [
					AbstractBaseRoute::R_MSG => $output['msg'] ?? $this->getLabels()->getLabel('genericSuccess'),
					AbstractBaseRoute::R_DEBUG => [
						AbstractBaseRoute::R_DEBUG_KEY => 'bulkSuccess' . \ucfirst($type),
					],
				];
			case 'warning':
				throw new BadRequestException(
					$output['msg'] ?? $this->getLabels()->getLabel('genericWarning'),
					[
						AbstractBaseRoute::R_DEBUG_KEY => 'bulkWarning' . \ucfirst($type),
					]
				);
			default:
				throw new BadRequestException(
					$output['msg'] ?? $this->getLabels()->getLabel('genericError'),
					[
						AbstractBaseRoute::R_DEBUG_KEY => 'bulkError' . \ucfirst($type),
					]
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

			// Prevent non sync forms from syncing like mailer.
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
