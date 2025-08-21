<?php

/**
 * The file that defines the project entry point class.
 *
 * A class definition that includes attributes and functions used across both the
 * public side of the site and the admin area.
 *
 * @package EightshiftForms\Config
 */

declare(strict_types=1);

namespace EightshiftForms\Config;

/**
 * The project config class.
 */
class Config
{
	/**
	 * Main plugin slug name.
	 *
	 * @var string
	 */
	public const MAIN_PLUGIN_PROJECT_SLUG = 'eightshift-forms';

	/**
	 * Main plugin file name.
	 *
	 * @var string
	 */
	public const MAIN_PLUGIN_FILE_NAME = 'eightshift-forms.php';

	/**
	 * Main plugin folder name.
	 *
	 * @var string
	 */
	public const MAIN_PLUGIN_FOLDER_NAME = self::MAIN_PLUGIN_PROJECT_SLUG;

	/**
	 * Main plugin full name.
	 *
	 * @var string
	 */
	public const MAIN_PLUGIN_FULL_NAME = self::MAIN_PLUGIN_FOLDER_NAME . \DIRECTORY_SEPARATOR . self::MAIN_PLUGIN_FILE_NAME;

	// ------------------------------------------------------------------
	// FILTERS
	// ------------------------------------------------------------------

	/**
	 * Prefix added to all filters.
	 *
	 * @var string
	 */
	public const FILTER_PREFIX = 'es_forms';

	/**
	 * Filter name triggered when main forms plugins is loaded.
	 *
	 * @var string
	 */
	public const FILTER_LOADED_NAME = self::FILTER_PREFIX . '_loaded';

	/**
	 * Filter name for settings builder.
	 *
	 * @var string
	 */
	public const FILTER_SETTINGS_DATA = self::FILTER_PREFIX . '_settings_data';

	/**
	 * Constant name for all public filters set on the global variable.
	 *
	 * @var string
	 */
	public const PUBLIC_FILTERS_NAME = 'filters';

	/**
	 * Constant name for all public actions set on the global variable.
	 *
	 * @var string
	 */
	public const PUBLIC_ACTIONS_NAME = 'actions';

	/**
	 * Constant name for all public none translatable names set on the global variable.
	 *
	 * @var string
	 */
	public const PUBLIC_NONE_TRANSLATABLE_NAMES_NAME = 'noneTranslatableNames';

	// ------------------------------------------------------------------
	// BLOCKS
	// ------------------------------------------------------------------

	/**
	 * Block main category slug
	 *
	 * @var string
	 */
	public const BLOCKS_MAIN_CATEGORY_SLUG = 'eightshift-forms';

	/**
	 * Block add-ons category slug
	 *
	 * @var string
	 */
	public const BLOCKS_ADDONS_CATEGORY_SLUG = 'eightshift-forms-addons';

	// ------------------------------------------------------------------
	// INTEGRATIONS
	// ------------------------------------------------------------------

	/**
	 * Integration type - default.
	 *
	 * @var string
	 */
	public const INTEGRATION_TYPE_DEFAULT = 'default';

	/**
	 * Integration type - no builder.
	 *
	 * @var string
	 */
	public const INTEGRATION_TYPE_NO_BUILDER = 'no-builder';

	/**
	 * Integration type - complex.
	 *
	 * @var string
	 */
	public const INTEGRATION_TYPE_COMPLEX = 'complex';

	// ------------------------------------------------------------------
	// FILE UPLOAD
	// ------------------------------------------------------------------

	/**
	 * File upload temp folder name.
	 *
	 * @var string
	 */
	public const TEMP_UPLOAD_DIR = 'esforms-tmp';

	/**
	 * File upload type name used for admin.
	 *
	 * @var string
	 */
	public const FILE_UPLOAD_ADMIN_TYPE_NAME = 'fileUploadAdmin';

	// ------------------------------------------------------------------
	// WP-CLI
	// ------------------------------------------------------------------

	/**
	 * Main plugin WP-CLI command prefix.
	 *
	 * @var string
	 */
	public const MAIN_PLUGIN_WP_CLI_COMMAND_PREFIX = self::MAIN_PLUGIN_PROJECT_SLUG;

	// ------------------------------------------------------------------
	// Enqueue
	// ------------------------------------------------------------------

	/**
	 * Main plugin enqueue assets prefix.
	 *
	 * @var string
	 */
	public const MAIN_PLUGIN_ENQUEUE_ASSETS_PREFIX = self::MAIN_PLUGIN_PROJECT_SLUG;

	// ------------------------------------------------------------------
	// Manifest
	// ------------------------------------------------------------------

	/**
	 * Main plugin manifest cache name.
	 *
	 * @var string
	 */
	public const MAIN_PLUGIN_MANIFEST_CACHE_NAME = 'es_forms';

	// ------------------------------------------------------------------
	// DEVELOPER
	// ------------------------------------------------------------------

	/**
	 * Debug filter is debug active key.
	 *
	 * @var string
	 */
	public const FILTER_SETTINGS_IS_DEBUG_ACTIVE = 'es_forms_settings_is_debug_active';

	/**
	 * Debug settings name - debug mode.
	 *
	 * @var string
	 */
	public const SETTINGS_DEBUG_DEBUGGING_KEY = 'troubleshooting-debugging';

	/**
	 * Debug settings name - skip validation mode.
	 *
	 * @var string
	 */
	public const SETTINGS_DEBUG_SKIP_VALIDATION_KEY = 'skip-validation';

	/**
	 * Debug settings name - skip form reset mode.
	 *
	 * @var string
	 */
	public const SETTINGS_DEBUG_SKIP_RESET_KEY = 'skip-reset';

	/**
	 * Debug settings name - skip captcha mode.
	 *
	 * @var string
	 */
	public const SETTINGS_DEBUG_SKIP_CAPTCHA_KEY = 'skip-captcha';

	/**
	 * Debug settings name - skip forms sync mode.
	 *
	 * @var string
	 */
	public const SETTINGS_DEBUG_SKIP_FORMS_SYNC_KEY = 'skip-forms-sync';

	/**
	 * Debug settings name - skip cache mode.
	 *
	 * @var string
	 */
	public const SETTINGS_DEBUG_SKIP_CACHE_KEY = 'skip-cache';

	/**
	 * Debug settings name - developer mode.
	 *
	 * @var string
	 */
	public const SETTINGS_DEBUG_DEVELOPER_MODE_KEY = 'developer-mode';

	// ------------------------------------------------------------------
	// SETTINGS TYPES
	// ------------------------------------------------------------------

	/**
	 * Settings name prefix.
	 *
	 * @var string
	 */
	public const SETTINGS_NAME_PREFIX = 'es-forms';

	/**
	 * Setting type name.
	 *
	 * @var string
	 */
	public const SETTINGS_TYPE_NAME = 'settings';

	/**
	 * Setting global type name.
	 *
	 * @var string
	 */
	public const SETTINGS_GLOBAL_TYPE_NAME = 'settingsGlobal';

	/**
	 * Settings internal types - general.
	 *
	 * @var string
	 */
	public const SETTINGS_INTERNAL_TYPE_GENERAL = 'sidebar-general';

	/**
	 * Settings internal types - integration.
	 *
	 * @var string
	 */
	public const SETTINGS_INTERNAL_TYPE_INTEGRATION = 'sidebar-integration';

	/**
	 * Settings internal types - troubleshooting.
	 *
	 * @var string
	 */
	public const SETTINGS_INTERNAL_TYPE_TROUBLESHOOTING = 'sidebar-troubleshooting';

	/**
	 * Settings internal types - miscellaneous.
	 *
	 * @var string
	 */
	public const SETTINGS_INTERNAL_TYPE_MISCELLANEOUS = 'sidebar-miscellaneous';

	/**
	 * Settings internal types - advanced.
	 *
	 * @var string
	 */
	public const SETTINGS_INTERNAL_TYPE_ADVANCED = 'sidebar-advanced';

	/**
	 * Settings internal types - addon.
	 *
	 * @var string
	 */
	public const SETTINGS_INTERNAL_TYPE_ADDON = 'sidebar-addon';

	// ------------------------------------------------------------------
	// POST TYPE AND SLUG
	// ------------------------------------------------------------------

	/**
	 * Post type name.
	 *
	 * @var string
	 */
	public const SLUG_POST_TYPE = self::MAIN_PLUGIN_PROJECT_SLUG;

	/**
	 * Post type name - result output.
	 *
	 * @var string
	 */
	public const SLUG_RESULT_POST_TYPE = self::MAIN_PLUGIN_PROJECT_SLUG . '-res';

	/**
	 * Slug name for admin prefix.
	 *
	 * @var string
	 */
	public const SLUG_ADMIN = 'es-forms';

	/**
	 * Slug page name for settings page.
	 *
	 * @var string
	 */
	public const SLUG_ADMIN_SETTINGS = 'es-settings';

	/**
	 * Slug page name for settings global page.
	 *
	 * @var string
	 */
	public const SLUG_ADMIN_SETTINGS_GLOBAL = 'es-settings-global';

	/**
	 * Slug page name for results page.
	 *
	 * @var string
	 */
	public const SLUG_ADMIN_RESULTS = 'es-settings-results';

	/**
	 * Slug page name for global settings dashboard page.
	 *
	 * @var string
	 */
	public const SLUG_ADMIN_DASHBOARD = 'dashboard';


	/**
	 * Slug page name for listing type page.
	 *
	 * @var string
	 */
	public const SLUG_ADMIN_LISTING_ENTRIES = 'entries';
	public const SLUG_ADMIN_LISTING_TRASH = 'trash';
	public const SLUG_ADMIN_LISTING_RESULTS = 'results';
	public const SLUG_ADMIN_LISTING_LOCATIONS = 'locations';
	public const SLUG_ADMIN_LISTING_ACTIVITY_LOGS = 'activity-logs';

	// ------------------------------------------------------------------
	// REST API
	// ------------------------------------------------------------------

	/**
	 * Dynamic name route prefix for integrations items inner.
	 *
	 * @var string
	 */
	public const ROUTE_PREFIX_INTEGRATION_ITEMS_INNER = 'integration-items-inner';

	/**
	 * Dynamic name route prefix for integrations items.
	 *
	 * @var string
	 */
	public const ROUTE_PREFIX_INTEGRATION_ITEMS = 'integration-items';

	/**
	 * Dynamic name route prefix for form submit.
	 *
	 * @var string
	 */
	public const ROUTE_PREFIX_FORM_SUBMIT = 'submit';

	/**
	 * Dynamic name route prefix for integration editor.
	 *
	 * @var string
	 */
	public const ROUTE_PREFIX_INTEGRATION_EDITOR = 'integration-editor';

	/**
	 * Dynamic name route prefix for test api.
	 *
	 * @var string
	 */
	public const ROUTE_PREFIX_TEST_API = 'test-api';

	/**
	 * Delimiter used in checkboxes and multiple items.
	 *
	 * @var string
	 */
	public const DELIMITER = '---';

	/**
	 * Status error const.
	 *
	 * @var string
	 */
	public const STATUS_ERROR = 'error';

	/**
	 * Status success const.
	 *
	 * @var string
	 */
	public const STATUS_SUCCESS = 'success';

	/**
	 * Status warning const.
	 *
	 * @var string
	 */
	public const STATUS_WARNING = 'warning';

	/**
	 * Routes namespace.
	 *
	 * @var string
	 */
	public const ROUTE_NAMESPACE = 'eightshift-forms';

	/**
	 * Routes version number.
	 *
	 * @var string
	 */
	public const ROUTE_VERSION = 'v1';

	public const API_RESPONSE_CODE_SUCCESS = 200;

	// ------------------------------------------------------------------
	// Form details keys
	// ------------------------------------------------------------------

	public const FD_ITEM_ID = 'itemId';
	public const FD_INNER_ID = 'innerId';
	public const FD_TYPE = 'type';
	public const FD_INTEGRATION_TYPE = 'integrationType';
	public const FD_FORM_ID = 'formId';
	public const FD_POST_ID = 'postId';
	public const FD_PARAMS = 'params';
	public const FD_FILES = 'files';
	public const FD_SETTINGS_TYPE = 'settingsType';
	public const FD_FIELDS_ONLY = 'fieldsOnly';
	public const FD_FILES_UPLOAD = 'filesUpload';
	public const FD_ACTION = 'action';
	public const FD_SECURE_DATA = 'secureData';
	public const FD_ACTION_EXTERNAL = 'actionExternal';
	public const FD_API_STEPS = 'apiSteps';
	public const FD_CAPTCHA = 'captcha';
	public const FD_STORAGE = 'storage';
	public const FD_IS_VALID = 'isValid';
	public const FD_IS_API_VALID = 'isApiValid';
	public const FD_LABEL = 'label';
	public const FD_ICON = 'icon';
	public const FD_FIELDS = 'fields';
	public const FD_FIELD_NAMES = 'fieldNames';
	public const FD_FIELD_NAMES_FULL = 'fieldNamesFull';
	public const FD_STEPS_SETUP = 'stepsSetup';
	public const FD_RESPONSE_OUTPUT_DATA = 'responseOutputData';
	public const FD_PARAMS_ORIGINAL = 'paramsOriginal';
	public const FD_COUNTRY = 'country';

	// ------------------------------------------------------------------
	// Integration API response details data Keys
	// ------------------------------------------------------------------

	public const IARD_TYPE = self::FD_TYPE;
	public const IARD_STATUS = 'status';
	public const IARD_MSG = 'message';
	public const IARD_PARAMS = self::FD_PARAMS;
	public const IARD_FILES = self::FD_FILES;
	public const IARD_RESPONSE = 'response';
	public const IARD_CODE = 'code';
	public const IARD_BODY = 'body';
	public const IARD_URL = 'url';
	public const IARD_ITEM_ID = self::FD_ITEM_ID;
	public const IARD_FORM_ID = self::FD_FORM_ID;
	public const IARD_IS_DISABLED = 'isDisabled';
	public const IARD_VALIDATION = 'validation';

	// ------------------------------------------------------------------
	// CAPS
	// ------------------------------------------------------------------

	/**
	 * Cap for listing page.
	 *
	 * @var string
	 */
	public const CAP_LISTING = 'eightshift_forms_adminu_menu';

	/**
	 * Cap for settings page.
	 *
	 * @var string
	 */
	public const CAP_SETTINGS = 'eightshift_forms_form_settings';

	/**
	 * Cap for global settings page.
	 *
	 * @var string
	 */
	public const CAP_SETTINGS_GLOBAL = 'eightshift_forms_global_settings';

	/**
	 * Cap for listing page.
	 *
	 * @var string
	 */
	public const CAP_RESULTS = 'eightshift_forms_results';

	/**
	 * Caps for block editor page - forms.
	 *
	 * @var string
	 */
	public const CAP_FORM = 'eightshift_forms';
	public const CAP_FORM_EDIT = 'edit_eightshift_forms';
	public const CAP_FORM_READ = 'read_eightshift_forms';
	public const CAP_FORM_DELETE = 'delete_eightshift_forms';
	public const CAP_FORM_EDIT_MULTIPLE = 'edit_eightshift_formss';
	public const CAP_FORM_EDIT_OTHERS = 'edit_others_eightshift_formss';
	public const CAP_FORM_DELETE_MULTIPLE = 'delete_eightshift_formss';
	public const CAP_FORM_PUBLISH = 'publish_eightshift_formss';
	public const CAP_FORM_READ_PRIVATE = 'read_private_eightshift_formss';

	/**
	 * Caps for block editor page - results.
	 *
	 * @var string
	 */
	public const CAP_FORM_RESULT = 'eightshift_forms_result';
	public const CAP_FORM_RESULT_EDIT = 'edit_eightshift_forms_result';
	public const CAP_FORM_RESULT_READ = 'read_eightshift_forms_result';
	public const CAP_FORM_RESULT_DELETE = 'delete_eightshift_forms_result';
	public const CAP_FORM_RESULT_EDIT_MULTIPLE = 'edit_eightshift_forms_results';
	public const CAP_FORM_RESULT_EDIT_OTHERS = 'edit_others_eightshift_forms_results';
	public const CAP_FORM_RESULT_DELETE_MULTIPLE = 'delete_eightshift_forms_results';
	public const CAP_FORM_RESULT_PUBLISH = 'publish_eightshift_forms_results';
	public const CAP_FORM_RESULT_READ_PRIVATE = 'read_private_eightshift_forms_results';

	/**
	 * Capability list.
	 *
	 * @var array<string>
	 */
	public const CAPS = [
		self::CAP_LISTING,
		self::CAP_SETTINGS,
		self::CAP_SETTINGS_GLOBAL,
		self::CAP_RESULTS,
		self::CAP_FORM,
		self::CAP_FORM_EDIT,
		self::CAP_FORM_READ,
		self::CAP_FORM_DELETE,
		self::CAP_FORM_EDIT_MULTIPLE,
		self::CAP_FORM_EDIT_OTHERS,
		self::CAP_FORM_DELETE_MULTIPLE,
		self::CAP_FORM_PUBLISH,
		self::CAP_FORM_READ_PRIVATE,
		self::CAP_FORM_RESULT,
		self::CAP_FORM_RESULT_EDIT,
		self::CAP_FORM_RESULT_READ,
		self::CAP_FORM_RESULT_DELETE,
		self::CAP_FORM_RESULT_EDIT_MULTIPLE,
		self::CAP_FORM_RESULT_EDIT_OTHERS,
		self::CAP_FORM_RESULT_DELETE_MULTIPLE,
		self::CAP_FORM_RESULT_PUBLISH,
		self::CAP_FORM_RESULT_READ_PRIVATE,
	];
}
