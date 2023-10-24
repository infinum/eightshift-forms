<?php

/**
 * The class register route for transfer endpoint
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Transfer\SettingsTransfer;
use EightshiftForms\Transfer\Transfer;
use EightshiftForms\Transfer\TransferInterface;
use EightshiftForms\Validation\ValidatorInterface;
use WP_REST_Request;

/**
 * Class TransferRoute
 */
class TransferRoute extends AbstractBaseRoute
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
	 * Instance variable of TransferInterface data.
	 *
	 * @var TransferInterface
	 */
	protected $transfer;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 * @param TransferInterface $transfer Inject TransferInterface which holds transfer methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		TransferInterface $transfer
	) {
		$this->validator = $validator;
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
		$premission = $this->checkUserPermission();
		if ($premission) {
			return \rest_ensure_response($premission);
		}

		$debug = [
			'request' => $request,
		];

		$params = $this->prepareSimpleApiParams($request, $this->getMethods());

		$type = $params['type'] ?? '';

		$output = [
			Transfer::TYPE_GLOBAL_SETTINGS => [],
			Transfer::TYPE_FORMS => [],
		];

		switch ($type) {
			case SettingsTransfer::TYPE_EXPORT_GLOBAL_SETTINGS:
				$output[Transfer::TYPE_GLOBAL_SETTINGS] = $this->transfer->getExportGlobalSettings();
				$internalType = 'export';
				break;
			case SettingsTransfer::TYPE_EXPORT_FORMS:
				$items = $params['items'] ?? [];

				if (!$items) {
					return \rest_ensure_response(
						$this->getApiErrorOutput(
							\esc_html__('Please click on the forms you want to export.', 'eightshift-forms'),
							[],
							$debug
						)
					);
				}

				$items = \explode(',', $items);

				$output[Transfer::TYPE_FORMS] = $this->transfer->getExportForms($items);
				$internalType = 'export';
				break;
			case SettingsTransfer::TYPE_EXPORT_ALL:
				$output[Transfer::TYPE_GLOBAL_SETTINGS] = $this->transfer->getExportGlobalSettings();
				$output[Transfer::TYPE_FORMS] = $this->transfer->getExportForms();
				$internalType = 'export';
				break;
			case SettingsTransfer::TYPE_IMPORT:
				$upload = $params['upload'] ?? '';

				if (!$upload) {
					return \rest_ensure_response(
						$this->getApiErrorOutput(
							\esc_html__('Please use the upload field to provide the .json file for the upload.', 'eightshift-forms'),
							[],
							$debug
						)
					);
				}

				$uploadStatus = $this->transfer->getImport(
					$upload,
					isset($params['override']) ? \filter_var($params['override'], \FILTER_VALIDATE_BOOLEAN) : false
				);

				if (!$uploadStatus) {
					return \rest_ensure_response(
						$this->getApiErrorOutput(
							\esc_html__('There was an issue with your upload file. Please make sure you use forms export file and try again.', 'eightshift-forms'),
							[],
							$debug
						)
					);
				}

				$internalType = 'import';
				break;
			default:
				return \rest_ensure_response(
					$this->getApiErrorOutput(
						\esc_html__('Transfer version type key was not provided.', 'eightshift-forms'),
						[],
						$debug
					)
				);
		}

		$date = \current_datetime()->format('Y-m-d-H-i-s-u');

		// Finish.
		return \rest_ensure_response(
			$this->getApiSuccessOutput(
				// translators: %s will be replaced with the transfer internal type.
				\sprintf(\esc_html__('%s successfully done!', 'eightshift-forms'), \ucfirst($internalType)),
				[
					'name' => "eightshift-forms-{$type}-{$date}",
					'content' => \wp_json_encode($output),
				],
				$debug
			)
		);
	}
}
