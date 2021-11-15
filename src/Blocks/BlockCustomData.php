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
		if (!has_filter(Filters::FILTER_BLOCK_CUSTOM_DATA_OPTIONS_DATA_NAME)) {
			return '';
		}

		$customDataData = $attributes['customDataData'] ?? '';

		$data = apply_filters(Filters::FILTER_BLOCK_CUSTOM_DATA_OPTIONS_DATA_NAME, $customDataData);

		if (!$data) {
			return '';
		}

		$customDataFieldType = $attributes['customDataFieldType'] ?? '';

		switch ($customDataFieldType) {
			case 'checkboxes':
				$output = [
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
				];
				break;
			case 'radios':
				$output = [
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
				];
				break;
			default:
				$output = [
					'component' => 'select',
					'selectFieldLabel' => $attributes['customDataSelectFieldLabel'] ?? '',
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
				];
				break;
		}

		return $this->buildComponent($output);
	}
}
