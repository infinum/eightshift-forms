<?php

/**
 * BlockQuery integration class.
 *
 * @package EightshiftForms\Blocks
 */

declare(strict_types=1);

namespace EightshiftForms\Blocks;

use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Hooks\Filters;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * BlockQuery integration class.
 */
class BlockQuery extends AbstractFormBuilder implements ServiceInterface
{
	/**
	 * Filter return query component from filter.
	 *
	 * @var string
	 */
	public const FILTER_BLOCK_QUERY_COMPONENT_NAME = 'es_forms_block_query_component';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		// Blocks string to value filter name constant.
		\add_filter(static::FILTER_BLOCK_QUERY_COMPONENT_NAME, [$this, 'getQueryComponent'], 50);
	}

	/**
	 * Build block from query data.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 *
	 * @return string
	 */
	public function getQueryComponent(array $attributes): string
	{
		if (!has_filter(Filters::FILTER_BLOCK_QUERY_OPTIONS_DATA_NAME)) {
			return '';
		}

		$queryData = $attributes['queryData'] ?? '';

		$data = apply_filters(Filters::FILTER_BLOCK_QUERY_OPTIONS_DATA_NAME, $queryData);

		if (!$data) {
			return '';
		}

		$queryFieldType = $attributes['queryFieldType'] ?? '';

		switch ($queryFieldType) {
			case 'checkboxes':
				$output = [
					'component' => 'checkboxes',
					'checkboxesFieldLabel' => $attributes['queryCheckboxesFieldLabel'] ?? '',
					'checkboxesId' => $attributes['queryId'] ?? '',
					'checkboxesName' => $attributes['queryCheckboxesName'] ?? '',
					'checkboxesContent' => array_map(
						function ($option) {
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
					'radiosFieldLabel' => $attributes['queryRadiosFieldLabel'] ?? '',
					'radiosId' => $attributes['queryId'] ?? '',
					'radiosName' => $attributes['queryRadiosName'] ?? '',
					'radiosContent' => array_map(
						function ($option) {
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
					'selectFieldLabel' => $attributes['querySelectFieldLabel'] ?? '',
					'selectId' => $attributes['queryId'] ?? '',
					'selectName' => $attributes['querySelectName'] ?? '',
					'selectOptions' => array_map(
						function ($option) {
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
