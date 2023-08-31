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
		$debug = [
			'request' => $request,
		];

		$premission = $this->checkUserPermission();
		if ($premission) {
			return \rest_ensure_response($premission);
		}

		$formId = $request->get_param('id') ?? '';

		if (!$formId) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					\esc_html__('Form Id was not provided.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$data = Helper::getFormDetailsById($formId);
		$fieldsOnly = $data['fieldsOnly'] ?? [];

		$debug = \array_merge(
			$debug,
			[
				'data' => $data,
			]
		);

		if (!$fieldsOnly) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					\esc_html__('Form has no fields to provide, please check your form is configured correctly.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$fieldsOutput = $this->getItems($fieldsOnly);

		$steps = $data['stepsSetup'] ?? [];

		return \rest_ensure_response(
			$this->getApiSuccessOutput(
				\esc_html__('Success.', 'eightshift-forms'),
				[
					'fields' => \array_values($fieldsOutput),
					'steps' => $steps ? \array_values($this->getSteps($fieldsOutput, $steps['steps'])) : [],
					'names' => $data['fieldNamesFull'],
				],
				$debug
			)
		);
	}

	/**
	 * Get steps output
	 *
	 * @param array<mixed> $items Fields output.
	 * @param array<mixed> $data Steps output.
	 *
	 * @return array<mixed>
	 */
	private function getSteps(array $items, array $data): array
	{
		$output = [];

		foreach ($data as $step) {
			$value = $step['value'] ?? '';

			if (!$value) {
				continue;
			}

			$subItems = $step['subItems'] ?? [];

			if (!$subItems) {
				continue;
			}

			$item = $step;

			$item['subItems'] = \array_values(\array_filter(\array_map(
				static function ($item) use ($items) {
					if (isset($items[$item])) {
						return $items[$item];
					}
				},
				$subItems
			)));

			$output[] = $item;
		}

		return $output;
	}

	/**
	 * Get fields items output.
	 *
	 * @param array<mixed> $items Field data.
	 *
	 * @return array<mixed>
	 */
	private function getItems(array $items): array
	{
		$output = [];

		$ignore = \array_flip([
			'file',
			'step',
			'submit',
		]);

		foreach ($items as $value) {
			$blockName = Helper::getBlockNameDetails($value['blockName']);
			$prefix = Components::kebabToCamelCase("{$blockName['nameAttr']}-{$blockName['nameAttr']}");

			$name = $value['attrs']["{$prefix}Name"] ?? '';

			if (!$name) {
				continue;
			}

			$type = $blockName['name'];

			if (isset($ignore[$type])) {
				continue;
			}

			$label = $value['attrs']["{$prefix}FieldLabel"] ?? '';

			if (!$label) {
				$label = $name;
			}

			$output[$name] = [
				'label' => $label,
				'value' => $name,
				'type' => $type,
				'subItems' => $this->getInnerItems($value['innerBlocks'], $type),
			];
		}

		return $output;
	}

	/**
	 * Get inner items with details
	 *
	 * @param array<mixed> $items Items to find in the block.
	 * @param string $parentType Parent type for the block.
	 *
	 * @return array<mixed>
	 */
	private function getInnerItems(array $items, string $parentType): array
	{
		$output = [];

		if (!$items) {
			return $output;
		}

		$output[] = [
			'label' => $parentType === 'radios' ? \__('Unchecked', 'eightshift-forms') : \__('Unselected', 'eightshift-forms'),
			'value' => '',
		];

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
