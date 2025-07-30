<?php

/**
 * The class register route for transfer endpoint
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\CustomPostType\Result;
use EightshiftForms\Transfer\SettingsTransfer;
use EightshiftForms\Transfer\Transfer;
use EightshiftForms\Transfer\TransferInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;
use EightshiftForms\Security\SecurityInterface;

/**
 * Class TransferRoute
 */
class TransferRoute extends AbstractSimpleFormSubmit
{
	/**
	 * Instance variable of TransferInterface data.
	 *
	 * @var TransferInterface
	 */
	protected $transfer;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param SecurityInterface $security Inject security methods.
	 * @param ValidatorInterface $validator Inject validation methods.
	 * @param LabelsInterface $labels Inject labels.
	 * @param TransferInterface $transfer Inject TransferInterface which holds transfer methods.
	 */
	public function __construct(
		SecurityInterface $security,
		ValidatorInterface $validator,
		LabelsInterface $labels,
		TransferInterface $transfer
	) {
		$this->security = $security;
		$this->validator = $validator;
		$this->labels = $labels;
		$this->transfer = $transfer;
	}

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'transfer';

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
		$type = $params['type'] ?? '';

		$output = [
			Transfer::TYPE_GLOBAL_SETTINGS => [],
			Transfer::TYPE_FORMS => [],
			Transfer::TYPE_RESULT_OUTPUTS => [],
		];

		switch ($type) {
			case SettingsTransfer::TYPE_EXPORT_GLOBAL_SETTINGS:
				$output[Transfer::TYPE_GLOBAL_SETTINGS] = $this->transfer->getExportGlobalSettings();
				$internalType = 'export';
				break;
			case SettingsTransfer::TYPE_EXPORT_FORMS:
				$items = $params['items'] ?? [];

				if (!$items) {
					throw new BadRequestException(
						$this->getLabels()->getLabel('transferExportMissingForms'),
						[
							AbstractBaseRoute::R_DEBUG_KEY => 'transferExportMissingForms',
						]
					);
				}

				$items = \explode(',', $items);

				$output[Transfer::TYPE_FORMS] = $this->transfer->getExportCpts($items);
				$internalType = 'export';
				break;
			case SettingsTransfer::TYPE_EXPORT_RESULT_OUTPUTS:
				$items = $params['items'] ?? [];

				if (!$items) {
					throw new BadRequestException(
						$this->getLabels()->getLabel('transferExportMissingResultOutputs'),
						[
							AbstractBaseRoute::R_DEBUG_KEY => 'transferExportMissingResultOutputs',
						]
					);
				}

				$items = \explode(',', $items);

				$output[Transfer::TYPE_RESULT_OUTPUTS] = $this->transfer->getExportCpts($items, Result::POST_TYPE_SLUG);
				$internalType = 'export';
				break;
			case SettingsTransfer::TYPE_EXPORT_ALL:
				$output[Transfer::TYPE_GLOBAL_SETTINGS] = $this->transfer->getExportGlobalSettings();
				$output[Transfer::TYPE_FORMS] = $this->transfer->getExportCpts();
				$output[Transfer::TYPE_RESULT_OUTPUTS] = $this->transfer->getExportCpts([], Result::POST_TYPE_SLUG);
				$internalType = 'export';
				break;
			case SettingsTransfer::TYPE_IMPORT:
				$upload = $params['upload'] ?? '';

				if (!$upload) {
					throw new BadRequestException(
						$this->getLabels()->getLabel('transferUploadMissingFile'),
						[
							AbstractBaseRoute::R_DEBUG_KEY => 'transferUploadMissingFile',
						]
					);
				}

				$uploadStatus = $this->transfer->getImport(
					$upload,
					isset($params['override']) ? \filter_var($params['override'], \FILTER_VALIDATE_BOOLEAN) : false
				);

				if (!$uploadStatus) {
					throw new BadRequestException(
						$this->getLabels()->getLabel('transferUploadError'),
						[
							AbstractBaseRoute::R_DEBUG_KEY => 'transferUploadError',
						]
					);
				}

				$internalType = 'import';
				break;
			default:
				throw new BadRequestException(
					$this->getLabels()->getLabel('transferUploadMissingType'),
					[
						AbstractBaseRoute::R_DEBUG_KEY => 'transferUploadMissingType',
					]
				);
		}

		$date = \current_datetime()->format('Y-m-d-H-i-s-u');


		return [
			AbstractBaseRoute::R_MSG => \sprintf(\esc_html__('%s %s', 'eightshift-forms'), \ucfirst($internalType), $this->getLabels()->getLabel('transferSuccess')),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG_KEY => 'transferSuccess',
			],
			AbstractBaseRoute::R_DATA => [
				UtilsHelper::getStateResponseOutputKey('adminTransferName') => "eightshift-forms-{$type}-{$date}",
				UtilsHelper::getStateResponseOutputKey('adminTransferContent') => \wp_json_encode($output),
			],
		];
	}
}
