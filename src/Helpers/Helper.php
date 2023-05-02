<?php

/**
 * Class that holds all generic helpers.
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

use EightshiftForms\AdminMenus\FormGlobalSettingsAdminSubMenu;
use EightshiftForms\AdminMenus\FormSettingsAdminSubMenu;
use EightshiftForms\AdminMenus\FormListingAdminSubMenu;
use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\ActiveCampaign\SettingsActiveCampaign;
use EightshiftForms\Integrations\Jira\SettingsJira;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\Settings\SettingsDashboard;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

/**
 * Helper class.
 */
class Helper
{
	/**
	 * Encript method.
	 *
	 * @param string $value Value used.
	 * @param string $action Action used.
	 *
	 * @return string|bool
	 */
	public static function encryptor(string $value, string $action = 'encrypt')
	{
		$encryptMethod = "AES-256-CBC";
		$secretKey = \wp_salt(); // user define private key.
		$secretIv = \wp_salt('SECURE_AUTH_KEY'); // user define secret key.
		$key = \hash('sha256', $secretKey);
		$iv = \substr(\hash('sha256', $secretIv), 0, 16); // sha256 is hash_hmac_algo.

		if ($action === 'encrypt') {
			$output = \openssl_encrypt($value, $encryptMethod, $key, 0, $iv);

			return \base64_encode((string) $output); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		}

		return \openssl_decrypt(\base64_decode($value), $encryptMethod, $key, 0, $iv); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	}

	/**
	 * Decrypt method.
	 *
	 * @param string $value Value used.
	 *
	 * @return string|bool
	 */
	public static function decryptor(string $value)
	{
		return self::encryptor($value, 'decryptor');
	}

	/**
	 * Method that returns listing page url.
	 *
	 * @return string
	 */
	public static function getListingPageUrl(): string
	{
		$page = FormListingAdminSubMenu::ADMIN_MENU_SLUG;

		return \get_admin_url(null, "admin.php?page={$page}");
	}

	/**
	 * Method that returns form settings page url.
	 *
	 * @param string $formId Form ID.
	 * @param string $type Type key.
	 *
	 * @return string
	 */
	public static function getSettingsPageUrl(string $formId, string $type = SettingsGeneral::SETTINGS_TYPE_KEY): string
	{
		$page = FormSettingsAdminSubMenu::ADMIN_MENU_SLUG;
		$typeKey = '';

		if (!empty($type)) {
			$typeKey = "&type={$type}";
		}

		return \get_admin_url(null, "admin.php?page={$page}&formId={$formId}{$typeKey}");
	}

	/**
	 * Method that returns form settings global page url.
	 *
	 * @param string $type Type key.
	 *
	 * @return string
	 */
	public static function getSettingsGlobalPageUrl(string $type = SettingsDashboard::SETTINGS_TYPE_KEY): string
	{
		$page = FormGlobalSettingsAdminSubMenu::ADMIN_MENU_SLUG;
		$typeKey = '';

		if (!empty($type)) {
			$typeKey = "&type={$type}";
		}

		return \get_admin_url(null, "admin.php?page={$page}{$typeKey}");
	}

	/**
	 * Method that returns new form page url.
	 *
	 * @return string
	 */
	public static function getNewFormPageUrl(): string
	{
		$postType = Forms::POST_TYPE_SLUG;

		return \get_admin_url(null, "post-new.php?post_type={$postType}");
	}

	/**
	 * Method that returns trash page url.
	 *
	 * @return string
	 */
	public static function getFormsTrashPageUrl(): string
	{
		return self::getListingPageUrl() . '&type=trash';
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
	 * Method that returns form trash action url.
	 *
	 * @param string $formId Form ID.
	 * @param bool $permanent Permanently delete.
	 *
	 * @return string
	 */
	public static function getFormTrashActionUrl(string $formId, bool $permanent = false): string
	{
		return \get_delete_post_link((int) $formId, '', $permanent);
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
	 * Provide error log output to a custom log file.
	 *
	 * @param array<mixed> $data Data array to output.
	 *
	 * @return void
	 */
	public static function logger(array $data): void
	{
		$wpContentDir = \defined('WP_CONTENT_DIR') ? \WP_CONTENT_DIR : '';

		if (!empty($wpContentDir)) {
			$data['time'] = \gmdate("Y-m-d H:i:s");

			if (isset($data['files'])) {
				unset($data['files']);
			}

			$filterName = Filters::getFilterName(['troubleshooting', 'outputLog']);

			if (\has_filter($filterName)) {
				\apply_filters($filterName, $data);
			} else {
				\error_log((string) \wp_json_encode($data) . "\n -------------------------------------", 3, $wpContentDir . '/eightshift-forms-debug.log'); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}
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
			$output[] = [
				'label' => Helper::minifyString($match[2] ?? ''),
				'value' => Helper::minifyString($match[1] ?? ''),
				'original' => $match[0] ?? '',
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
			'nameAttr' => Components::kebabToCamelCase($blockName),
		];
	}

	/**
	 * Convert camel to snake case
	 *
	 * @param string $input Name to change.
	 *
	 * @return string
	 */
	public static function camelToSnakeCase($input): string
	{
		return \strtolower((string) \preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
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
	 * Get current form content from the database and do prepare output.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<string, mixed>
	 */
	public static function getFormDetailsById(string $formId): array
	{
		$output = [
			'formId' => $formId,
			'isValid' => false,
			'isApiValid' => false,
			'label' => '',
			'icon' => '',
			'type' => '',
			'typeFilter' => '',
			'itemId' => '',
			'innerId' => '',
			'fields' => [],
			'fieldsOnly' => [],
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
		$type = $blockName['nameAttr'];

		$output['type'] = $type;
		$output['typeFilter'] = $blockName['name'];
		$output['label'] = Filters::getSettingsLabels($type, 'title');
		$output['icon'] = Helper::getProjectIcons($type);
		$output['itemId'] = $blocks['innerBlocks'][0]['attrs']["{$type}IntegrationId"] ?? '';
		$output['innerId'] = $blocks['innerBlocks'][0]['attrs']["{$type}IntegrationInnerId"] ?? '';
		$output['fields'] = $blocks;
		$output['fieldsOnly'] = $blocks['innerBlocks'][0]['innerBlocks'] ?? [];

		switch ($output['typeFilter']) {
			case SettingsActiveCampaign::SETTINGS_TYPE_KEY:
				if ($output['itemId'] && $output['type'] && $output['innerId']) {
					$output['isValid'] = true;

					if ($output['fieldsOnly']) {
						$output['isApiValid'] = true;
					}
				}
				break;
			case SettingsMailer::SETTINGS_TYPE_KEY:
			case SettingsJira::SETTINGS_TYPE_KEY:
				if ($output['type']) {
					$output['isValid'] = true;

					if ($output['fieldsOnly']) {
						$output['isApiValid'] = true;
					}
				}
				break;
			default:
				if ($output['itemId'] && $output['type']) {
					$output['isValid'] = true;

					if ($output['fieldsOnly']) {
						$output['isApiValid'] = true;
					}
				}
				break;
		}

		$output['fieldNames'] = \array_values(\array_filter(\array_map(
			static function ($item) {
				$blockItemName = self::getBlockNameDetails($item['blockName'])['nameAttr'];
				$value = $item['attrs'][Components::kebabToCamelCase("{$blockItemName}-{$blockItemName}-Name")] ?? '';

				if ($value) {
					return $value;
				}
			},
			$output['fieldsOnly']
		)));

		return $output;
	}

	/**
	 * Convert all special characters in attributes.
	 * Logic got from the core `serialize_block_attributes` function.
	 *
	 * @param string $attribute Attribute value to check.
	 *
	 * @return string
	 */
	public static function unserializeAttributes(string $attribute): string
	{
		$attribute = \preg_replace('/\u002d\u002d/', '--', $attribute);
		$attribute = \preg_replace('/\u003c/', '<', $attribute);
		$attribute = \preg_replace('/\u003e/', '>', $attribute);
		$attribute = \preg_replace('/\u0026/', '&', $attribute);
		// Regex: /\\"/.
		$attribute = \preg_replace('/\u0022/', '"', $attribute);

		return $attribute;
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
	 * Remove unecesery custom params.
	 *
	 * @param array<string, mixed> $params Params to check.
	 * @param array<int, string> $additional Additional keys to remove.
	 *
	 * @return array<string, mixed>
	 */
	public static function removeUneceseryParamFields(array $params, array $additional = []): array
	{
		$customFields = \array_flip(Components::flattenArray(AbstractBaseRoute::CUSTOM_FORM_PARAMS));
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
	 *
	 * @return array<string, mixed>
	 */
	public static function prepareGenericParamsOutput(array $params): array
	{
		$output = [];

		foreach ($params as $key => $param) {
			$value = $param['value'] ?? '';
			if (!$value) {
				continue;
			}

			$name = $param['name'] ?? '';
			if (!$name) {
				continue;
			}

			$output[$name] = $value;
		}

		return $output;
	}

	/**
	 * Return files from data folder.
	 *
	 * @param string $type Folder name.
	 * @param string $file File name with ext.
	 *
	 * @return array<mixed>
	 */
	public static function getDataManifest(string $type, string $file = 'manifest.json'): array
	{
		$path = self::getDataManifestRaw($type, $file);

		if ($path) {
			return \json_decode($path, true);
		}

		return [];
	}

	/**
	 * Return files from data folder in raw format.
	 *
	 * @param string $type Folder name.
	 * @param string $file File name with ext.
	 *
	 * @return string
	 */
	public static function getDataManifestRaw(string $type, string $file = 'manifest.json'): string
	{
		$path = self::getDataManifestPath($type, $file);

		if (\file_exists($path)) {
			return \file_get_contents($path); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		}

		return '';
	}

	/**
	 * Return files full path.
	 *
	 * @param string $type Folder name.
	 * @param string $file File name with ext.
	 *
	 * @return string
	 */
	public static function getDataManifestPath(string $type, string $file = 'manifest.json'): string
	{
		return \dirname(__FILE__, 3) . "/data/{$type}/{$file}";
	}

	/**
	 * Return counries filtered by some key for multiple usages.
	 *
	 * @return array<int, array<int, string>>
	 */
	public static function getCountrySelectList(): array
	{
		return self::getDataManifest('country');
	}

	/**
	 * Output additional content from filter by block.
	 *
	 * @param string $name Name of the block/component.
	 * @param array<string, mixed> $attributes To load in filter.
	 *
	 * @return string
	 */
	public static function getBlockAdditionalContentViaFilter(string $name, array $attributes): string
	{
		$filterName = Filters::getFilterName(['block', $name, 'additionalContent']);
		if (\has_filter($filterName)) {
			return \apply_filters($filterName, $attributes);
		}

		return '';
	}

	/**
	 * Return project icons from utils component.
	 *
	 * @param string $type Type to return.
	 *
	 * @return string
	 */
	public static function getProjectIcons(string $type): string
	{
		return Components::getComponent('utils')['icons'][Components::kebabToCamelCase($type)] ?? '';
	}

	/**
	 * Find array value by key in recursive array.
	 *
	 * @param array<mixed> $array Array to find.
	 * @param string $needle Key name to find.
	 *
	 * @return array<int, string>
	 */
	public static function recursiveFind(array $array, string $needle): array
	{
		$iterator  = new RecursiveArrayIterator($array);
		$recursive = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
		$aHitList = [];

		foreach ($recursive as $key => $value) {
			if ($key === $needle) {
				\array_push($aHitList, $value);
			}
		}

		return $aHitList;
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
				$slug = $item[1] ?? '';
				$label = $item[2] ?? '';

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
}
