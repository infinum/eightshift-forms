<?php

/**
 * Class that holds all generic helpers.
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

use EightshiftForms\Config\Config;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

/**
 * GeneralHelpers class.
 */
final class GeneralHelpers
{
	/**
	 * Method that returns listing page url.
	 *
	 * @param string $type Type key.
	 * @param string $formId Form ID.
	 * @param string $parent Parent key.
	 * @param string $page Top page key.
	 *
	 * @return string
	 */
	public static function getListingPageUrl(string $type = '', string $formId = '', string $parent = '', string $page = Config::SLUG_ADMIN): string
	{
		return \add_query_arg(
			[
				'page' => $page,
				'type' => $type,
				'formId' => $formId,
				'parent' => $parent,
			],
			\get_admin_url(null, "admin.php")
		);
	}

	/**
	 * Method that returns form settings page url.
	 *
	 * @param string $formId Form ID.
	 * @param string $type Type key.
	 *
	 * @return string
	 */
	public static function getSettingsPageUrl(string $formId, string $type): string
	{
		return self::getListingPageUrl($type, $formId, '', Config::SLUG_ADMIN_SETTINGS);
	}

	/**
	 * Method that returns form settings global page url.
	 *
	 * @param string $type Type key.
	 *
	 * @return string
	 */
	public static function getSettingsGlobalPageUrl(string $type): string
	{
		return self::getListingPageUrl($type, '', '', Config::SLUG_ADMIN_SETTINGS_GLOBAL);
	}

	/**
	 * Method that returns new form page url.
	 *
	 * @param string $postType Post type.
	 *
	 * @return string
	 */
	public static function getNewFormPageUrl(string $postType): string
	{
		return \add_query_arg(
			[
				'post_type' => $postType,
			],
			\get_admin_url(null, "post-new.php")
		);
	}

	/**
	 * Method that returns form edit page url.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	public static function getFormEditPageUrl(string $formId): string
	{
		return \get_edit_post_link((int) $formId) ?? '';
	}

	/**
	 * Method that checks if request is a part of the forms.
	 *
	 * @return bool
	 */
	public static function isEightshiftFormsAdminPages(): bool
	{
		$page = isset($_GET['page']) ? \sanitize_text_field(\wp_unslash($_GET['page'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$pages = \array_flip([
			Config::SLUG_ADMIN,
			Config::SLUG_ADMIN_SETTINGS,
			Config::SLUG_ADMIN_SETTINGS_GLOBAL,
		]);

		return isset($pages[$page]) && \is_admin();
	}

	/**
	 * Method that returns form trash action url.
	 *
	 * @param string $formId Form ID.
	 * @param bool $permanent Permanently delete.
	 *
	 * @return string
	 */
	public static function getFormTrashActionUrl(string $formId, bool $permanent = false): string
	{
		return (string) \get_delete_post_link((int) $formId, '', $permanent);
	}

	/**
	 * Method that returns form trash restore action url.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	public static function getFormTrashRestoreActionUrl(string $formId): string
	{
		return \get_admin_url(null, \wp_nonce_url("post.php?post={$formId}&action=untrash", 'untrash-post_' . $formId));
	}

	/**
	 * Check if current page is part of the settings page
	 *
	 * @return boolean
	 */
	public static function isSettingsPage(): bool
	{
		global $plugin_page; // phpcs:ignore Squiz.NamingConventions.ValidVariableName.NotCamelCaps

		return !empty($plugin_page); // phpcs:ignore Squiz.NamingConventions.ValidVariableName.NotCamelCaps
	}

	/**
	 * Minify string
	 *
	 * @param string $string String to check.
	 *
	 * @return string
	 */
	public static function minifyString(string $string): string
	{
		$string = \str_replace(\PHP_EOL, ' ', $string);
		$string = \preg_replace('/[\r\n]+/', "\n", $string);
		return (string) \preg_replace('/[ \t]+/', ' ', (string) $string);
	}


	/**
	 * Convert inner blocks to array
	 *
	 * @param string $string String to convert.
	 * @param string $type Type of content.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function convertInnerBlocksToArray(string $string, string $type): array
	{
		$output = [];

		switch ($type) {
			case 'select':
				$re = '/<option[^>]*value="(.*?)"[^>]*>([^<]*)<\s*\/\s*option\s*>/m';
				break;
			default:
				$re = '';
				break;
		}

		if (!$re) {
			return $output;
		}

		\preg_match_all($re, $string, $matches, \PREG_SET_ORDER, 0);

		if (!$matches) {
			return $output;
		}

		foreach ($matches as $match) {
			$original = $match[0] ?: '';
			$label = $match[2] ?: '';
			$value = $match[1] ?: '';

			$output[] = [
				'label' => self::minifyString($label),
				'value' => self::minifyString($value),
				'original' => $original,
			];
		}

		return $output;
	}

	/**
	 * Return block details depending on the full block name.
	 *
	 * @param string $blockName Block name.
	 *
	 * @return array<string, string>
	 */
	public static function getBlockNameDetails(string $blockName): array
	{
		$block = \explode('/', $blockName);
		$blockName = \end($block);

		return [
			'namespace' => $block[0] ?? '',
			'name' => $blockName,
			'nameAttr' => Helpers::kebabToCamelCase($blockName),
		];
	}

	/**
	 * Output the form type used by checking the post_content and extracting the block used for the integration.
	 *
	 * @param string $formId Form ID to check.
	 *
	 * @return string
	 */
	public static function getFormTypeById(string $formId): string
	{
		$content = \get_post_field('post_content', (int) $formId);

		if (!$content) {
			return '';
		}

		$blocks = \parse_blocks($content);

		if (!$blocks) {
			return '';
		}

		$blockName = $blocks[0]['innerBlocks'][0]['blockName'] ?? '';

		if (!$blockName) {
			return '';
		}

		return self::getBlockNameDetails($blockName)['name'];
	}

	/**
	 * Output the form type used by checking the post_content and extracting the block used for the integration.
	 *
	 * @param string $formId Form ID to check.
	 *
	 * @return string
	 */
	public static function isFormValid(string $formId): string
	{
		$content = \get_post_field('post_content', (int) $formId);

		if (!$content) {
			return '';
		}

		$blocks = \parse_blocks($content);

		if (!$blocks) {
			return '';
		}

		$blockName = $blocks[0]['innerBlocks'][0]['blockName'] ?? '';

		if (!$blockName) {
			return '';
		}

		return self::getBlockNameDetails($blockName)['name'];
	}

	/**
	 * Get full form data by id.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<string, mixed>
	 */
	public static function getFormDetails(string $formId): array
	{
		$output = [
			Config::FD_FORM_ID => $formId,
			Config::FD_IS_VALID => false,
			Config::FD_IS_API_VALID => false,
			Config::FD_LABEL => '',
			Config::FD_ICON => '',
			Config::FD_TYPE => '',
			Config::FD_ITEM_ID => '',
			Config::FD_INNER_ID => '',
			Config::FD_FIELDS => [],
			Config::FD_FIELDS_ONLY => [],
			Config::FD_FIELD_NAMES => [],
			Config::FD_FIELD_NAMES_FULL => [],
			Config::FD_STEPS_SETUP => [],
		];

		$form = \get_post_field('post_content', (int) $formId);

		if (!$form) {
			return $output;
		}

		$blocks = \parse_blocks($form); // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

		if (!$blocks) {
			return $output;
		}

		$blocks = $blocks[0];

		$blockName = $blocks['innerBlocks'][0]['blockName'] ?? '';

		if (!$blockName) {
			return $output;
		}

		$blockName = self::getBlockNameDetails($blockName);
		$namespace = $blockName['namespace'];
		$type = $blockName['nameAttr'];

		$fieldsOnly = $blocks['innerBlocks'][0]['innerBlocks'] ?? [];

		$settings = \apply_filters(Config::FILTER_SETTINGS_DATA, [])[$type] ?? [];

		$output[Config::FD_TYPE] = $type;
		$output[Config::FD_INTEGRATION_TYPE] = $settings['integrationType'] ?? '';
		$output[Config::FD_LABEL] = $settings['labels']['title'] ?? '';
		$output[Config::FD_ICON] = $settings['labels']['icon'] ?? '';
		$output[Config::FD_ITEM_ID] = $blocks['innerBlocks'][0]['attrs']["{$type}IntegrationId"] ?? '';
		$output[Config::FD_INNER_ID] = $blocks['innerBlocks'][0]['attrs']["{$type}IntegrationInnerId"] ?? '';
		$output[Config::FD_FIELDS] = $blocks;
		$output[Config::FD_FIELDS_ONLY] = $fieldsOnly;

		switch ($output[Config::FD_INTEGRATION_TYPE]) {
			case Config::INTEGRATION_TYPE_COMPLEX:
				if ($output[Config::FD_ITEM_ID] && $output[Config::FD_TYPE] && $output[Config::FD_INNER_ID]) {
					$output[Config::FD_IS_VALID] = true;

					if ($output[Config::FD_FIELDS_ONLY]) {
						$output[Config::FD_IS_API_VALID] = true;
					}
				}
				break;
			case Config::INTEGRATION_TYPE_NO_BUILDER:
				if ($output[Config::FD_TYPE]) {
					$output[Config::FD_IS_VALID] = true;

					if ($output[Config::FD_FIELDS_ONLY]) {
						$output[Config::FD_IS_API_VALID] = true;
					}
				}
				break;
			default:
				if ($output[Config::FD_ITEM_ID] && $output[Config::FD_TYPE]) {
					$output[Config::FD_IS_VALID] = true;

					if ($output[Config::FD_FIELDS_ONLY]) {
						$output[Config::FD_IS_API_VALID] = true;
					}
				}
				break;
		}

		$ignoreBlocks = \array_flip([
			'step',
			'submit',
		]);

		foreach ($output[Config::FD_FIELDS_ONLY] as $item) {
			$blockItemName = self::getBlockNameDetails($item['blockName'])['nameAttr'];

			$value = $item['attrs'][Helpers::kebabToCamelCase("{$blockItemName}-{$blockItemName}-Name")] ?? '';

			if (!$value) {
				continue;
			}

			$output[Config::FD_FIELD_NAMES_FULL][] = $value;

			if (isset($ignoreBlocks[$blockItemName])) {
				continue;
			}

			$output[Config::FD_FIELD_NAMES][] = $value;
		}

		// Check if this form uses steps.
		$hasSteps = \array_search($namespace . '/step', \array_column($output[Config::FD_FIELDS_ONLY], 'blockName'), true);
		$hasSteps = $hasSteps !== false;

		if ($hasSteps) {
			$stepCurrent = 'step-init';

			// If the users don't add first step add it to the list.
			if ($output[Config::FD_FIELDS_ONLY][0]['blockName'] !== "{$namespace}/step") {
				\array_unshift(
					$output[Config::FD_FIELDS_ONLY],
					[
						'blockName' => "{$namespace}/step",
						'attrs' => [
							'stepStepName' => $stepCurrent,
							'stepStepLabel' => \__('Step init', 'eightshift-forms'),
							'stepStepContent' => '',
						],
						'innerBlocks' => [],
						'innerHTML' => '',
						'innerContent' => [],
					],
				);
			}

			foreach ($output[Config::FD_FIELDS_ONLY] as $block) {
				$blockName = self::getBlockNameDetails($block['blockName']);
				$name = $blockName['name'];

				if ($name === 'step') {
					$stepCurrent = $block['attrs'][Helpers::kebabToCamelCase("{$name}-{$name}Name")] ?? '';
					$stepLabel = $block['attrs'][Helpers::kebabToCamelCase("{$name}-{$name}Label")] ?? '';

					if (!$stepLabel) {
						$stepLabel = $stepCurrent;
					}
					$output[Config::FD_STEPS_SETUP]['steps'][$stepCurrent] = [
						'label' => $stepLabel,
						'value' => $stepCurrent,
					];

					continue;
				}

				if ($name === 'submit') {
					continue;
				}

				$itemName = $block['attrs'][Helpers::kebabToCamelCase("{$name}-{$name}Name")] ?? '';
				if (!$itemName) {
					continue;
				}

				$output[Config::FD_STEPS_SETUP]['steps'][$stepCurrent]['subItems'][] = $itemName;
				$output[Config::FD_STEPS_SETUP]['relations'][$itemName] = $stepCurrent;
			}

			$output[Config::FD_STEPS_SETUP]['multiflow'] = $output[Config::FD_FIELDS]['innerBlocks'][0]['attrs']["{$type}StepMultiflowRules"] ?? [];
		}

		return $output;
	}

	/**
	 * Get field details by name.
	 *
	 * @param array<string, mixed> $params Form fields params.
	 * @param string $key Field key.
	 *
	 * @return array<string, mixed>
	 */
	public static function getFieldDetailsByName(array $params, string $key): array
	{
		return \array_values(\array_filter($params, function ($item) use ($key) {
			return isset($item['name']) && $item['name'] === $key;
		}))[0] ?? [];
	}


	/**
	 * Find email field from params sent by form.
	 *
	 * @param array<string, mixed> $params Params to check.
	 *
	 * @return string
	 */
	public static function getEmailParamsField(array $params): string
	{
		$allowed = [
			'email' => 0,
			'e-mail' => 1,
			'mail' => 2,
			'email_address' => 3,
		];

		$field = \array_filter(
			$params,
			static function ($item) use ($allowed) {
				if (isset($allowed[$item['name'] ?? ''])) {
					return true;
				}
			}
		);

		return \reset($field)['value'] ?? '';
	}

	/**
	 * Convert date formats to libs formats.
	 *
	 * @param string $date Date to convert.
	 * @param string $separator Date separator.
	 *
	 * @return string
	 */
	public static function getCorrectLibDateFormats(string $date, string $separator): string
	{
		return \implode(
			$separator,
			\array_map(
				static function ($item) {
					$item = \count_chars($item, 3);

					if ($item === 'Y') {
						return $item;
					}

					return \strtolower($item);
				},
				\explode($separator, $date)
			)
		);
	}

	/**
	 * Prepare generic params output. Used if no specific configurations are needed.
	 *
	 * @param array<string, mixed> $params Params.
	 * @param array<string> $exclude Exclude params.
	 *
	 * @return array<string, mixed>
	 */
	public static function prepareGenericParamsOutput(array $params, array $exclude = []): array
	{
		$output = [];

		$exclude = \array_flip($exclude);

		foreach ($params as $param) {
			$value = $param['value'] ?? '';
			if (!$value) {
				continue;
			}

			$name = $param['name'] ?? '';
			if (!$name) {
				continue;
			}

			if (isset($exclude[$name])) {
				continue;
			}

			$output[$name] = $value;
		}

		return $output;
	}

	/**
	 * Output additional content from filter by block.
	 * Limited to front page only.
	 *
	 * @param string $name Name of the block/component.
	 * @param array<string, mixed> $attributes To load in filter.
	 *
	 * @return string
	 */
	public static function getBlockAdditionalContentViaFilter(string $name, array $attributes): string
	{
		if (\is_admin()) {
			return '';
		}

		if (self::isBlockEditor()) {
			return '';
		}

		$filterName = HooksHelpers::getFilterName(['block', $name, 'additionalContent']);

		if (\has_filter($filterName)) {
			return \apply_filters($filterName, $attributes);
		}

		return '';
	}

	/**
	 * Output select options ass array from html string.
	 *
	 * @param string $options Options string.
	 *
	 * @return array<int, array<string, string>>
	 */
	public static function getSelectOptionsArrayFromString(string $options): array
	{
		$output = \wp_json_encode($options);
		$output = \str_replace('\n\t\t\t', '', $output);
		$output = \str_replace('>\n\t', '>', $output);
		$output = \str_replace('\n\t', ' ', $output);
		$output = \str_replace('\n\t', ' ', $output);
		$output = \trim(\json_decode($output));

		\preg_match_all('/<option value="(.*?)">(.*?)<\/option>/m', $output, $matches, \PREG_SET_ORDER, 0);

		return \array_values(\array_filter(\array_map(
			static function ($item) {
				$slug = $item[1] ?: '';
				$label = $item[2] ?: '';

				if (!$slug || !$label) {
					return false;
				}

				return [
					'slug' => $slug,
					'label' => $label,
				];
			},
			$matches
		)));
	}

	/**
	 * Is block editor page.
	 *
	 * @return boolean
	 */
	public static function isBlockEditor(): bool
	{
		if (!\function_exists('get_current_screen')) {
			return false;
		}

		$currentScreen = \get_current_screen() ?? '';

		if (!\method_exists($currentScreen, 'is_block_editor')) {
			return false;
		}

		return $currentScreen->is_block_editor();
	}

	/**
	 * Remove unnecessary custom params.
	 *
	 * @param array<string, mixed> $params Params to check.
	 * @param array<int, string> $additional Additional keys to remove.
	 *
	 * @return array<string, mixed>
	 */
	public static function removeUnnecessaryParamFields(array $params, array $additional = []): array
	{
		$customFields = \array_flip(Helpers::flattenArray(UtilsHelper::getStateParams()));
		$additional = \array_flip($additional);

		return \array_filter(
			$params,
			static function ($item) use ($customFields, $additional) {
				if (isset($customFields[$item['name'] ?? ''])) {
					return false;
				}

				if ($additional && isset($additional[$item['name'] ?? ''])) {
					return false;
				}

				return true;
			}
		);
	}

	/**
	 * Check if integration can use sync feature.
	 *
	 * @param string $integrationName Integration name.
	 *
	 * @return boolean
	 */
	public static function canIntegrationUseSync(string $integrationName): bool
	{
		return isset(\apply_filters(Config::FILTER_SETTINGS_DATA, [])[$integrationName]['fields']);
	}

	/**
	 * Return all posts where form is assigned.
	 *
	 * @param string $formId Form Id.
	 * @param string $type Type of the form.
	 *
	 * @return array<int, mixed>
	 */
	public static function getBlockLocations(string $formId, string $type): array
	{
		switch ($type) {
			case Config::SLUG_RESULT_POST_TYPE:
				$outputString = "%\"resultOutputPostId\":\"{$formId}\"%";
				break;
			default:
				$outputString = "%\"formsFormPostId\":\"{$formId}\"%";
				break;
		}

		global $wpdb;

		$items = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT ID, post_type, post_title, post_status
				 FROM $wpdb->posts
				 WHERE post_content
				 LIKE %s
				 AND (post_status='publish' OR post_status='draft')
				",
				$outputString
			)
		);

		if (!$items) {
			return [];
		}

		$isDeveloperModeActive = DeveloperHelpers::isDeveloperModeActive();

		return \array_map(
			function ($item) use ($isDeveloperModeActive) {
				$id = $item->ID;
				$title = $item->post_title; // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
				$title = $isDeveloperModeActive ? "{$id} - {$title}" : $title;

				return [
					'id' => $id,
					'postType' => $item->post_type, // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
					'title' => $title,
					'status' => $item->post_status, // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
					'editLink' => self::getFormEditPageUrl((string) $id),
					'viewLink' => \get_permalink($id),
					'activeIntegration' => [
						'isActive' => true,
						'isValid' => true,
						'isApiValid' => true,
					]
				];
			},
			$items
		);
	}

	/**
	 * Get the settings labels and details by type and key.
	 * This method is used to provide the ability to translate all strings.
	 *
	 * @param string $type Settings type from the Settings class.
	 *
	 * @return array<string, string>
	 */
	public static function getSpecialConstants(string $type): array
	{
		$data = [
			'tracking' => [
				'{invalidFieldsString}' => \__('comma-separated list of invalid fields', 'eightshift-forms'),
				'{invalidFieldsArray}' => \__('array of invalid fields', 'eightshift-forms'),
			],
		];
		return isset($data[$type]) ? $data[$type] : [];
	}
}
