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
		$useMultiflow = $request->get_param('useMultiflow') ? \filter_var($request->get_param('useMultiflow'), \FILTER_VALIDATE_BOOLEAN) : false;

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

		$outputFields = [];
		$outputSteps = [];

		foreach ($data['fieldsOnly'] as $value) {
			$blockName = Helper::getBlockNameDetails($value['blockName']);
			$prefix = Components::kebabToCamelCase("{$blockName['nameAttr']}-{$blockName['nameAttr']}");

			$name = $value['attrs']["{$prefix}Name"] ?? '';

			if (!$name) {
				continue;
			}

			$type = $blockName['name'];

			if ($type === 'submit' || $type === 'file') {
				continue;
			}

			if ($type === 'step') {
				$outputSteps[] = [
					'label' => $name,
					'value' => $name,
					'type' => $type,
					'subItems' => [],
				];
			} else {
				$label = $value['attrs']["{$prefix}FieldLabel"] ?? '';

				if (!$label) {
					$label = $name;
				}

				$outputFields[] = [
					'label' => $label,
					'value' => $name,
					'type' => $type,
					'subItems' => $this->getInnerItems($value['innerBlocks'], $type, $name),
				];
			}
		}

		return \rest_ensure_response(
			$this->getApiSuccessOutput(
				\esc_html__('Success.', 'eightshift-forms'),
				[
					'fields' => $outputFields,
					'steps' => $outputSteps,
				]
			)
		);
	}

	private function getInnerItems(array $items, string $parentType, string $parentName, bool $useEmpty = true): array
	{
		$output = [];

		if (!$items) {
			return $output;
		}

		if ($useEmpty) {
			$output[] = [
				'label' => $parentType === 'radios' ? \__('Unchecked', 'eightshift-forms') : \__('Unselected', 'eightshift-forms'),
				'value' => '',
			];
		}

		foreach ($items as $item) {
			$blockName = Helper::getBlockNameDetails($item['blockName']);
			$prefix = Components::kebabToCamelCase("{$blockName['nameAttr']}-{$blockName['nameAttr']}");

			$innerKeyValue =  $item['attrs']["{$prefix}Value"] ?? '';

			if (!$innerKeyValue) {
				continue;
			}

			$innerLabel = $item['attrs']["{$prefix}Label"] ?? '';

			if (!$innerLabel) {
				$innerLabel = $innerKeyValue;
			}

			$output[] = [
				'label' => $innerLabel,
				'value' => "{$innerKeyValue}",
			];
		}

		return $output;
	}
}
