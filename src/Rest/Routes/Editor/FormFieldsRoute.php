<?php

/**
 * The class to provide form fields from the form ID. Used in the forms block for conditional tags.
 *
 * @package EightshiftForms\Rest\Routes\Editor
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Editor;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Integrations\IntegrationSyncInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use WP_REST_Request;

/**
 * Class FormFieldsRoute
 */
class FormFieldsRoute extends AbstractBaseRoute
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/form-fields/';

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
		$premission = $this->checkUserPermission();
		if ($premission) {
			return \rest_ensure_response($premission);
		}

		$formId = $request->get_param('id') ?? '';

		if (!$formId) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					\esc_html__('Form Id was not provided.', 'eightshift-forms'),
				)
			);
		}

		$data = Helper::getFormDetailsById($formId);

		if (!$data['fieldsOnly']) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					\esc_html__('Form has no fields to provide, please check your form is configured correctly.', 'eightshift-forms'),
				)
			);
		}

		$output = [];

		foreach ($data['fieldsOnly'] as $value) {
			$blockName = Helper::getBlockNameDetails($value['blockName']);
			$prefix = Components::kebabToCamelCase("{$blockName['nameAttr']}-{$blockName['nameAttr']}");

			$name = $value['attrs']["{$prefix}Name"] ?? '';

			if (!$name) {
				continue;
			}

			if ($blockName['name'] === 'submit') {
				continue;
			}

			$label = $value['attrs']["{$prefix}FieldLabel"] ?? '';

			if (!$label) {
				$label = $name;
			}

			$outputItem = [
				'label' => $label,
				'value' => $name,
				'type' => $blockName['name'],
				'subItems' => [],
			];

			if ($value['innerBlocks']) {
				$outputItem['subItems'][] = [
					'label' => \__('Empty', 'eightshift-forms'),
					'value' => '',
				];

				foreach ($value['innerBlocks'] as $valueInner) {
					$blockNameInner = Helper::getBlockNameDetails($valueInner['blockName']);
					$prefixInner = Components::kebabToCamelCase("{$blockNameInner['nameAttr']}-{$blockNameInner['nameAttr']}");

					$innerKeyValue =  $valueInner['attrs']["{$prefixInner}Value"] ?? '';

					if (!$innerKeyValue) {
						continue;
					}

					$innerLabel = $valueInner['attrs']["{$prefixInner}Label"] ?? '';

					if (!$innerLabel) {
						$innerLabel = $innerKeyValue;
					}

					$outputItem['subItems'][] = [
						'label' => $innerLabel,
						'value' => $innerKeyValue,
					];
				}
			}

			$output[] = $outputItem;
		}

		return \rest_ensure_response(
			$this->getApiSuccessOutput(
				\esc_html__('Success.', 'eightshift-forms'),
				$output
			)
		);
	}
}
