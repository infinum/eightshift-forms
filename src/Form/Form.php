<?php

/**
 * Class that holds all filter used in the component and blocks regarding form.
 *
 * @package EightshiftLibs\Form
 */

declare(strict_types=1);

namespace EightshiftForms\Form;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Settings\FiltersOuputMock;
use EightshiftForms\Settings\Settings\SettingsBlocks;
use EightshiftForms\Settings\Settings\SettingsSettings;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Form class.
 */
class Form extends AbstractFormBuilder implements ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Use filters mock helper trait.
	 */
	use FiltersOuputMock;

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
		$trackingEventName = $this->getTrackingEventNameFilterValue($type, $formId)['data'];
		if ($trackingEventName) {
			$attributes["{$prefix}TrackingEventName"] = $trackingEventName;
		}

		// Provide additional data to tracking attr.
		$trackingAdditionalData = $this->getTrackingAditionalDataFilterValue($type, $formId)['data'];
		if ($trackingAdditionalData) {
			$attributes["{$prefix}TrackingAdditionalData"] = \wp_json_encode($trackingAdditionalData);
		}

		// Success redirect url.
		$successRedirectUrl = $this->getSuccessRedirectUrlFilterValue($type, $formId)['data'];
		if ($successRedirectUrl) {
			$attributes["{$prefix}SuccessRedirect"] = $successRedirectUrl;
		}

		// Success redirect variation.
		if (!$attributes["{$prefix}SuccessRedirectVariation"]) {
			$successRedirectUrl = $this->getSuccessRedirectVariationFilterValue($type, $formId)['data'];

			if ($successRedirectUrl) {
				$attributes["{$prefix}SuccessRedirectVariation"] = $successRedirectUrl;
			}
		}

		// Phone sync with country block.
		$attributes["{$prefix}PhoneSync"] = '';
		$filterName = Filters::getFilterName(['block', 'form', 'phoneSync']);
		if (\has_filter($filterName)) {
			$attributes["{$prefix}PhoneSync"] = \apply_filters($filterName, $type, $formId);
		} else {
			$attributes["{$prefix}PhoneSync"] = !$this->isCheckboxSettingsChecked(SettingsBlocks::SETTINGS_BLOCK_PHONE_DISABLE_SYNC_KEY, SettingsBlocks::SETTINGS_BLOCK_PHONE_DISABLE_SYNC_KEY, $formId);
		}

		$attributes["{$prefix}PhoneDisablePicker"] = $this->isCheckboxOptionChecked(SettingsBlocks::SETTINGS_BLOCK_PHONE_DISABLE_PICKER_KEY, SettingsBlocks::SETTINGS_BLOCK_PHONE_DISABLE_PICKER_KEY);

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
		$formsDownloads = Components::checkAttr('formsDownloads', $attributes, $manifest);
		$formsFormDataTypeSelector = Components::checkAttr('formsFormDataTypeSelector', $attributes, $manifest);
		$formsServerSideRender = Components::checkAttr('formsServerSideRender', $attributes, $manifest);
		$formsConditionalTagsRulesForms = Components::checkAttr('formsConditionalTagsRulesForms', $attributes, $manifest);
		$formsAttrs = Components::checkAttr('formsAttrs', $attributes, $manifest);

		$checkStyleEnqueue = $this->isCheckboxOptionChecked(SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_STYLE_KEY, SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY);

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
				$blockName = Helper::getBlockNameDetails($innerBlock['blockName'])['name'];

				// Populate forms blocks attributes to the form component later in the chain.
				$innerBlock['attrs']["{$blockName}FormSuccessRedirectVariation"] = $formsSuccessRedirectVariation;
				$innerBlock['attrs']["{$blockName}FormSuccessRedirectVariationUrl"] = $formsSuccessRedirectVariationUrl;
				$innerBlock['attrs']["{$blockName}FormDownloads"] = $formsDownloads;
				$innerBlock['attrs']["{$blockName}FormType"] = $blockName;
				$innerBlock['attrs']["{$blockName}FormPostId"] = $formsFormPostId;
				$innerBlock['attrs']["{$blockName}FormDataTypeSelector"] = $formsFormDataTypeSelector;
				$innerBlock['attrs']["{$blockName}FormServerSideRender"] = $formsServerSideRender;
				$innerBlock['attrs']["{$blockName}FormDisabledDefaultStyles"] = $checkStyleEnqueue;
				$innerBlock['attrs']["{$blockName}FormConditionalTags"] = \wp_json_encode($formsConditionalTagsRulesForms);
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
					$nameDetails = Helper::getBlockNameDetails($inBlock['blockName']);
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
							$inBlock['attrs'][Components::kebabToCamelCase("{$name}-{$name}FormPostId")] = $formsFormPostId;
							break;
					}

					// Add custom field block around none forms block to be able to use positioning.
					if ($namespace !== $formsNamespace) {
						// Find all forms attribtues added to a custom block.
						$customUsedAttrsDiff = \array_intersect_key(
							$inBlock['attrs'] ?? [],
							Components::getComponent('field')['attributes']
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
					$innerBlock['attrs']["{$blockName}FormHasSteps"] = true;

					$inBlockOutput = \array_values($inBlockOutput);
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
}
