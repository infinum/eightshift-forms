<?php

/**
 * The class to provide form fields from the form ID. Used in the forms block for conditional tags.
 *
 * @package EightshiftForms\Rest\Routes\Editor
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Editor;

use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Integrations\IntegrationSyncInterface;
use EightshiftForms\Config\Config;
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

/**
 * Class FormFieldsRoute
 */
class FormFieldsRoute extends AbstractSimpleFormSubmit
{
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
	 * @param SecurityInterface $security Inject security methods.
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param IntegrationSyncInterface $integrationSyncDiff Inject IntegrationSyncDiff which holds sync data.
	 */
	public function __construct(
		SecurityInterface $security,
		ValidatorInterface $validator,
		LabelsInterface $labels,
		IntegrationSyncInterface $integrationSyncDiff
	) {
		$this->security = $security;
		$this->validator = $validator;
		$this->labels = $labels;
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
	 * Returns allowed methods for this route.
	 *
	 * @return string
	 */
	protected function getMethods(): string
	{
		return static::READABLE;
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
	 * @param array<string, mixed> $params Params passed from the request.
	 *
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(array $params): array
	{
		return [
			'id' => 'string',
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
		$formDetails = GeneralHelpers::getFormDetails($params['id'] ?? '');

		$fieldsOnly = $formDetails[Config::FD_FIELDS_ONLY] ?? [];

		if (!$fieldsOnly) {
			throw new BadRequestException(
				$this->getLabels()->getLabel('formFieldsMissing'),
				[
					AbstractBaseRoute::R_DEBUG => $formDetails,
					AbstractBaseRoute::R_DEBUG_KEY => 'formFieldsMissing',
				]
			);
		}

		$fieldsOutput = $this->getItems($fieldsOnly);

		$steps = $formDetails[Config::FD_STEPS_SETUP] ?? [];

		return [
			AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel('formFieldsSuccess'),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG => $fieldsOutput,
				AbstractBaseRoute::R_DEBUG_KEY => 'formFieldsSuccess',
			],
			AbstractBaseRoute::R_DATA => [
				UtilsHelper::getStateResponseOutputKey('editorFormFields') => $fieldsOutput,
				UtilsHelper::getStateResponseOutputKey('editorFormFieldsSteps') => $steps ? \array_values($this->getSteps($fieldsOutput, $steps['steps'], false)) : [],
				UtilsHelper::getStateResponseOutputKey('editorFormFieldsStepsFull') => $steps ? \array_values($this->getSteps($fieldsOutput, $steps['steps'], true)) : [],
				UtilsHelper::getStateResponseOutputKey('editorFormFieldsNames') => $formDetails[Config::FD_FIELD_NAMES_FULL],
			],
		];
	}

	/**
	 * Get steps output
	 *
	 * @param array<mixed> $items Fields output.
	 * @param array<mixed> $data Steps output.
	 * @param bool $outputFull Output full steps without excluding steps with fields.
	 *
	 * @return array<mixed>
	 */
	private function getSteps(array $items, array $data, bool $outputFull): array
	{
		$output = [];

		foreach ($data as $step) {
			$value = $step['value'] ?? '';

			if (!$value) {
				continue;
			}

			$subItems = $step['subItems'] ?? [];

			if (!$subItems && !$outputFull) {
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
			$blockName = GeneralHelpers::getBlockNameDetails($value['blockName']);
			$prefix = Helpers::kebabToCamelCase("{$blockName['nameAttr']}-{$blockName['nameAttr']}");

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
			$blockName = GeneralHelpers::getBlockNameDetails($item['blockName']);
			$prefix = Helpers::kebabToCamelCase("{$blockName['nameAttr']}-{$blockName['nameAttr']}");

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
