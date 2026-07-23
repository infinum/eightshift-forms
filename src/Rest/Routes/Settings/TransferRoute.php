<?php

/**
 * The class register route for transfer endpoint
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\CustomPostType\Result;
use EightshiftForms\Labels\Labels;
use EightshiftForms\Transfer\SettingsTransfer;
use EightshiftForms\Transfer\Transfer;
use EightshiftForms\Transfer\TransferInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;
use EightshiftForms\Security\SecurityInterface;

/**
 * Class TransferRoute
 */
class TransferRoute extends AbstractSimpleFormSubmit
{
	/**
	 * Create a new instance that injects classes
	 *
	 * @param SecurityInterface $security Inject security methods.
	 * @param ValidatorInterface $validator Inject validation methods.
	 * @param TransferInterface $transfer Inject TransferInterface which holds transfer methods.
	 */
	public function __construct(
		SecurityInterface $security,
		ValidatorInterface $validator,
		protected TransferInterface $transfer
	) {
		$this->security = $security;
		$this->validator = $validator;
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
	 */
	protected function isRouteAdminProtected(): bool
	{
		return true;
	}

	/**
	 * Get mandatory params.
	 *
	 * @param array<string, mixed> $params Params passed from the request.
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
	 * @throws BadRequestException If transfer type is not found.
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
					// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
					throw new BadRequestException(
						Labels::getLabel(Labels::LABEL_TRANSFER_EXPORT_MISSING_FORMS),
						[
							AbstractBaseRoute::R_DEBUG_KEY => Labels::LABEL_TRANSFER_EXPORT_MISSING_FORMS,
						]
					);
					// phpcs:enable
				}

				$items = \explode(',', $items);

				$output[Transfer::TYPE_FORMS] = $this->transfer->getExportCpts($items);
				$internalType = 'export';
				break;
			case SettingsTransfer::TYPE_EXPORT_RESULT_OUTPUTS:
				$items = $params['items'] ?? [];

				if (!$items) {
					// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
					throw new BadRequestException(
						Labels::getLabel(Labels::LABEL_TRANSFER_EXPORT_MISSING_RESULT_OUTPUTS),
						[
							AbstractBaseRoute::R_DEBUG_KEY => Labels::LABEL_TRANSFER_EXPORT_MISSING_RESULT_OUTPUTS,
						]
					);
					// phpcs:enable
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
					// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
					throw new BadRequestException(
						Labels::getLabel(Labels::LABEL_TRANSFER_UPLOAD_MISSING_FILE),
						[
							AbstractBaseRoute::R_DEBUG_KEY => Labels::LABEL_TRANSFER_UPLOAD_MISSING_FILE,
						]
					);
					// phpcs:enable
				}

				$uploadStatus = $this->transfer->getImport(
					$upload,
					isset($params['override']) && \filter_var($params['override'], \FILTER_VALIDATE_BOOLEAN)
				);

				if (!$uploadStatus) {
					// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
					throw new BadRequestException(
						Labels::getLabel(Labels::LABEL_TRANSFER_UPLOAD_ERROR),
						[
							AbstractBaseRoute::R_DEBUG_KEY => Labels::LABEL_TRANSFER_UPLOAD_ERROR,
						]
					);
					// phpcs:enable
				}

				$internalType = 'import';
				break;
			default:
				// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
				throw new BadRequestException(
					Labels::getLabel(Labels::LABEL_TRANSFER_UPLOAD_MISSING_TYPE),
					[
						AbstractBaseRoute::R_DEBUG_KEY => Labels::LABEL_TRANSFER_UPLOAD_MISSING_TYPE,
					]
				);
				// phpcs:enable
		}

		$date = \current_datetime()->format('Y-m-d-H-i-s-u');


		return [
			// translators: %1$s will be replaced with the transfer type. %2$s will be replaced with the transfer success text.
			AbstractBaseRoute::R_MSG => \sprintf(\esc_html__('%1$s %2$s', 'eightshift-forms'), \ucfirst($internalType), Labels::getLabel(Labels::LABEL_TRANSFER_SUCCESS)),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG_KEY => Labels::LABEL_TRANSFER_SUCCESS,
			],
			AbstractBaseRoute::R_DATA => [
				UtilsHelper::getStateResponseOutputKey('adminTransferName') => "eightshift-forms-{$type}-{$date}",
				UtilsHelper::getStateResponseOutputKey('adminTransferContent') => \wp_json_encode($output),
			],
		];
	}
}
