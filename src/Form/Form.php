<?php

/**
 * Class that holds all filter used in the component and blocks regarding form.
 *
 * @package EightshiftForms\Form
 */

declare(strict_types=1);

namespace EightshiftForms\Form;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Blocks\SettingsBlocks;
use EightshiftForms\General\SettingsGeneral;
use EightshiftForms\Settings\Settings\SettingsSettings;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftForms\Hooks\FiltersOuputMock;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Form class.
 */
class Form extends AbstractFormBuilder implements ServiceInterface
{
	/**
	 * Filter form component atributes modifications key.
	 */
	public const FILTER_FORM_COMPONENT_ATTRIBUTES_MODIFICATIONS = 'es_forms_form_settings_options';

	/**
	 * Filter forms block modifications key.
	 */
	public const FILTER_FORMS_BLOCK_MODIFICATIONS = 'es_forms_forms_block_modifications';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_FORM_COMPONENT_ATTRIBUTES_MODIFICATIONS, [$this, 'updateFormComponentAttributesOutput']);
		\add_filter(self::FILTER_FORMS_BLOCK_MODIFICATIONS, [$this, 'updateFormsBlockOutput'], 10, 2);
	}

	/**
	 * Modify form component original attributes before final output in form.
	 *
	 * @param array<string, mixed> $attributes Attributes to update.
	 *
	 * @return array<string, mixed>
	 */
	public function updateFormComponentAttributesOutput(array $attributes): array
	{
		$prefix = $attributes['prefix'] ?? '';
		$type = $attributes['blockName'] ?? '';
		$formId = $attributes["{$prefix}PostId"] ?? '';

		if (!$prefix || !$type || !$formId) {
			return $attributes;
		}

		// Change form type depending if it is mailer empty.
		if ($type === SettingsMailer::SETTINGS_TYPE_KEY && isset($attributes["{$prefix}Action"])) {
			$attributes["{$prefix}Type"] = SettingsMailer::SETTINGS_TYPE_CUSTOM_KEY;
		}

		// Tracking event name.
		$trackingEventName = FiltersOuputMock::getTrackingEventNameFilterValue($type, $formId)['data'];
		if ($trackingEventName) {
			$attributes["{$prefix}TrackingEventName"] = $trackingEventName;
		}

		// Provide additional data to tracking attr.
		$trackingAdditionalData = FiltersOuputMock::getTrackingAditionalDataFilterValue($type, $formId)['data'];
		if ($trackingAdditionalData) {
			$attributes["{$prefix}TrackingAdditionalData"] = \wp_json_encode($trackingAdditionalData);
		}

		// Success redirect url.
		$successRedirectUrl = FiltersOuputMock::getSuccessRedirectUrlFilterValue($type, $formId)['data'];
		if ($successRedirectUrl) {
			$attributes["{$prefix}SuccessRedirect"] = $successRedirectUrl;
		}

		// Success redirect variation.
		if (!$attributes["{$prefix}SuccessRedirectVariation"]) {
			$successRedirectUrl = FiltersOuputMock::getSuccessRedirectVariationFilterValue($type, $formId)['data'];

			if ($successRedirectUrl) {
				$attributes["{$prefix}SuccessRedirectVariation"] = $successRedirectUrl;
			}
		}

		// Custom form name.
		$customFormName = UtilsSettingsHelper::getSettingValue(SettingsGeneral::SETTINGS_GENERAL_FORM_CUSTOM_NAME_KEY, $formId);
		if ($customFormName) {
			$attributes["{$prefix}CustomName"] = $customFormName;
		}

		// Phone sync with country block.
		$attributes["{$prefix}PhoneSync"] = '';
		$filterName = UtilsHooksHelper::getFilterName(['block', 'form', 'phoneSync']);
		if (\has_filter($filterName)) {
			$attributes["{$prefix}PhoneSync"] = \apply_filters($filterName, $type, $formId);
		} else {
			$attributes["{$prefix}PhoneSync"] = !UtilsSettingsHelper::isSettingCheckboxChecked(SettingsBlocks::SETTINGS_BLOCK_PHONE_DISABLE_SYNC_KEY, SettingsBlocks::SETTINGS_BLOCK_PHONE_DISABLE_SYNC_KEY, $formId);
		}

		$attributes["{$prefix}PhoneDisablePicker"] = UtilsSettingsHelper::isOptionCheckboxChecked(SettingsBlocks::SETTINGS_BLOCK_PHONE_DISABLE_PICKER_KEY, SettingsBlocks::SETTINGS_BLOCK_PHONE_DISABLE_PICKER_KEY);

		return $attributes;
	}

	/**
	 * Modify forms block before final output.
	 *
	 * @param array<string, mixed> $blocks Blocks from the core.
	 * @param array<string, mixed> $attributes Attributes to update.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function updateFormsBlockOutput(array $blocks, array $attributes): array
	{
		$output = [];

		$formsNamespace = Components::getSettingsNamespace();
		$manifest = Components::getBlock('forms');
		$formsFormPostId = Components::checkAttr('formsFormPostId', $attributes, $manifest);
		$formsSuccessRedirectVariation = Components::checkAttr('formsSuccessRedirectVariation', $attributes, $manifest);
		$formsSuccessRedirectVariationUrl = Components::checkAttr('formsSuccessRedirectVariationUrl', $attributes, $manifest);
		$formsSuccessRedirectVariationUrlTitle = Components::checkAttr('formsSuccessRedirectVariationUrlTitle', $attributes, $manifest);
		$formsDownloads = Components::checkAttr('formsDownloads', $attributes, $manifest);
		$formsFormDataTypeSelector = Components::checkAttr('formsFormDataTypeSelector', $attributes, $manifest);
		$formsServerSideRender = Components::checkAttr('formsServerSideRender', $attributes, $manifest);
		$formsConditionalTagsRulesForms = Components::checkAttr('formsConditionalTagsRulesForms', $attributes, $manifest);
		$formsAttrs = Components::checkAttr('formsAttrs', $attributes, $manifest);
		$formsCustomName = Components::checkAttr('formsCustomName', $attributes, $manifest);

		$checkStyleEnqueue = UtilsSettingsHelper::isOptionCheckboxChecked(SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_STYLE_KEY, SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY);

		// Iterate blocks an children by passing them form ID.
		foreach ($blocks as $block) {
			if ($block['blockName'] !== "{$formsNamespace}/form-selector") {
				continue;
			}

			$block['attrs']['formSelectorFormPostId'] = $formsFormPostId;

			if (!isset($block['innerBlocks'])) {
				continue;
			}

			$innerBlockOutput = [];
			foreach ($block['innerBlocks'] as $innerBlock) {
				// Bailout if there is  no inner blocks because forms block can't be empty.
				if (!isset($innerBlock['innerBlocks'])) {
					continue;
				}

				// Check if this form uses steps.
				$hasSteps = \array_search($formsNamespace . '/step', \array_column($innerBlock['innerBlocks'] ?? '', 'blockName'), true);
				$hasSteps = $hasSteps !== false;

				// Get block name details.
				$blockName = UtilsGeneralHelper::getBlockNameDetails($innerBlock['blockName'])['name'];

				// Populate forms blocks attributes to the form component later in the chain.
				$innerBlock['attrs']["{$blockName}FormSuccessRedirectVariation"] = $formsSuccessRedirectVariation;
				$innerBlock['attrs']["{$blockName}FormSuccessRedirectVariationUrl"] = $formsSuccessRedirectVariationUrl;
				$innerBlock['attrs']["{$blockName}FormSuccessRedirectVariationUrlTitle"] = $formsSuccessRedirectVariationUrlTitle;
				$innerBlock['attrs']["{$blockName}FormDownloads"] = $formsDownloads;
				$innerBlock['attrs']["{$blockName}FormType"] = $blockName;
				$innerBlock['attrs']["{$blockName}FormPostId"] = $formsFormPostId;
				$innerBlock['attrs']["{$blockName}FormDataTypeSelector"] = $formsFormDataTypeSelector;
				$innerBlock['attrs']["{$blockName}FormServerSideRender"] = $formsServerSideRender;
				$innerBlock['attrs']["{$blockName}FormDisabledDefaultStyles"] = $checkStyleEnqueue;
				$innerBlock['attrs']["{$blockName}FormConditionalTags"] = \wp_json_encode($formsConditionalTagsRulesForms);
				$innerBlock['attrs']["{$blockName}FormCustomName"] = $formsCustomName;
				$innerBlock['attrs']["{$blockName}FormAttrs"] = $formsAttrs;
				$innerBlock['attrs']["blockSsr"] = $formsServerSideRender;

				$inBlockOutput = [];
				$stepKey = 0;

				// If the users don't add first step add it to the list.
				if ($hasSteps && $innerBlock['innerBlocks'][0]['blockName'] !== "{$formsNamespace}/step") {
					$innerBlock['attrs']["{$blockName}FormProgressBarSteps"][] = [
						'name' => 'step-init',
						'label' => \__('Init', 'eightshift-forms'),
					];

					\array_unshift(
						$innerBlock['innerBlocks'],
						[
							'blockName' => "{$formsNamespace}/step",
							'attrs' => [
								'stepStepName' => 'step-init',
								'stepStepContent' => '',
							],
							'innerBlocks' => [],
							'innerHTML' => '',
							'innerContent' => [],
						],
					);
				}

				foreach ($innerBlock['innerBlocks'] as $inKey => $inBlock) {
					// Get fields components details.
					$nameDetails = UtilsGeneralHelper::getBlockNameDetails($inBlock['blockName']);
					$name = $nameDetails['name'];
					$namespace = $nameDetails['namespace'];

					// Do manipulations on specific components.
					switch ($name) {
						case 'submit':
							$inBlock['attrs']["{$name}SubmitServerSideRender"] = $formsServerSideRender;
							$inBlock['attrs']["blockSsr"] = $formsServerSideRender;
							break;
						case 'phone':
						case 'country':
						case 'dynamic':
							$inBlock['attrs'][Components::kebabToCamelCase("{$name}-{$name}FormPostId")] = $formsFormPostId;
							break;
						case 'select':
						case 'checkboxes':
						case 'radios':
							$inBlock = $this->getShowAsOutput($inBlock);
							break;
					}

					// Add custom field block around none forms block to be able to use positioning.
					if ($namespace !== $formsNamespace) {
						// Find all forms attribtues added to a custom block.
						$customUsedAttrsDiff = \array_intersect_key(
							$inBlock['attrs'] ?? [],
							\array_merge(
								Components::getComponent('field')['attributes'],
								Components::getComponent('conditional-tags')['attributes'],
							)
						);

						// Change the forms attributes to a correct prefix and remove them from the original block.
						$customUsedAttrs = [];
						if ($customUsedAttrsDiff) {
							foreach ($customUsedAttrsDiff as $customDiffKey => $customDiffValue) {
								$customUsedAttrs['field' . \ucfirst($customDiffKey)] = $customDiffValue;
								unset($inBlock['attrs'][$customDiffKey]);
							}
						}

						// Change the original output of the custom block.
						$inBlock = [
							'blockName' => "{$formsNamespace}/field",
							'attrs' => \array_merge(
								$customUsedAttrs,
								[
									// Build string of custom blocks.
									'fieldFieldContent' => \apply_filters('the_content', \render_block($inBlock)),
									// Remove label.
									'fieldFieldHideLabel' => true,
									// And remove error fields.
									'fieldFieldUseError' => false,
								]
							),
							'innerBlocks' => [],
							'innerHTML' => '',
							'innerContent' => [],
						];
					}

					// Populate the list of steps position in the original array.
					if ($hasSteps) {
						// If block is step we need to just create block output and exit this loop.
						if ($name === 'step') {
							// Output key is insite the step key and this changes everytime we have step in the loop.
							$stepKey = $inKey;

							$innerBlock['attrs']["{$blockName}FormProgressBarSteps"][] = [
								'name' => $inBlock['attrs']['stepStepName'] ?? '',
								'label' => $inBlock['attrs']['stepStepLabel'] ?? '',
							];

							$inBlockOutput[$stepKey] = [
								'blockName' => $inBlock['blockName'],
								'attrs' => \array_merge(
									$inBlock['attrs'],
									[
										'stepStepContent' => '',
										'stepStepSubmit' => '',
									]
								),
								'innerBlocks' => [],
								'innerHTML' => '',
								'innerContent' => [],
							];
							continue;
						}

						// Remove submit button from the flow and push it to step to be used in the navigation bar.
						if ($name === 'submit') {
							$inBlockOutput[$stepKey]['attrs']['stepStepSubmit'] = \apply_filters('the_content', \render_block($inBlock));
						} else {
							// Blocks in steps are passed as an attribute and we need to convert block to HTML string and append to the previous.
							$inBlockOutput[$stepKey]['attrs']['stepStepContent'] = $inBlockOutput[$stepKey]['attrs']['stepStepContent'] . \apply_filters('the_content', \render_block($inBlock));
						}
					} else {
						// Just populate normal blocks if there are no steps here.
						$inBlockOutput[$inKey] = [
							'blockName' => $inBlock['blockName'],
							'attrs' => $inBlock['attrs'],
							'innerBlocks' => $inBlock['innerBlocks'] ?? [],
							'innerHTML' => '',
							'innerContent' => $inBlock['innerBlocks'] ?? [],
						];
					}
				}

				if ($hasSteps) {
					// Add attribute to form component.
					$innerBlock['attrs']["{$blockName}FormProgressBarMultiflowUse"] = $innerBlock['attrs']["{$blockName}StepMultiflowUse"] ?? false;
					$innerBlock['attrs']["{$blockName}FormProgressBarMultiflowInitCount"] = $innerBlock['attrs']["{$blockName}StepProgressBarMultiflowInitCount"] ?? '';
					$innerBlock['attrs']["{$blockName}FormProgressBarUse"] = $innerBlock['attrs']["{$blockName}StepProgressBarUse"] ?? false;
					$innerBlock['attrs']["{$blockName}FormProgressBarHideLabels"] = $innerBlock['attrs']["{$blockName}StepProgressBarHideLabels"] ?? false;
					$innerBlock['attrs']["{$blockName}FormHasSteps"] = true;

					$inBlockOutput = \array_values($inBlockOutput);

					// Populate the first step as active so we don't have loading state.
					$inBlockOutput[0]['attrs']["stepStepIsActive"] = true;
				}

				// Populate custom hidden fields from filter.
				$filterName = UtilsHooksHelper::getFilterName(['block', 'form', 'additionalHiddenFields']);
				if (\has_filter($filterName)) {
					$customHiddenFields = \apply_filters($filterName, [], $formsFormPostId);

					if ($customHiddenFields) {
						$inBlockOutput = \array_merge($inBlockOutput, $this->getHiddenFields($customHiddenFields));
					}
				}

				$innerBlockOutput[] = [
					'blockName' => $innerBlock['blockName'],
					'attrs' => $innerBlock['attrs'],
					'innerBlocks' => $inBlockOutput,
					'innerHTML' => '',
					'innerContent' => $inBlockOutput,
				];
			}

			$output[] = [
				'blockName' => "{$formsNamespace}/form-selector",
				'attrs' => $block['attrs'],
				'innerBlocks' => $innerBlockOutput,
				'innerHTML' => '',
				'innerContent' => $innerBlockOutput,
			];
		}

		return \array_values($output);
	}

	/**
	 * Get show as output.
	 *
	 * @param array<string, mixed> $block Block array.
	 *
	 * @return array<string, mixed>
	 */
	private function getShowAsOutput(array $block): array
	{
		$nameDetails = UtilsGeneralHelper::getBlockNameDetails($block['blockName']);
		$name = $nameDetails['name'];
		$namespace = $nameDetails['namespace'];
		$attrs = $block['attrs'] ?? [];
		$innerBlocks = $block['innerBlocks'] ?? [];

		if (!$attrs || !$innerBlocks) {
			return $block;
		}

		$showAs = $attrs[Components::kebabToCamelCase("{$name}-{$name}-showAs")] ?? '';

		if ($showAs === $name || $showAs === 'default' || !$showAs) {
			return $block;
		}

		$output = [];

		if (
			($name === 'checkboxes' && $showAs === 'select') ||
			($name === 'checkboxes' && $showAs === 'radios') ||
			($name === 'radios' && $showAs === 'select')
		) {
			$output = $this->getShowAsOutputItem($name, $showAs, $namespace, $attrs, $innerBlocks);
		}

		if (
			($name === 'select' && $showAs === 'checkboxes') ||
			($name === 'radios' && $showAs === 'checkboxes') ||
			($name === 'select' && $showAs === 'radios')
		) {
			$output = $this->getShowAsOutputItem($name, $showAs, $namespace, $attrs, $innerBlocks, true);
		}

		if (!$output) {
			return $block;
		}

		$output['innerHTML'] = $block['innerHTML'] ?? '';
		$output['innerContent'] = $block['innerContent'] ?? [];

		return $output;
	}

	/**
	 * Transform one block to a new block.
	 *
	 * @param string $name Block name.
	 * @param string $showAs Show as name from block attribute.
	 * @param string $namespace Block namespace.
	 * @param array<string, mixed> $attrs Block attributes.
	 * @param array<int, mixed> $innerBlocks Block inner blocks.
	 * @param boolean $isFlipped Is flipped version.
	 *
	 * @return array<string, mixed>
	 */
	private function getShowAsOutputItem(string $name, string $showAs, string $namespace, array $attrs, array $innerBlocks, bool $isFlipped = false): array
	{
		$output = [];
		$maps = Components::getSettings()['showAsMap'];

		$mapName = "{$name}-{$showAs}";

		if ($isFlipped) {
			$mapName = "{$showAs}-{$name}";
		}

		$map = $maps[$mapName];
		$mapTop = $map['top'];
		$mapChildren = $map['children'];
		$mapNames = $map['names'];
		$mapPrefix = $map['prefix'];
		$mapAppend = $map['append'][$name] ?? [];

		if ($isFlipped) {
			$mapTop = \array_flip($mapTop);
			$mapChildren = \array_flip($mapChildren);
		}

		$output['blockName'] = "{$namespace}/{$mapNames[$name]['top']}";
		$output['attrs'] = [];

		foreach ($mapTop as $key => $value) {
			$attr = $attrs[$key] ?? '';

			if (!$attr) {
				continue;
			}

			$output['attrs'][$value] = $attr;
		}

		if ($mapAppend) {
			foreach ($mapAppend as $key => $value) {
				$output['attrs'][$key] = $value;
			}
		}

		foreach ($attrs as $attrKey => $attrsValue) {
			if (\strpos($attrKey, 'Field') !== false) {
				$output['attrs'][\str_replace($mapPrefix[$name]['from'], $mapPrefix[$name]['to'], $attrKey)] = $attrsValue;
			}
		}

		$outputInner = [];

		foreach ($innerBlocks as $innerBlockKey => $innerBlock) {
			$outputInner[$innerBlockKey]['blockName'] = "{$namespace}/{$mapNames[$name]['children']}";
			$attrs = $innerBlock['attrs'] ?? [];

			foreach ($mapChildren as $key => $value) {
				$attr = $attrs[$key] ?? '';

				if (!$attr) {
					continue;
				}

				$outputInner[$innerBlockKey]['attrs'][$value] = $attr;
			}

			$outputInner[$innerBlockKey]['innerBlocks'] = $innerBlock['innerBlocks'] ?? [];
			$outputInner[$innerBlockKey]['innerHTML'] = '';
			$outputInner[$innerBlockKey]['innerContent'] = $innerBlock['innerBlocks'] ?? [];

			$output['innerBlocks'] = $outputInner;
		}

		return $output;
	}

	/**
	 * Get hidden fields from array.
	 *
	 * @param array<int, array<string, string>> $items Items to filter.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getHiddenFields(array $items): array
	{
		$output = [];

		$namespace = Components::getSettingsNamespace();

		foreach ($items as $item) {
			$name = $item['name'] ?? '';
			$value = $item['value'] ?? '';

			if (!$name) {
				continue;
			}

			$output[] = [
				'blockName' => "{$namespace}/input",
				'attrs' => [
					'inputInputFieldHidden' => true,
					'inputInputName' => $name,
					'inputInputValue' => $value,
				],
				'innerBlocks' => [],
				'innerHTML' => '',
				'innerContent' => [],
			];
		}

		return $output;
	}
}
