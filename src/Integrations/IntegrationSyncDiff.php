<?php

/**
 * Class that holds all filter used the Block Editor Integration diff.
 *
 * @package EightshiftForms\Integrations
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations;

use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\Airtable\SettingsAirtable;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * IntegrationSyncDiff class.
 */
class IntegrationSyncDiff implements ServiceInterface, IntegrationSyncInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('load-post.php', [$this, 'updateFormOnBlockEditorLoad']);
	}

	/**
	 * Sync and update form DB when the user opens a block editor.
	 *
	 * @return void
	 */
	public function updateFormOnBlockEditorLoad(): void
	{
		// Prevent forms sync.
		$skipFormsSync = $this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_SKIP_FORMS_SYNC_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY);
		if ($skipFormsSync) {
			return;
		}

		global $typenow;

		// Bailout if not forms editor page.
		if ($typenow !== Forms::POST_TYPE_SLUG) {
			return;
		}

		// Get Form ID.
		$formId = isset($_GET['post']) ? \sanitize_text_field(\wp_unslash($_GET['post'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Run form sync.
		$syncForm = $this->syncFormDirect($formId);

		// Find final status.
		$status = $syncForm['status'] ?? '';

		// If error output log.
		if ($status === AbstractBaseRoute::STATUS_ERROR) {
			Helper::logger(\array_merge(
				[
					'type' => 'diff',
				],
				$syncForm
			));

			return;
		}

		// Finish with success.
		Helper::logger(\array_merge(
			[
				'type' => 'diff',
			],
			$syncForm
		));
	}

	/**
	 * Sync and update form DB.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<string, mixed>
	 */
	public function syncFormDirect(string $formId): array
	{
		// Run form sync.
		$syncForm = $this->syncFormEditor($formId);

		// Find final status.
		$status = $syncForm['status'] ?? '';

		// If error output log.
		if ($status === AbstractBaseRoute::STATUS_ERROR) {
			return $syncForm;
		}

		$namespace = Components::getSettingsNamespace();

		$keys = $this->getBlockKeysOutput();

		$blockNameKey = $keys['blockName'];
		$attrsKey = $keys['attrs'];
		$innerBlocksKey = $keys['innerBlocks'];
		$innerContentKey = $keys['innerContent'];

		// Create block grammar from array.
		// @phpstan-ignore-next-line.
		$blocksGrammar = \serialize_blocks([
			[
				$blockNameKey => "{$namespace}/form-selector",
				$attrsKey => [],
				$innerBlocksKey => $syncForm['data']['output'],
				$innerContentKey => $syncForm['data']['output'],
			]
		]);

		// Bailout if we have output to show.
		if (!$blocksGrammar) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'integration_missing_itemId',
				'message' => \esc_html__('Block grammer build failed.', 'eightshift-forms'),
			];
		}

		// Update block content.
		$update = $this->updateBlockContent($formId, $blocksGrammar);

		// Bailout if db content update failed.
		if (!$update) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'integration_missing_itemId',
				'message' => \esc_html__('DB update failed.', 'eightshift-forms'),
			];
		}

		return [
			'formId' => $formId,
			'status' => AbstractBaseRoute::STATUS_SUCCESS,
			'debugType' => 'after_success',
			'message' => \esc_html__('Form updated.', 'eightshift-forms'),
		];
	}

	/**
	 * Create new form block output, used for block editor route to populate new integrations after user selection.
	 *
	 * @param string $formId Form Id.
	 * @param string $type Integration type.
	 * @param string $itemId Item integration ID.
	 * @param string $innerId Item integration inner ID.
	 * @param boolean $editorOutput Change output keys depending on the output type.
	 *
	 * @return array<string, mixed>
	 */
	public function createFormEditor(string $formId, string $type, string $itemId, string $innerId, bool $editorOutput = false): array
	{
		// Bailout if form ID is missing.
		if (!$formId) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'before_missing_formId',
				'message' => \esc_html__('Missing form ID.', 'eightshift-forms'),
			];
		}

		// Check if integration filter exists.
		$integrationFilterName = Filters::ALL[$type]['fields'] ?? '';
		if (!\has_filter($integrationFilterName)) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'integration_missing_integration_filter',
				'message' => \esc_html__('Provided integration name is not in our list of available integrations.', 'eightshift-forms'),
			];
		}

		// Get integration fields.
		$integration = \apply_filters($integrationFilterName, $formId, $itemId, $innerId);

		// Prepare integration variables.
		$integrationType = $integration['type'] ?? '';
		$integrationItemId = $integration['itemId'] ?? '';
		$integrationInnerId = $integration['innerId'] ?? '';
		$integrationFields = $integration['fields'] ?? [];

		// Bailout if integration type is missing.
		if (!$integrationType) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'integration_missing_type',
				'message' => \esc_html__('Missing form integration type.', 'eightshift-forms'),
			];
		}

		// Bailout if integration item ID is missing.
		if (!$integrationItemId) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'integration_missing_itemId',
				'message' => \esc_html__('Missing form integration item Id.', 'eightshift-forms'),
			];
		}

		// Bailout if integration inner ID is missing only on airtable.
		if (!$integrationInnerId && $integrationType === SettingsAirtable::SETTINGS_TYPE_KEY) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'integration_missing_innerId',
				'message' => \esc_html__('Missing form integration inner Id.', 'eightshift-forms'),
			];
		}

		// Bailout if integration fields are missing.
		if (!$integrationFields) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'integration_missing_fields',
				'message' => \esc_html__('Missing form integration fields.', 'eightshift-forms'),
			];
		}

		$fields = $this->reconstructBlocksOutput(
			\array_map(
				static function ($item) {
					return $item['integration'];
				},
				$this->prepareIntegrationBlocksForCheck($integrationFields)
			),
			$editorOutput
		);

		// Bailout if integration fields are missing.
		if (!$fields) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'integration_fields_build_empty',
				'message' => \esc_html__('Integration fields build has failed.', 'eightshift-forms'),
			];
		}

		// Output additional array from whom we built block grammar for debug.
		$output['output'] = $fields;

		// Bailout if db content update with success.
		return [
			'formId' => $formId,
			'status' => AbstractBaseRoute::STATUS_SUCCESS,
			'debugType' => 'after_success',
			'message' => \esc_html__('Form updated.', 'eightshift-forms'),
			'data' => $output,
		];
	}

	/**
	 * Sync content and integration form and provide the otuput for block editor route to manualy sync forms.
	 *
	 * @param string $formId Form Id.
	 * @param boolean $editorOutput Change output keys depending on the output type.
	 *
	 * @return array<string, mixed>
	 */
	public function syncFormEditor(string $formId, bool $editorOutput = false): array
	{
		// Bailout if form ID is missing.
		if (!$formId) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'before_missing_formId',
				'message' => \esc_html__('Missing form ID.', 'eightshift-forms'),
			];
		}

		// Get content from DB.
		$content = Helper::getFormDetailsById($formId);

		// Bailout if content is empty.
		if (!$content) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'content_missing_content',
				'message' => \esc_html__('Missing form content.', 'eightshift-forms'),
			];
		}

		// Prepare content variables.
		$contentType = $content['type'] ?? '';
		$contentItemId = $content['itemId'] ? Helper::unserializeAttributes($content['itemId']) : '';
		$contentInnerId = $content['innerId'] ? Helper::unserializeAttributes($content['innerId']) : '';
		$contentFields = $content['fields'] ?? [];

		// Bailout if content type is missing.
		if (!$contentType) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'content_missing_type',
				'message' => \esc_html__('Missing form content integration type block.', 'eightshift-forms'),
			];
		}

		// Bailout if content item ID is missing.
		if (!$contentItemId) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'content_missing_itemId',
				'message' => \esc_html__('Missing form content integration item Id.', 'eightshift-forms'),
			];
		}

		// Bailout if content inner ID is missing only on airtable.
		if (!$contentInnerId && $contentType === SettingsAirtable::SETTINGS_TYPE_KEY) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'content_missing_innerId',
				'message' => \esc_html__('Missing form content integration inner Id.', 'eightshift-forms'),
			];
		}

		// Check if integration filter exists.
		$integrationFilterName = Filters::ALL[$contentType]['fields'] ?? '';
		if (!\has_filter($integrationFilterName)) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'integration_missing_integration_filter',
				'message' => \esc_html__('Provided integration name is not in our list of available integrations.', 'eightshift-forms'),
			];
		}

		// Get integration fields.
		$integration = \apply_filters($integrationFilterName, $formId, $contentItemId, $contentInnerId);

		// Prepare integration variables.
		$integrationType = $integration['type'] ?? '';
		$integrationItemId = $integration['itemId'] ?? '';
		$integrationInnerId = $integration['innerId'] ?? '';
		$integrationFields = $integration['fields'] ?? [];

		// Bailout if integration type is missing.
		if (!$integrationType) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'integration_missing_type',
				'message' => \esc_html__('Missing form integration type.', 'eightshift-forms'),
			];
		}

		// Bailout if integration item ID is missing.
		if (!$integrationItemId) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'integration_missing_itemId',
				'message' => \esc_html__('Missing form integration item Id.', 'eightshift-forms'),
			];
		}

		// Bailout if integration inner ID is missing only on airtable.
		if (!$integrationInnerId && $integrationType === SettingsAirtable::SETTINGS_TYPE_KEY) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'integration_missing_innerId',
				'message' => \esc_html__('Missing form integration inner Id.', 'eightshift-forms'),
			];
		}

		// Bailout if content fields are missing.
		if (!$contentFields) {
			// Bailout if integration fields are missing.
			if (!$integrationFields) {
				return [
					'formId' => $formId,
					'status' => AbstractBaseRoute::STATUS_ERROR,
					'debugType' => 'integration_missing_fields_existing_content',
					'message' => \esc_html__('Missing form content fields and missing integration fields.', 'eightshift-forms'),
				];
			}

			// Bailout if content fields are missing.
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'content_missing_fields',
				'message' => \esc_html__('Missing form content integration fields.', 'eightshift-forms'),
			];
		}

		// Bailout if integration type is different than content type.
		if ($integrationType !== $contentType) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'after_different_type',
				'message' => \esc_html__('Integration type is different than content type.', 'eightshift-forms'),
			];
		}

		// Bailout if integration item ID is different than content item Id.
		if ($integrationItemId !== $contentItemId) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'after_different_itemId',
				'message' => \esc_html__('Integration item ID is different than content item ID.', 'eightshift-forms'),
			];
		}

		// Bailout if integration inner ID is different than content inner Id. Only on airtable.
		if ($integrationInnerId !== $contentInnerId && $integrationType === SettingsAirtable::SETTINGS_TYPE_KEY) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'debugType' => 'after_different_innerId',
				'message' => \esc_html__('Integration inner ID is different than content inner ID.', 'eightshift-forms'),
			];
		}

		// Run diff.
		$output = $this->diffChanges($integration, $content, $editorOutput);

		// Bailout if update is not necesery.
		if (!$output['update']) {
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_SUCCESS,
				'debugType' => 'after_no_update',
				'message' => \esc_html__('Integration and local form are the same, no update required.', 'eightshift-forms'),
				'data' => $output,
			];
		}

		// Bailout if we have output to show.
		if ($output['output']) {
			// Bailout if db content update with success.
			return [
				'formId' => $formId,
				'status' => AbstractBaseRoute::STATUS_SUCCESS,
				'debugType' => 'after_success',
				'message' =>  \esc_html__('Form updated.', 'eightshift-forms'),
				'data' => $output,
			];
		}

		// Bailout if some undefined error occurred.
		return [
			'formId' => $formId,
			'status' => AbstractBaseRoute::STATUS_ERROR,
			'debugType' => 'after_undefined',
			'message' => \esc_html__('Something went wrong.', 'eightshift-forms'),
			'data' => $output,
		];
	}

	/**
	 * Compare content and integration form and outputs diff of a built block ready for import in the db.
	 *
	 * @param array<string, mixed> $integration Form integration content.
	 * @param array<string, mixed> $content Form content.
	 * @param boolean $editorOutput Change block output format depending on the usage.
	 *
	 * @return array<string, mixed>
	 */
	private function diffChanges(array $integration, array $content, bool $editorOutput = false): array
	{
		// Prepare arrays for diff.
		$diff = $this->prepareContentBlocksForCheck($content['fields']['innerBlocks'][0]['innerBlocks'] ?? [], $this->prepareIntegrationBlocksForCheck($integration['fields']));

		// Prepare standard output.
		$output = [
			'type' => $integration['type'] ?? '',
			'itemId' => $integration['itemId'] ?? '',
			'innerId' => $integration['innerId'] ?? '',
			'update' => false,
			'removed' => [],
			'added' => [],
			'replaced' => [],
			'changed' => [],
			'order' => $diff['order'],
			'output' => [],
			'diff' => $diff['diff'],
		];

		// Loop diff of content and integration.
		foreach ($diff['diff'] as $key => $block) {
			$isExternal = $block['content']['isExternal'] ?? false;

			if ($isExternal) {
				// Output none forms blocks.
				$changes = [
					'removed' => [],
					'added' => [],
					'replaced' => [],
					'changed' => [],
					'output' => $block['content'],
					'update' => false,
				];
			} else {
				// Do diff on one field.
				$changes = $this->diffChange($block['integration'] ?? [], $block['content'] ?? [], $key);
			}

			// Loop all outputs and prepare for the final output.
			foreach ($changes as $changeKey => $changeValue) {
				// No value no output necesery.
				if (!$changeValue) {
					continue;
				}

				// Some output keys are array, some are strings and some require keys.
				switch ($changeKey) {
					case 'update':
						$output[$changeKey] = $changeValue;
						break;
					case 'output':
						$output[$changeKey][$key] = $changeValue;
						break;
					default:
						$output[$changeKey][] = $changeValue;
						break;
				}
			}
		}

		// Reorder block by provided array list in the content data and remove items that are missing.
		$output['output'] = \array_filter(
			\array_replace(
				\array_flip($output['order']),
				$output['output']
			),
			static fn($item) => \is_array($item)
		);

		// Recounstruct blocks output and build array for final serialization.
		$output['output'] = $this->reconstructBlocksTopLevelOutput($output, $editorOutput);

		return $output;
	}

	/**
	 * Compare integration and content of one field and outputs new field.
	 *
	 * @param array<string, mixed> $integration Form integration content.
	 * @param array<string, mixed> $content Form content.
	 * @param string $key Index key.
	 *
	 * @return array<string, mixed>
	 */
	private function diffChange(array $integration, array $content, string $key): array
	{
		// Prepare output.
		$output = [
			'removed' => [],
			'added' => [],
			'replaced' => [],
			'changed' => [],
			'output' => [],
			'update' => false,
		];

		// Remove item if block is not present on integration, output nothing.
		if (!$integration) {
			$output['update'] = true;
			$output['removed'] = $key;
			return $output;
		}

		// If field exists on the integration but not on the content add it.
		if (!$content) {
			$output['update'] = true;
			$output['added'] = $key;
			$output['output'] = $integration;
			return $output;
		}

		// If field type has changed on integration use the integration one.
		if ($integration['component'] !== $content['component']) {
			$output['update'] = true;
			$output['replaced'] = $key;
			$output['output'] = $integration;
			return $output;
		}

		// Check if disabled attrs changed.
		$innerOutput = $content;

		// Find prefix of the component.
		$prefix = Components::kebabToCamelCase($integration['component'] . \ucfirst($integration['component']));

		// Find components disabled options.
		$disabledOptionsIntegration = $integration['attrs']["{$prefix}DisabledOptions"] ?? [];

		$innerOutput['attrs']["{$prefix}DisabledOptions"] = $disabledOptionsIntegration;

		// Check disabled options.
		if ($disabledOptionsIntegration) {
			foreach ($disabledOptionsIntegration as $disabledOption) {
				// Find attributes in integration and content that match disabled options item.
				$i = $integration['attrs'][$disabledOption] ?? '';
				$c = $content['attrs'][$disabledOption] ?? '';

				// If intregration is missing disabled or protected attribute. There could be and issue in the mapping of component attributes for integration.
				if (!$i) {
					$output['update'] = true;
					$output['replaced'] = $key;
					$output['output'] = $integration;
					break;
				}

				// If content has missing, disabled or protected attribute add it from integration.
				if (!$c) {
					$output['update'] = true;
					$output['changed'][$key][] = $disabledOption;
					$innerOutput['attrs'][$disabledOption] = $i;
					break;
				}

				// If values of attribute in content and intregation are diffrerent do something.
				if ($i !== $c) {
					// If protected attribute name has changed we need to update the whole block. This is an unlikely scenario but it can happen.
					if ($i === "{$prefix}Name" && $c === "{$prefix}Name") {
						$output['update'] = true;
						$output['replaced'] = $key;
						$output['output'] = $integration;
						break;
					}

					// Output the changed value.
					$output['update'] = true;
					$output['changed'][$key][] = $disabledOption;
					$innerOutput['attrs'][$disabledOption] = $i;
					continue;
				}
			}
		}

		// Populate output data.
		$output['output'] = $innerOutput;

		$disabledOptionsKeys = \array_flip($output['output']['attrs']["{$prefix}DisabledOptions"]);

		// Add missing content attributes from integration.
		$missingAttributes = \array_diff_key($integration['attrs'], $content['attrs']);
		if ($missingAttributes) {
			foreach ($missingAttributes as $missingAttributesKey => $missingAttributesValue) {
				// No need to add default values.
				if ($missingAttributesKey === 'inputInputType' && $missingAttributesValue === 'text') {
					continue;
				}

				// Add missing attributes to the otput.
				$output['update'] = true;
				$output['added'] = $missingAttributesKey;
				$output['output']['attrs'][$missingAttributesKey] = $missingAttributesValue;
			}
		}

		// Remove attributes removed from integration but it is still in the content.
		$removedAttributes = \array_diff_key($content['attrs'], $integration['attrs']);
		if ($removedAttributes) {
			foreach ($removedAttributes as $removedAttributesKey => $removedAttributesValue) {
				if (!isset($disabledOptionsKeys[$removedAttributesKey])) {
					continue;
				}

				// Remove attributes to the otput.
				$output['update'] = true;
				$output['removed'] = $removedAttributesKey;
				unset($output['output']['attrs'][$removedAttributesKey]);
			}
		}

		return $output;
	}

	/**
	 * Prepare integration blocks for diff check.
	 *
	 * @param array<string, mixed> $blocks Blocks from external integration.
	 *
	 * @return array<string, mixed>
	 */
	private function prepareIntegrationBlocksForCheck(array $blocks): array
	{
		$output = [];

		$nestedKeys = \array_flip(AbstractFormBuilder::NESTED_KEYS);
		$namespace = Components::getSettingsNamespace();

		foreach ($blocks as $block) {
			$blockTypeOriginal = $block['component'] ?? '';

			if (!$blockTypeOriginal) {
				continue;
			}

			$blockType = Components::kebabToCamelCase($blockTypeOriginal, '-');
			$blockName = "{$blockType}Name";

			$name = $block[$blockName] ?? '';

			if (!$name) {
				continue;
			}

			$output[$name]['integration']  = [
				'namespace' => $namespace,
				'component' => $blockTypeOriginal,
				'prefix' => $blockType . \ucfirst($blockType),
				'attrs' => $this->prepareBlockAttributes($block, $blockType),
				'parent' => '',
				'inner' => [],
				'isExternal' => false,
			];

			$innerBlocks = \array_intersect_key($block, $nestedKeys);

			if ($innerBlocks) {
				foreach (\reset($innerBlocks) as $innerKey => $innerBlock) {
					$blockInnerTypeOriginal = $innerBlock['component'] ?? '';

					if (!$blockInnerTypeOriginal) {
						continue;
					}

					$blockInnerType = Components::kebabToCamelCase($blockInnerTypeOriginal, '-');
					$blockInnerAttributes = $this->prepareBlockAttributes($innerBlock, $blockInnerType);
					$innerPrefix = $blockInnerType . \ucfirst($blockInnerType);

					$output[$this->getInnerBlocksKeyName($innerPrefix, $blockInnerAttributes, $innerKey, $name)]['integration']  = [
						'namespace' => $namespace,
						'component' => $blockInnerTypeOriginal,
						'prefix' => $innerPrefix,
						'attrs' => $blockInnerAttributes,
						'parent' => $name,
						'inner' => [],
						'isExternal' => false,
					];
				}
			}
		}

		return $output;
	}

	/**
	 * Prepare content blocks for diff check and combine with integration blocks.
	 *
	 * @param array<string, mixed> $blocks Blocks from form content.
	 * @param array<string, mixed> $integration Prepared blocks external integration.
	 *
	 * @return array<string, mixed>
	 */
	private function prepareContentBlocksForCheck(array $blocks, array $integration): array
	{
		$output = $integration;

		// Used to preserver content order of the blocks.
		$order = [];

		foreach ($blocks as $index => $block) {
			$blockTypeOriginal = $block['blockName'] ?? '';

			if (!$blockTypeOriginal) {
				continue;
			}


			$blockType = $this->getBlockAttributePrefixByFullBlockName($blockTypeOriginal);
			$blockNamespace = $blockType['namespace'];

			// Output block as is if it is not forms block.
			if ($blockNamespace !== Components::getSettingsNamespace()) {
				$noneFormsBlockName = "{$blockTypeOriginal}-{$index}";

				$output[$noneFormsBlockName]['content']  = [
					'namespace' => $blockNamespace,
					'component' => $blockType['component'],
					'prefix' => '',
					'attrs' => $block['attrs'],
					'parent' => '',
					'inner' => $block['innerBlocks'],
					'isExternal' => true,
				];

				$order[] = $noneFormsBlockName;

				continue;
			}

			$blockName = $blockType['prefix'] . "Name";

			if (!$block['attrs']) {
				continue;
			}

			$name = $block['attrs'][$blockName] ?? '';

			if (!$name) {
				continue;
			}

			$block['attrs'] = \array_filter($block['attrs']);

			$output[$name]['content']  = [
				'namespace' => $blockNamespace,
				'component' => $blockType['component'],
				'prefix' => $blockType['prefix'],
				'attrs' => $block['attrs'],
				'parent' => '',
				'inner' => [],
				'isExternal' => false,
			];

			$order[] = $name;

			if (isset($block['innerBlocks'])) {
				foreach ($block['innerBlocks'] as $innerKey => $innerBlock) {
					$blockInnerType = $innerBlock['blockName'] ?? '';

					if (!$blockInnerType) {
						continue;
					}

					$blockInnerType = $this->getBlockAttributePrefixByFullBlockName($blockInnerType);
					$blockInnerAttributes = $innerBlock['attrs'];
					$innerPrefix = $blockInnerType['prefix'];

					$innerKeyValue = $this->getInnerBlocksKeyName($innerPrefix, $blockInnerAttributes, $innerKey, $name);

					$output[$innerKeyValue]['content'] = [
						'namespace' => $blockInnerType['namespace'],
						'component' => $blockInnerType['component'],
						'prefix' => $innerPrefix,
						'attrs' => $blockInnerAttributes,
						'parent' => $name,
						'inner' => [],
						'isExternal' => false,
					];

					$order[] = $innerKeyValue;
				}
			}
		}

		return [
			'order' => $order ? $order : \array_keys($output),
			'diff' => $output,
		];
	}

	/**
	 * Rebuild for blocks output in block grammar format after diff check. Top level with all blocks.
	 *
	 * @param array<string, mixed> $data Diff prepared data.
	 * @param boolean $editorOutput Change output keys depending on the output type.
	 *
	 * @return array<int, array<string, array<int|string, array<string, mixed>|string>|string>>
	 */
	private function reconstructBlocksTopLevelOutput(array $data, bool $editorOutput = false): array
	{
		$fieldsOutput = $this->reconstructBlocksOutput($data['output'], $editorOutput);

		$namespace = Components::getSettingsNamespace();

		$keys = $this->getBlockKeysOutput($editorOutput);

		$blockNameKey = $keys['blockName'];
		$attrsKey = $keys['attrs'];
		$innerBlocksKey = $keys['innerBlocks'];
		$innerContentKey = $keys['innerContent'];

		return [
			[
				$blockNameKey => "{$namespace}/" . $data['type'],
				$attrsKey => \array_merge(
					[
						$data['type'] . "IntegrationId" => $data['itemId'],
					],
					$data['innerId'] ? [
						$data['type'] . "IntegrationInnerId" => $data['innerId'],
					] : []
				),
				$innerBlocksKey => $fieldsOutput,
				$innerContentKey => $fieldsOutput,
			],
		];
	}

	/**
	 * Rebuild for blocks output in block grammar format after diff check. Only inner blocks for integration
	 *
	 * @param array<string, mixed> $data Diff prepared data.
	 * @param boolean $editorOutput Change output keys depending on the output type.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function reconstructBlocksOutput(array $data, bool $editorOutput = false): array
	{
		$fieldsOutput = [];

		$keys = $this->getBlockKeysOutput($editorOutput);

		$blockNameKey = $keys['blockName'];
		$attrsKey = $keys['attrs'];
		$innerBlocksKey = $keys['innerBlocks'];
		$innerContentKey = $keys['innerContent'];

		foreach ($data as $key => $value) {
			if (!$value['parent']) {
				$fieldsOutput[$key] = [
					$blockNameKey => $value['namespace'] . '/' . $value['component'],
					$attrsKey => $value['attrs'],
					$innerBlocksKey => $value['inner'],
					$innerContentKey => $value['inner'],
				];
			} else {
				$innerOutput = [
					$blockNameKey => $value['namespace'] . '/' . $value['component'],
					$attrsKey => $value['attrs'],
					$innerBlocksKey => $value['inner'],
					$innerContentKey => $value['inner'],
				];

				$fieldsOutput[$value['parent']][$innerBlocksKey][] = $innerOutput;
				$fieldsOutput[$value['parent']][$innerContentKey][] = $innerOutput;
			}
		}

		return \array_values($fieldsOutput);
	}

	/**
	 * Update block content with the new version of the blocks.
	 *
	 * @param string $formId Form Id.
	 * @param string $content Form Block grammar content.
	 *
	 * @return int
	 */
	private function updateBlockContent(string $formId, string $content): int
	{
		return \wp_update_post([
			'ID' => (int) $formId,
			'post_content' => \wp_slash($content),
		 ]);
	}

	/**
	 * Prepare every attribute for later usage in diff.
	 *
	 * @param array<string, mixed> $attributes Array of all component attributes.
	 * @param string $component Component name for attributes.
	 *
	 * @return array<string, mixed>
	 */
	private function prepareBlockAttributes(array $attributes, string $component): array
	{
		$output = [];

		$nestedKeys = \array_flip(AbstractFormBuilder::NESTED_KEYS);

		foreach ($attributes as $key => $value) {
			if ($key === 'component') {
				continue;
			}

			if (!$value) {
				continue;
			}

			if (isset($nestedKeys[$key])) {
				continue;
			}

			if ($key === "{$component}DisabledOptions") {
				$value = \array_values(\array_map(
					static function ($item) use ($component) {
						return $component . \ucfirst($item);
					},
					$value
				));
			}

			$output[$component . \ucfirst($key)] = $value;
		}

		return $output;
	}

	/**
	 * Get Block attribute prefix from full block name.
	 *
	 * @param string $name Block name to check.
	 *
	 * @return array<string, string>
	 */
	private function getBlockAttributePrefixByFullBlockName(string $name): array
	{
		$blockName = Helper::getBlockNameDetails($name);

		$component = $blockName['nameAttr'];

		return [
			'namespace' => $blockName['namespace'],
			'component' => $blockName['name'],
			'prefix' => "{$component}" . \ucfirst($component),
		];
	}

	/**
	 * General inner blocks key name from component value atrribute.
	 *
	 * @param string $prefix Component prefix.
	 * @param array<string, mixed> $attributes Array of all component attributes.
	 * @param integer $index Index in list of inner blocks.
	 * @param string $parentName Parent block name.
	 *
	 * @return string
	 */
	private function getInnerBlocksKeyName(string $prefix, array $attributes, int $index, string $parentName): string
	{
		$value = $attributes["{$prefix}Value"] ?? '';
		$label = $attributes["{$prefix}Label"] ?? '';

		if (!$value) {
			$value = $label;
		}

		if (!$value) {
			return "{$parentName}-{$index}";
		}

		$value = \crc32((string) $value);

		return "{$parentName}-{$value}";
	}

	/**
	 * Get correct block output keys depending on the usage.
	 *
	 * @param bool $editorOutput Change output keys depending on the output type.
	 *
	 * @return array<string, string>
	 */
	private function getBlockKeysOutput(bool $editorOutput = false): array
	{
		return [
			'blockName' => $editorOutput ? 'name' : 'blockName',
			'attrs' => $editorOutput ? 'attributes' : 'attrs',
			'innerBlocks' => 'innerBlocks',
			'innerContent' => 'innerContent',
		];
	}
}
