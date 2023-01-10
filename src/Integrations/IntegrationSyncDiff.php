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
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;
use WP_Query;

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
		\add_action('load-post.php', [$this, 'updateForm']);
	}

	/**
	 * Update form when the user opens a block editor.
	 *
	 * @return void
	 */
	public function updateForm(): void
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
		$syncForm = $this->syncForm($formId);

		// Find final status.
		$status = $syncForm['status'] ?? '';

		// If error output log.
		if ($status === 'error') {
			Helper::logger(array_merge(
				[
					'type' => 'diff',
				],
				$syncForm
			));
		}
	}

	public function createForm(string $formId, string $type, string $itemId, string $innerId): array
	{
		// Bailout if form ID is missing.
		if (!$formId) {
			return [
				'formId' => $formId,
				'status' => 'error',
				'debugType' => 'before_missing_formId',
				'message' => \esc_html__('Missing form ID.', 'eightshift-forms'),
			];
		}

		// Check if integration filter exists.
		$integrationFilterName = Filters::ALL[$type]['fields'] ?? '';
		if (!\has_filter($integrationFilterName)) {
			return [
				'formId' => $formId,
				'status' => 'error',
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
				'status' => 'error',
				'debugType' => 'integration_missing_type',
				'message' => \esc_html__('Missing form integration type.', 'eightshift-forms'),
			];
		}

		// Bailout if integration item ID is missing.
		if (!$integrationItemId) {
			return [
				'formId' => $formId,
				'status' => 'error',
				'debugType' => 'integration_missing_itemId',
				'message' => \esc_html__('Missing form integration item Id.', 'eightshift-forms'),
			];
		}

		// Bailout if integration inner ID is missing only on airtable.
		if (!$integrationInnerId && $integrationType === SettingsAirtable::SETTINGS_TYPE_KEY) {
			return [
				'formId' => $formId,
				'status' => 'error',
				'debugType' => 'integration_missing_innerId',
				'message' => \esc_html__('Missing form integration inner Id.', 'eightshift-forms'),
			];
		}

		// Bailout if integration fields are missing.
		if (!$integrationFields) {
			return [
				'formId' => $formId,
				'status' => 'error',
				'debugType' => 'integration_missing_fields',
				'message' => \esc_html__('Missing form integration fields.', 'eightshift-forms'),
			];
		}

		$fields = $this->reconstructBlocksOutput(
			array_map(
				static function ($item) {
					return $item['integration'];
				},
				$this->prepareIntegrationBlocksForCheck($integrationFields)
			),
			true
		);

		// Bailout if integration fields are missing.
		if (!$fields) {
			return [
				'formId' => $formId,
				'status' => 'error',
				'debugType' => 'integration_fields_build_empty',
				'message' => \esc_html__('Integration fields build has failed.', 'eightshift-forms'),
			];
		}

		// Output additional array from whom we built block grammar for debug.
		$output['output'] = $fields;

		// Bailout if db content update with success.
		return [
			'formId' => $formId,
			'status' => 'success',
			'debugType' => 'after_success',
			'message' => \esc_html__('Form updated.', 'eightshift-forms'),
			'data' => $output,
		];
	}

	/**
	 * Sync content and integration form and update the database with the new version on success.
	 *
	 * @param string $formId Form Id.
	 * @param boolean $isPreview Check if used as preview.
	 *
	 * @return array
	 */
	public function syncForm(string $formId, bool $isPreview = false): array
	{
		// Bailout if form ID is missing.
		if (!$formId) {
			return [
				'formId' => $formId,
				'status' => 'error',
				'debugType' => 'before_missing_formId',
				'message' => \esc_html__('Missing form ID.', 'eightshift-forms'),
			];
		}

		// Get content from DB.
		$content = $this->getFormContent($formId);

		// Bailout if content is empty.
		if (!$content) {
			return [
				'formId' => $formId,
				'status' => 'error',
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
				'status' => 'error',
				'debugType' => 'content_missing_type',
				'message' => \esc_html__('Missing form content integration type block.', 'eightshift-forms'),
			];
		}

		// Bailout if content item ID is missing.
		if (!$contentItemId) {
			return [
				'formId' => $formId,
				'status' => 'error',
				'debugType' => 'content_missing_itemId',
				'message' => \esc_html__('Missing form content integration item Id.', 'eightshift-forms'),
			];
		}

		// Bailout if content inner ID is missing only on airtable.
		if (!$contentInnerId && $contentType === SettingsAirtable::SETTINGS_TYPE_KEY) {
			return [
				'formId' => $formId,
				'status' => 'error',
				'debugType' => 'content_missing_innerId',
				'message' => \esc_html__('Missing form content integration inner Id.', 'eightshift-forms'),
			];
		}

		// Check if integration filter exists.
		$integrationFilterName = Filters::ALL[$contentType]['fields'] ?? '';
		if (!\has_filter($integrationFilterName)) {
			return [
				'formId' => $formId,
				'status' => 'error',
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
				'status' => 'error',
				'debugType' => 'integration_missing_type',
				'message' => \esc_html__('Missing form integration type.', 'eightshift-forms'),
			];
		}

		// Bailout if integration item ID is missing.
		if (!$integrationItemId) {
			return [
				'formId' => $formId,
				'status' => 'error',
				'debugType' => 'integration_missing_itemId',
				'message' => \esc_html__('Missing form integration item Id.', 'eightshift-forms'),
			];
		}

		// Bailout if integration inner ID is missing only on airtable.
		if (!$integrationInnerId && $integrationType === SettingsAirtable::SETTINGS_TYPE_KEY) {
			return [
				'formId' => $formId,
				'status' => 'error',
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
					'status' => 'error',
					'debugType' => 'integration_missing_fields_existing_content',
					'message' => \esc_html__('Missing form content fields and missing integration fields.', 'eightshift-forms'),
				];
			}

			// Bailout if content fields are missing.
			return [
				'formId' => $formId,
				'status' => 'error',
				'debugType' => 'content_missing_fields',
				'message' => \esc_html__('Missing form content integration fields.', 'eightshift-forms'),
			];
		}

		// Bailout if integration type is different than content type.
		if ($integrationType !== $contentType) {
			return [
				'formId' => $formId,
				'status' => 'error',
				'debugType' => 'after_different_type',
				'message' => \esc_html__('Integration type is different than content type.', 'eightshift-forms'),
			];
		}

		// Bailout if integration item ID is different than content item Id.
		if ($integrationItemId !== $contentItemId) {
			return [
				'formId' => $formId,
				'status' => 'error',
				'debugType' => 'after_different_itemId',
				'message' => \esc_html__('Integration item ID is different than content item ID.', 'eightshift-forms'),
			];
		}

		// Bailout if integration inner ID is different than content inner Id. Only on airtable.
		if ($integrationInnerId !== $contentInnerId && $integrationType === SettingsAirtable::SETTINGS_TYPE_KEY) {
			return [
				'formId' => $formId,
				'status' => 'error',
				'debugType' => 'after_different_innerId',
				'message' => \esc_html__('Integration inner ID is different than content inner ID.', 'eightshift-forms'),
			];
		}

		// Run diff.
		$output = $this->diffChanges($integration, $content);

		// Bailout if update is not necesery.
		if (!$output['update']) {
			return [
				'formId' => $formId,
				'status' => 'success',
				'debugType' => 'after_no_update',
				'message' => \esc_html__('Integration and local form are the same, no update required.', 'eightshift-forms'),
				'data' => $output,
			];
		}

		// Bailout if we have output to show.
		if ($output['output']) {
			// If preview is used don't update database content.
			if (!$isPreview) {
				$update = $this->updateBlockContent($formId, $output['output']);

				// Bailout if db content update failed.
				if (!$update) {
					return [
						'formId' => $formId,
						'status' => 'error',
						'debugType' => 'after_error_form_update',
						'message' => \esc_html__('Something went wrong with form update.', 'eightshift-forms'),
						'data' => $output,
					];
				}
			}

			// Bailout if db content update with success.
			return [
				'formId' => $formId,
				'status' => 'success',
				'debugType' => 'after_success',
				'message' => $isPreview ? \esc_html__('Form preview mode.', 'eightshift-forms') : \esc_html__('Form updated.', 'eightshift-forms'),
				'data' => $output,
			];
		}

		// Bailout if some undefined error occurred.
		return [
			'formId' => $formId,
			'status' => 'error',
			'debugType' => 'after_undefined',
			'message' => \esc_html__('Something went wrong.', 'eightshift-forms'),
			'data' => $output,
		];
	}

	/**
	 * Compare content and integration form and outputs diff of a built block ready for import in the db.
	 *
	 * @param array $integration Form integration content.
	 * @param array $content Form content.
	 *
	 * @return array
	 */
	private function diffChanges(array $integration, array $content): array
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
			'output' => [],
			'diff' => $diff,
		];

		// Loop diff of content and integration.
		foreach ($diff as $key => $block) {
			// Do diff on one field.
			$changes = $this->diffChange($block['integration'] ?? [], $block['content'] ?? [], $key);

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

		// Recounstruct blocks output and build array for final serialization.
		$blocksOutput = $this->reconstructBlocksTopLevelOutput($output['output'], $output['type'], $output['itemId']);

		// Create block grammar.
		$output['output'] = serialize_blocks($blocksOutput);

		// Output additional array from whom we built block grammar for debug.
		$output['outputPure'] = $blocksOutput;

		return $output;
	}

	/**
	 * Compare integration and content of one field and outputs new field.
	 *
	 * @param array $integration Form integration content.
	 * @param array $content Form content.
	 * @param string $key Index key.
	 *
	 * @return array
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
		$prefix = $integration['component'] . ucfirst($integration['component']);

		// Find components disabled options.
		$disabledOptions = $integration['attrs']["{$prefix}DisabledOptions"] ?? [];

		// Check disabled options.
		if ($disabledOptions) {
			foreach ($disabledOptions as $disabledOption) {
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
					$innerOutput['attrs']["{$prefix}DisabledOptions"] = $integration['attrs']["{$prefix}DisabledOptions"];
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
					$innerOutput['attrs']["{$prefix}DisabledOptions"] = $integration['attrs']["{$prefix}DisabledOptions"];
					$innerOutput['attrs'][$disabledOption] = $i;
					continue;
				}
			}
		}

		$output['output'] = $innerOutput;

		return $output;
	}

	/**
	 * Prepare integration blocks for diff check.
	 *
	 * @param array $blocks Blocks from external integration.
	 *
	 * @return array
	 */
	private function prepareIntegrationBlocksForCheck(array $blocks): array
	{
		$output = [];

		$nestedKeys = array_flip(AbstractFormBuilder::NESTED_KEYS);
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
				'prefix' => $blockType . ucfirst($blockType),
				'attrs' => $this->prepareBlockAttributes($block, $blockType),
				'parent' => '',
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
					$innerPrefix = $blockInnerType . ucfirst($blockInnerType);

					$output[$this->getInnerBlocksKeyName($innerPrefix, $blockInnerAttributes, $innerKey, $name)]['integration']  = [
						'namespace' => $namespace,
						'component' => $blockInnerTypeOriginal,
						'prefix' => $innerPrefix,
						'attrs' => $blockInnerAttributes,
						'parent' => $name,
					];
				}
			}
		}

		return $output;
	}

	/**
	 * Prepare content blocks for diff check and combine with integration blocks.
	 *
	 * @param array $blocks Blocks from form content.
	 * @param array $integration Prepared blocks external integration.
	 *
	 * @return array
	 */
	private function prepareContentBlocksForCheck(array $blocks, array $integration): array
	{
		$output = $integration;

		foreach ($blocks as $block) {

			$blockTypeOriginal = $block['blockName'] ?? '';

			if (!$blockTypeOriginal) {
				continue;
			}

			$blockType = $this->getBlockAttributePrefixByFullBlockName($blockTypeOriginal);
			$blockName = $blockType['prefix']. "Name";

			if (!$block['attrs']) {
				continue;
			}

			$name = $block['attrs'][$blockName] ?? '';

			if (!$name) {
				continue;
			}

			$block['attrs'] = array_filter($block['attrs']);

			$output[$name]['content']  = [
				'namespace' => $blockType['namespace'],
				'component' => $blockType['component'],
				'prefix' => $blockType['prefix'],
				'attrs' => $block['attrs'],
				'parent' => '',
			];

			if (isset($block['innerBlocks'])) {
				foreach ($block['innerBlocks'] as $innerKey => $innerBlock) {
					$blockInnerType = $innerBlock['blockName'] ?? '';

					if (!$blockInnerType) {
						continue;
					}
					
					$blockInnerType = $this->getBlockAttributePrefixByFullBlockName($blockInnerType);
					$blockInnerAttributes = $innerBlock['attrs'];
					$innerPrefix = $blockInnerType['prefix'];

					$output[$this->getInnerBlocksKeyName($innerPrefix, $blockInnerAttributes, $innerKey, $name)]['content'] = [
						'namespace' => $blockInnerType['namespace'],
						'component' => $blockInnerType['component'],
						'prefix' => $innerPrefix,
						'attrs' => $blockInnerAttributes,
						'parent' => $name,
					];
				}
			}
		}

		return $output;
	}

	/**
	 * Rebuild for blocks output in block grammar format after diff check. Top level with all blocks.
	 *
	 * @param array $data Diff prepared data.
	 * @param string $type Integation type.
	 * @param string $itemId Item ID from integration.
	 *
	 * @return array
	 */
	private function reconstructBlocksTopLevelOutput(array $data, string $type, string $itemId): array
	{
		$fieldsOutput = $this->reconstructBlocksOutput($data);

		$namespace = Components::getSettingsNamespace();

		$innerBlock = [
			[
				'blockName' => "{$namespace}/" . $type,
				'attrs' => [
					$type . "IntegrationId" => $itemId,
				],
				'innerBlocks' => $fieldsOutput,
				'innerContent' => $fieldsOutput,
			],
		];

		return [
			[
				'blockName' => "{$namespace}/form-selector",
				'attrs' => [],
				'innerBlocks' => $innerBlock,
				'innerContent' => $innerBlock,
			],
		];
	}

	/**
	 * Rebuild for blocks output in block grammar format after diff check. Only inner blocks for integration
	 *
	 * @param array $data Diff prepared data.
	 *
	 * @return array
	 */
	private function reconstructBlocksOutput(array $data, $editorOutput = false): array
	{
		$fieldsOutput = [];

		$blockNameKey = $editorOutput ? 'name' : 'blockName';
		$attrsKey = $editorOutput ? 'attributes' : 'attrs';
		$innerBlocksKey = 'innerBlocks';
		$innerContentKey = 'innerContent';

		foreach ($data as $key => $value) {
			if (!$value['parent']) {
				$fieldsOutput[$key] = [
					$blockNameKey => $value['namespace'] . '/' . $value['component'],
					$attrsKey => $value['attrs'],
					$innerBlocksKey => [],
					$innerContentKey => [],
				];
			} else {
				$innerOutput = [
					$blockNameKey => $value['namespace'] . '/' . $value['component'],
					$attrsKey => $value['attrs'],
					$innerBlocksKey => [],
					$innerContentKey => [],
				];

				$fieldsOutput[$value['parent']][$innerBlocksKey][] = $innerOutput;
				$fieldsOutput[$value['parent']][$innerContentKey][] = $innerOutput;
			}
		}

		return array_values($fieldsOutput);
	}

	/**
	 * Get current form content from the database and do initial preparations for diff.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array
	 */
	private function getFormContent(string $formId): array
	{
		$output = [
			'type' => '',
			'itemId' => '',
			'innerId' => '',
			'fields' => [],
		];

		$theQuery = new WP_Query([
			'p' => $formId,
			'post_type' => Forms::POST_TYPE_SLUG,
			'no_found_rows' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'post_status' => 'any',
		]);

		$form = $theQuery->post;

		wp_reset_postdata();

		if (!$form) {
			return $output;
		}

		$blocks = parse_blocks($form->post_content);

		if (!$blocks) {
			return $output;
		}

		$blocks = $blocks[0];

		$blockName = $blocks['innerBlocks'][0]['blockName'] ?? '';

		if (!$blockName) {
			return $output;
		}

		$type = \explode('/', $blockName);
		$type = \end($type);
		$output['type'] = $type;

		$itemId = $blocks['innerBlocks'][0]['attrs'][Components::kebabToCamelCase($type) . "IntegrationId"] ?? '';
		$innerId = $blocks['innerBlocks'][0]['attrs'][Components::kebabToCamelCase($type) . "IntegrationInnerId"] ?? '';

		if (!$itemId) {
			return $output;
		}

		// Only Airtable has inner items.
		if ($type === SettingsAirtable::SETTINGS_TYPE_KEY && !$innerId) {
			return $output;
		}

		$output['itemId'] = $itemId;
		$output['innerId'] = $innerId;
		$output['fields'] = $blocks;

		return $output;
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
		return wp_update_post([
			'ID' => $formId,
			'post_content' => wp_slash($content),
		 ]);
	}

	/**
	 * Prepare every attribute for later usage in diff.
	 *
	 * @param array $attributes Array of all component attributes.
	 * @param string $component Component name for attributes.
	 *
	 * @return array
	 */
	private function prepareBlockAttributes(array $attributes, string $component): array
	{
		$output = [];

		$nestedKeys = array_flip(AbstractFormBuilder::NESTED_KEYS);

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
				$value = array_values(array_map(
					static function($item) use ($component) {
						return "{$component}" . ucfirst($item);
					},
					$value
				));
			}

			$output[$component . ucfirst($key)] = $value;
		}

		return $output;
	}

	/**
	 * Get Block attribute prefix from full block name.
	 *
	 * @param string $name Block name to check.
	 *
	 * @return array
	 */
	private function getBlockAttributePrefixByFullBlockName(string $name): array
	{
		$block = \explode('/', $name);
		$blockName = \end($block);

		$component = Components::kebabToCamelCase($blockName, '-');

		return [
			'namespace' => $block[0],
			'component' => $blockName,
			'prefix' => "{$component}" . ucfirst($component),
		];
	}

	/**
	 * General inner blocks key name from component value atrribute.
	 *
	 * @param string $prefix Component prefix.
	 * @param array $attributes Array of all component attributes.
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

		$value = crc32((string) $value);

		return "{$parentName}-{$value}";
	}
}
