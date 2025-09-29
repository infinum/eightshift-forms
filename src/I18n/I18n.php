<?php

/**
 * Class that holds all the internationalization functionality.
 *
 * @package EightshiftForms\I18n
 */

declare(strict_types=1);

namespace EightshiftForms\I18n;

use EightshiftForms\Config\Config;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * I18n class.
 */
class I18n implements ServiceInterface
{
	/**
	 * Available languages.
	 */
	public const AVAILABLE_LANGUAGES = [
		'ar' => 'Arabic (Arabic)',
		'en_US' => 'English (United States)',
		'en_GB' => 'English (United Kingdom)',
		'de_DE' => 'German (Germany)',
		'es_ES' => 'Spanish (Spain)',
		'fr_FR' => 'French (France)',
		'hr' => 'Croatian (Croatia)',
		'id_ID' => 'Indonesian (Indonesia)',
		'it_IT' => 'Italian (Italy)',
		'ko_KR' => 'Korean (South Korea)',
		'ms_MY' => 'Malay (Malaysia)',
		'pt_PT' => 'Portuguese (Portugal)',
		'ru_RU' => 'Russian (Russia)',
		'th' => 'Thai (Thailand)',
		'vi_VN' => 'Vietnamese (Vietnam)',
		'zh_CN' => 'Chinese (China)',
		'zh_TW' => 'Chinese (Taiwan)',
	];

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('init', [$this, 'loadTextdomain']);
	}

	/**
	 * Load the plugin text domain.
	 *
	 * @return void
	 */
	public function loadTextdomain(): void
	{
		\load_plugin_textdomain(Config::MAIN_PLUGIN_PROJECT_SLUG, false, \plugin_basename(__DIR__) . '/languages');
	}
}
