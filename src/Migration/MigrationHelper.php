<?php

/**
 * Trait that holds all migration helpers used in classes.
 *
 * @package EightshiftForms\Migration
 */

declare(strict_types=1);

namespace EightshiftForms\Migration;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

/**
 * MigrationHelper trait.
 */
trait MigrationHelper
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Update mailer forms.
	 *
	 * @version 2-3 forms.
	 *
	 * @param string $content Content of form.
	 *
	 * @return array<string, mixed>
	 */
	private function updateFormMailer2To3Forms(string $content): array
	{
		$output = [
			'msg' => [],
			'fatal' => false,
			'data' => [],
		];

		// Output blocks to array.
		$blocks = \parse_blocks($content);

		// Bailout if content is missing.
		if (!$blocks) {
			$output['msg'][] = \__('Missing block content', 'eightshift-forms');
			$output['fatal'] = true;
			return $output;
		}

		// Get our namespace.
		$namespace = Components::getSettingsNamespace();

		$newBlockName = "{$namespace}/" . SettingsMailer::SETTINGS_TYPE_KEY;

		if (isset($blocks[0]['innerBlocks'][0]['blockName']) && $blocks[0]['innerBlocks'][0]['blockName'] === $newBlockName) {
			$output['msg'][] = \__('Mailer block name exists on block, no need for migration', 'eightshift-forms');
			$output['fatal'] = true;
			return $output;
		}

		// Update block form to mailer name.
		$blocks[0]['innerBlocks'][0]['blockName'] = $newBlockName;

		// Output success.
		$output['data'] = $blocks;
		$output['msg'][] = \__('Success', 'eightshift-forms');

		return $output;
	}

	/**
	 * Update integration forms.
	 *
	 * @version 2-3 forms.
	 *
	 * @param string $type Integration type.
	 * @param string $itemIdKey Integration ID key.
	 * @param string $innerIdKey Integration inner ID key.
	 * @param string $id Form ID.
	 * @param string $content Content of form.
	 *
	 * @return array<string, mixed>
	 */
	private function updateFormIntegration2To3Forms(string $type, string $itemIdKey, string $innerIdKey, string $id, string $content): array
	{
		$output = [
			'msg' => [],
			'fatal' => false,
			'data' => [],
		];

		$itemId = $this->getSettingValue("{$type}-{$itemIdKey}", $id);
		$innerId = $this->getSettingValue("{$type}-{$innerIdKey}", $id);
		$blocks = \parse_blocks($content);
		$integrationFields = $this->prepareIntegrationFields2To3Forms($type, $id);

		if ($innerIdKey) {
			if (isset($blocks[0]['innerBlocks'][0]['attrs']["{$type}IntegrationId"]) && isset($blocks[0]['innerBlocks'][0]['attrs']["{$type}IntegrationInnerId"])) {
				$output['msg'][] = \__('IntegrationId and IntegrationInnerId exists on block, no need for migration', 'eightshift-forms');
				$output['fatal'] = true;
				return $output;
			}
		} else {
			if (isset($blocks[0]['innerBlocks'][0]['attrs']["{$type}IntegrationId"])) {
				$output['msg'][] = \__('IntegrationId exists on block, no need for migration', 'eightshift-forms');
				$output['fatal'] = true;
				return $output;
			}
		}

		$syncForm = $this->integrationSyncDiff->createFormEditor($id, $type, $itemId, $innerId);
		$syncFormOutput = $syncForm['data']['output'] ?? [];
		$syncFormStatus = $syncForm['status'] ?? AbstractBaseRoute::STATUS_ERROR;
		$syncFormDebugType = $syncForm['debugType'] ?? '';

		if (!$itemId) {
			$output['msg'][] = \__('Missing item ID', 'eightshift-forms');
			$output['fatal'] = true;
			return $output;
		}

		if (!$innerId && $innerIdKey) {
			$output['msg'][] = \__('Missing inner ID', 'eightshift-forms');
			$output['fatal'] = true;
			return $output;
		}

		if (!$blocks) {
			$output['msg'][] = \__('Missing block content', 'eightshift-forms');
			$output['fatal'] = true;
			return $output;
		}

		if ($syncFormStatus === AbstractBaseRoute::STATUS_ERROR) {
			// translators: %s will be replaced with the debug type.
			$output['msg'][] = \sprintf(\__("Sync form status is error - %s", 'eightshift-forms'), $syncFormDebugType);
			$output['fatal'] = true;
			return $output;
		}

		if (!$syncFormOutput) {
			// translators: %s will be replaced with the debug type.
			$output['msg'][] = \sprintf(\__("Missing sync form data output - %s", 'eightshift-forms'), $syncFormDebugType);
			$output['fatal'] = true;
			return $output;
		}

		if (!$integrationFields) {
			$output['msg'][] = \__('Missing integration fields', 'eightshift-forms');
		}

		foreach ($syncFormOutput as $key => $block) {
			$blockName = Helper::getBlockNameDetails($block['blockName'])['name'];
			$prefix = Components::kebabToCamelCase("{$blockName}-{$blockName}");
			$name = $block['attrs']["{$prefix}Name"] ?? '';
			$label = $block['attrs']["{$prefix}FieldLabel"] ?? '';

			if (!$name) {
				// translators: %s will be replaced with the block name.
				$output['msg'][] = \sprintf(\__("Missing integration fields name - %s", 'eightshift-forms'), $block['blockName']);
				continue;
			}

			$field = $integrationFields[$name] ?? [];

			if (!$field) {
				// translators: %s will be replaced with block name.
				$output['msg'][] = \sprintf(\__("Missing integration fields name - %s", 'eightshift-forms'), $name);
				continue;
			}

			if (isset($field['use']) && $field['use'] === 'false') {
				$syncFormOutput[$key]['attrs']["{$prefix}FieldUse"] = false;
			}
			if (isset($field['large'])) {
				$syncFormOutput[$key]['attrs']["{$prefix}FieldWidthLarge"] = (int) $field['large'];
			}
			if (isset($field['desktop'])) {
				$syncFormOutput[$key]['attrs']["{$prefix}FieldWidthDesktop"] = (int) $field['desktop'];
			}
			if (isset($field['tablet'])) {
				$syncFormOutput[$key]['attrs']["{$prefix}FieldWidthTablet"] = (int) $field['tablet'];
			}
			if (isset($field['mobile'])) {
				$syncFormOutput[$key]['attrs']["{$prefix}FieldWidthMobile"] = (int) $field['mobile'];
			}
			if (isset($field['field-style'])) {
				$syncFormOutput[$key]['attrs']["{$prefix}FieldStyle"] = $field['field-style'];
			}
			if (isset($field['label'])) {
				if ($blockName === 'submit') {
					$syncFormOutput[$key]['attrs']["{$prefix}Value"] = $field['label'];
				} else {
					$syncFormOutput[$key]['attrs']["{$prefix}FieldLabel"] = $field['label'];
				}
			}
			if (isset($field['file-info-label']) && $field['file-info-label'] === 'true') {
				$syncFormOutput[$key]['attrs']["{$prefix}FieldHideLabel"] = true;
				$syncFormOutput[$key]['attrs']["{$prefix}CustomInfoText"] = $label;
			}
		}

		$blocks[0]['innerBlocks'][0]['attrs'] = [
			"{$type}IntegrationId" => $itemId,
		];
		$blocks[0]['innerBlocks'][0]['innerBlocks'] = $syncFormOutput;
		$blocks[0]['innerBlocks'][0]['innerContent'] = $syncFormOutput;

		$output['data'] = $blocks;
		$output['msg'][] = \__('Success', 'eightshift-forms');

		return $output;
	}

	/**
	 * Prepare integration fields.
	 *
	 * @param string $type Integration type.
	 * @param string $id Form ID.
	 *
	 * @return array<string, mixed>
	 */
	private function prepareIntegrationFields2To3Forms(string $type, string $id): array
	{
		$output = [];

		$integrationFields = $this->getSettingValueGroup("{$type}-integration-fields", $id);

		if (!$integrationFields) {
			return [];
		}

		foreach ($integrationFields as $key => $value) {
			$key = \explode(AbstractBaseRoute::DELIMITER, $key);
			$name = $key[0] ?? '';
			$innerKey = $key[1] ?? '';

			if (!$name || !$innerKey || !$value || $innerKey === 'order') {
				continue;
			}

			$output[$name][$innerKey] = $value;
		}

		return $output;
	}
}
