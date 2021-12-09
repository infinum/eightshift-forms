<?php

/**
 * BlockCustomData integration class.
 *
 * @package EightshiftForms\Blocks
 */

declare(strict_types=1);

namespace EightshiftForms\Blocks;

use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Hooks\Filters;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * BlockCustomData integration class.
 */
class BlockCustomData extends AbstractFormBuilder implements ServiceInterface
{
	/**
	 * Filter return custom data component from filter.
	 *
	 * @var string
	 */
	public const FILTER_BLOCK_CUSTOM_DATA_COMPONENT_NAME = 'es_forms_block_custom_data_component';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		// Blocks string to value filter name constant.
		\add_filter(static::FILTER_BLOCK_CUSTOM_DATA_COMPONENT_NAME, [$this, 'getCustomDataComponent'], 50);
	}

	/**
	 * Build block from Custom data.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 *
	 * @return string
	 */
	public function getCustomDataComponent(array $attributes): string
	{
		$filterName = Filters::getBlockFilterName('customData', 'data');
		if (!has_filter($filterName)) {
			return '';
		}

		$customDataData = $attributes['customDataData'] ?? '';

		$data = apply_filters($filterName, $customDataData);

		if (!$data) {
			return '';
		}

		$customDataFieldType = $attributes['customDataFieldType'] ?? '';

		$ssr = $attributes['customDataServerSideRender'] ?? false;

		switch ($customDataFieldType) {
			case 'checkboxes':
				$output = array_merge(
					[
						'component' => 'checkboxes',
						'checkboxesFieldLabel' => $attributes['customDataCheckboxesFieldLabel'] ?? '',
						'checkboxesId' => $attributes['customDataId'] ?? '',
						'checkboxesName' => $attributes['customDataCheckboxesName'] ?? '',
						'checkboxesContent' => array_map(
							static function ($option) {
								return [
									'component' => 'checkbox',
									'checkboxLabel' => $option['label'] ?? '',
									'checkboxValue' => $option['value'] ?? $option['label'] ?? '',
									'checkboxIsChecked' => $option['selected'] ?? false,
								];
							},
							$data
						),
					],
					$this->getFieldAttributes('customDataCheckboxesField', $attributes)
				);

				if ($ssr) {
					$output['checkboxesFieldUniqueId'] = $attributes['customDataUniqueId'] ?? '';
				}
				break;
			case 'radios':
				$output = array_merge(
					[
						'component' => 'radios',
						'radiosFieldLabel' => $attributes['customDataRadiosFieldLabel'] ?? '',
						'radiosId' => $attributes['customDataId'] ?? '',
						'radiosName' => $attributes['customDataRadiosName'] ?? '',
						'radiosContent' => array_map(
							static function ($option) {
								return [
									'component' => 'radio',
									'radioLabel' => $option['label'] ?? '',
									'radioValue' => $option['value'] ?? $option['label'] ?? '',
									'radioIsChecked' => $option['selected'] ?? false,
								];
							},
							$data
						),
					],
					$this->getFieldAttributes('customDataRadiosField', $attributes)
				);

				if ($ssr) {
					$output['radiosFieldUniqueId'] = $attributes['customDataUniqueId'] ?? '';
				}
				break;
			default:
				$output = array_merge(
					[
						'component' => 'select',
						'selectId' => $attributes['customDataId'] ?? '',
						'selectName' => $attributes['customDataSelectName'] ?? '',
						'selectOptions' => array_map(
							static function ($option) {
								return [
									'component' => 'select-option',
									'selectOptionLabel' => $option['label'] ?? '',
									'selectOptionValue' => $option['value'] ?? $option['label'] ?? '',
									'selectOptionIsSelected' => $option['selected'] ?? false,
								];
							},
							$data
						),
					],
					$this->getFieldAttributes('customDataSelectField', $attributes)
				);

				if ($ssr) {
					$output['selectFieldUniqueId'] = $attributes['customDataUniqueId'] ?? '';
				}
				break;
		}

		return $this->buildComponent($output);
	}

	/**
	 * Get field attributes.
	 *
	 * @param string $key Key to check.
	 * @param array<string, mixed> $attributes Block attributes.
	 *
	 * @return array<string, mixed>
	 */
	private function getFieldAttributes(string $key, array $attributes): array
	{
		$fields = array_filter(
			$attributes,
			static function ($item) use ($key) {
				if (strpos($item, $key) === 0) {
					return true;
				}
			},
			ARRAY_FILTER_USE_KEY
		);

		if (!$fields) {
			return [];
		}

		$output = [];

		foreach ($fields as $fieldKey => $fieldValue) {
			$fieldKey = lcfirst(str_replace('customData', '', $fieldKey));

			$output[$fieldKey] = $fieldValue;
		}

		return $output;
	}
}
