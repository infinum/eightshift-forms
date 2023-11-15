<?php

/**
 * The class register route for versions migration endpoint
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\Blocks\SettingsBlocks;
use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\ActiveCampaign\SettingsActiveCampaign;
use EightshiftForms\Integrations\Airtable\SettingsAirtable;
use EightshiftForms\Integrations\Clearbit\SettingsClearbit;
use EightshiftForms\Integrations\Goodbits\SettingsGoodbits;
use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;
use EightshiftForms\Integrations\Hubspot\SettingsHubspot;
use EightshiftForms\Integrations\IntegrationSyncInterface;
use EightshiftForms\Integrations\Mailchimp\SettingsMailchimp;
use EightshiftForms\Integrations\Mailerlite\SettingsMailerlite;
use EightshiftForms\Integrations\Moments\SettingsMoments;
use EightshiftForms\Integrations\Workable\SettingsWorkable;
use EightshiftForms\Migration\MigrationHelper;
use EightshiftForms\Migration\SettingsMigration;
use EightshiftForms\Rest\ApiHelper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\Settings\Settings;
use EightshiftForms\Settings\Settings\SettingsSettings;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftForms\Troubleshooting\SettingsFallback;
use EightshiftForms\Validation\ValidatorInterface;
use WP_Query;
use WP_REST_Request;

/**
 * Class MigrationRoute
 */
class MigrationRoute extends AbstractBaseRoute
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Use API helper trait.
	 */
	use ApiHelper;

	/**
	 * Use Migration helper trait.
	 */
	use MigrationHelper;

	/**
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	protected $validator;

	/**
	 * Instance variable for HubSpot form data.
	 *
	 * @var IntegrationSyncInterface
	 */
	protected $integrationSyncDiff;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 * @param IntegrationSyncInterface $integrationSyncDiff Inject IntegrationSyncDiff which holds sync data.
	 */
	public function __construct(
		ValidatorInterface $validator,
		IntegrationSyncInterface $integrationSyncDiff
	) {
		$this->validator = $validator;
		$this->integrationSyncDiff = $integrationSyncDiff;
	}

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'migration';

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return self::ROUTE_SLUG;
	}

	/**
	 * Get callback arguments array
	 *
	 * @return array<string, mixed> Either an array of options for the endpoint, or an array of arrays for multiple methods.
	 */
	protected function getCallbackArguments(): array
	{
		return [
			'methods' => $this->getMethods(),
			'callback' => [$this, 'routeCallback'],
			'permission_callback' => [$this, 'permissionCallback'],
		];
	}

	/**
	 * Method that returns rest response
	 *
	 * @param WP_REST_Request $request Data got from endpoint url.
	 *
	 * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function routeCallback(WP_REST_Request $request)
	{
		$premission = $this->checkUserPermission();
		if ($premission) {
			return \rest_ensure_response($premission);
		}

		$debug = [
			'request' => $request,
		];

		$params = $this->prepareSimpleApiParams($request, $this->getMethods());

		$type = $params['type'] ?? '';

		switch ($type) {
			case SettingsMigration::VERSION_2_3_GENERAL:
				return $this->getMigration2To3General();
			case SettingsMigration::VERSION_2_3_FORMS:
				return $this->getMigration2To3Forms();
			case SettingsMigration::VERSION_2_3_LOCALE:
				return $this->getMigration2To3Locale();
			case SettingsMigration::VERSION_2_3_LABELS:
				return $this->getMigration2To3Labels();
			default:
				return $this->getApiErrorOutput(
					\__('Migration version type key was not provided or not valid.', 'eightshift-forms'),
					[],
					$debug
				);
		}
	}

	/**
	 * Migration version 2-3 general.
	 *
	 * @return array<string, mixed>
	 */
	private function getMigration2To3General(): array
	{
		$config = [
			'options' => [
				'new' => SettingsFallback::SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY,
				'use' => SettingsFallback::SETTINGS_FALLBACK_USE_KEY,
				'old' => 'troubleshooting-fallback-email',
			],
		];

		// Migrate global fallback.
		$globalFallback = $this->getOptionValue($config['options']['old']);

		if ($globalFallback) {
			\update_option($this->getOptionName($config['options']['new']), \maybe_unserialize($globalFallback));
			\update_option($this->getOptionName($config['options']['use']), \maybe_unserialize($config['options']['use']));
			\delete_option($this->getOptionName($config['options']['old']));
		}

		// Migrate each integration fallback.
		foreach (Filters::ALL as $key => $value) {
			if ($value['type'] !== Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION) {
				continue;
			}

			$globalIntegrationFallback = $this->getOptionValue($config['options']['old'] . '-' . $key);

			if ($globalIntegrationFallback) {
				\update_option($this->getOptionName($config['options']['new'] . '-' . $key), \maybe_unserialize($globalIntegrationFallback));
				\delete_option($this->getOptionName($config['options']['old'] . '-' . $key));
			}
		}

		$configDelimiter = [
			SettingsClearbit::SETTINGS_CLEARBIT_AVAILABLE_KEYS_KEY,
			SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY,
			SettingsSettings::SETTINGS_GENERAL_DISABLE_SCROLL_KEY,
			SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY,
			SettingsBlocks::SETTINGS_BLOCK_PHONE_OVERRIDE_GLOBAL_SETTINGS_KEY,
			SettingsGreenhouse::SETTINGS_GREENHOUSE_DISABLE_DEFAULT_FIELDS_KEY,
		];

		foreach ($configDelimiter as $key) {
			$option = $this->getOptionValue($key);
			if ($option) {
				$option = \explode(', ', $option);
				$option = \implode(AbstractBaseRoute::DELIMITER, $option);
				\update_option($this->getOptionName($key), \maybe_unserialize($option));
			}
		}

		$actionName = Filters::getFilterName(['migration', 'twoToThreeGeneral']);
		if (\has_action($actionName)) {
			\do_action($actionName, SettingsMigration::VERSION_2_3_GENERAL);
		}

		return $this->getApiSuccessOutput(\__('Migration version 2 to 3 finished with success.', 'eightshift-forms'));
	}

	/**
	 * Migration version 2-3 forms.
	 *
	 * @return array<string, mixed>
	 */
	private function getMigration2To3Forms(): array
	{
		$output = [];

		$theQuery = new WP_Query([
			'post_type' => Forms::POST_TYPE_SLUG,
			'no_found_rows' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'post_status' => 'any',
			'nopaging' => true,
			'posts_per_page' => 5000, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
		]);

		if (!$theQuery->have_posts()) {
			\wp_reset_postdata();
			return $this->getApiErrorOutput(\__('We could not find any forms on your project so no migration necesery.', 'eightshift-forms'));
		}

		while ($theQuery->have_posts()) {
			$theQuery->the_post();

			$id = (string) \get_the_ID();
			$title = \get_the_title();
			$content = \get_the_content();

			if (!$title) {
				$title = \get_post_field('post_name', (int) $id);
			}

			if (!$title) {
				$title = $id;
			}

			$type = Helper::getFormTypeById($id);

			// If there is nothing in the content, skip this form.
			if (!$type) {
				continue;
			}

			// Bailout integrations that are disabled.
			$use = Filters::ALL[$type]['use'] ?? '';

			// Skip deactivated integrations.
			if ($this->isOptionCheckboxChecked($use, $use)) {
				switch ($type) {
					case SettingsHubspot::SETTINGS_TYPE_KEY:
						$preCheck = $this->updateFormIntegration2To3Forms($type, 'item-id', '', $id, $content);

						$output[$id] = [
							'fatal' => $preCheck['fatal'],
							'title' => $title,
							'type' => $type,
							'msg' => $preCheck['msg'],
							'data' => $preCheck['data'],
						];
						break;
					case SettingsGreenhouse::SETTINGS_TYPE_KEY:
					case SettingsWorkable::SETTINGS_TYPE_KEY:
						$preCheck = $this->updateFormIntegration2To3Forms($type, 'job-id', '', $id, $content);

						$output[$id] = [
							'fatal' => $preCheck['fatal'],
							'title' => $title,
							'type' => $type,
							'msg' => $preCheck['msg'],
							'data' => $preCheck['data'],
						];
						break;
					case SettingsMailchimp::SETTINGS_TYPE_KEY:
					case SettingsMailerlite::SETTINGS_TYPE_KEY:
					case SettingsActiveCampaign::SETTINGS_TYPE_KEY:
					case SettingsGoodbits::SETTINGS_TYPE_KEY:
					case SettingsMoments::SETTINGS_TYPE_KEY:
						$preCheck = $this->updateFormIntegration2To3Forms($type, 'list', '', $id, $content);

						$output[$id] = [
							'fatal' => $preCheck['fatal'],
							'title' => $title,
							'type' => $type,
							'msg' => $preCheck['msg'],
							'data' => $preCheck['data'],
						];
						break;
					case SettingsAirtable::SETTINGS_TYPE_KEY:
						$preCheck = $this->updateFormIntegration2To3Forms($type, 'list', 'field', $id, $content);

						$output[$id] = [
							'fatal' => $preCheck['fatal'],
							'title' => $title,
							'type' => $type,
							'msg' => $preCheck['msg'],
							'data' => $preCheck['data'],
						];
						break;
					case 'form': // Legacy blocks for Mailer integrations is called form and not mailer.
						$preCheck = $this->updateFormMailer2To3Forms($content);

						$output[$id] = [
							'fatal' => $preCheck['fatal'],
							'title' => $title,
							'type' => $type,
							'msg' => $preCheck['msg'],
							'data' => $preCheck['data'],
						];
						break;
					default:
						$output[$id] = [
							'fatal' => true,
							'title' => $title,
							'type' => $type,
							'msg' => [\__('Form content is missing type block.', 'eightshift-forms')],
							'data' => [],
						];
						break;
				}

				\delete_option($this->getOptionName("{$type}-clearbit-email-field"));
				\delete_option($this->getOptionName("{$type}-integration-fields"));
			}
		}

		\wp_reset_postdata();

		$outputFatal = [];
		$outputFinal = [
			'fatal' => [],
			'items' => [],
		];

		foreach ($output as $key => $value) {
			if ($value['fatal']) {
				$outputFatal[$key] = [
					'title' => $value['title'],
					'type' => $value['type'],
					'msg' => $value['msg'],
				];

				continue;
			}

			$blockGrammar = \serialize_blocks($value['data']);

			if (!$blockGrammar) {
				$outputFatal[$key] = [
					'title' => $value['title'],
					'type' => $value['type'],
					'msg' => $value['msg'],
				];
				$outputFinal['items'][$key]['msg'][] = \__('Block content is empty after serialize_blocks.', 'eightshift-forms');
				continue;
			}

			unset($value['fatal']);
			unset($value['data']);

			$outputFinal['items'][$key] = $value;

			\wp_update_post([
				'ID' => (int) $key,
				'post_content' => \wp_slash($blockGrammar),
			 ]);
		}

		$outputFinal['fatal'] = $outputFatal;

		$actionName = Filters::getFilterName(['migration', 'twoToThreeForms']);
		if (\has_action($actionName)) {
			\do_action($actionName, SettingsMigration::VERSION_2_3_FORMS);
		}

		if (!$outputFinal['items']) {
			return $this->getApiErrorOutput(
				\__('All forms returned and error. It looks like you allready migrated everything.', 'eightshift-forms'),
				$outputFinal,
			);
		}

		return $this->getApiSuccessOutput(
			\__('Migration version 2 to 3 forms finished with success.', 'eightshift-forms'),
			$outputFinal
		);
	}

	/**
	 * Migration version 2-3 locale.
	 *
	 * @return array<string, mixed>
	 */
	private function getMigration2To3Locale(): array
	{
		global $wpdb;

		$output = [
			'options' => [
				'changed' => [],
				'errors' => [],
			],
			'forms' => [],
		];

		$theQuery = new WP_Query([
			'post_type' => Forms::POST_TYPE_SLUG,
			'no_found_rows' => true,
			'update_post_term_cache' => false,
			'post_status' => 'any',
			'nopaging' => true,
			'posts_per_page' => 5000, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
		]);

		$forms = $theQuery->posts;
		\wp_reset_postdata();

		if ($forms) {
			foreach ($forms as $key => $form) {
				$formId = (int) $form->ID;

				if (!$formId) {
					continue;
				}

				$settings = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->prepare(
						"SELECT meta_key name, meta_value as value
						FROM $wpdb->postmeta
						WHERE post_id=%d
						AND meta_key LIKE '%es-forms-%'", // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
						$form->ID
					)
				);

				foreach ($settings as $setting) {
					$name = $setting->name ?? '';
					$value = $setting->value ?? '';

					if (!$name) {
						$output['forms'][$formId]['errors'][] = $name;
						continue;
					}

					$newName = \str_replace('-en_US', '', $name);

					$newOption = \add_post_meta($formId, $newName, \maybe_unserialize($value), true);

					if ($newOption) {
						$output['forms'][$formId]['changed'][] = $name;
						\delete_post_meta($formId, $name);
					} else {
						$output['forms'][$formId]['errors'][] = $name;
					}
				}
			}
		}

		$options = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			"SELECT option_name as name, option_value as value
				FROM $wpdb->options
				WHERE option_name LIKE '%es-forms-%'"
		);

		if ($options) {
			foreach ($options as $option) {
				$name = $option->name ?? '';
				$value = $option->value ?? '';

				if (!$name) {
					$output['errors'][] = $name;
					continue;
				}

				$newName = \str_replace('-en_US', '', $name);

				$newOption = \add_option($newName, \maybe_unserialize($value));
				if ($newOption) {
					$output['options']['changed'][] = $name;
					\delete_option($name);
				} else {
					$output['options']['errors'][] = $name;
				}
			}
		}

		$actionName = Filters::getFilterName(['migration', 'twoToThreeLocale']);
		if (\has_action($actionName)) {
			\do_action($actionName, SettingsMigration::VERSION_2_3_LOCALE);
		}

		return $this->getApiSuccessOutput(
			\__('Migration version 2 to 3 locale finished with success.', 'eightshift-forms'),
			$output
		);
	}

	/**
	 * Migration version 2-3 labels.
	 *
	 * @return array<string, mixed>
	 */
	private function getMigration2To3Labels(): array
	{
		$output = [];

		$theQuery = new WP_Query([
			'post_type' => Forms::POST_TYPE_SLUG,
			'no_found_rows' => true,
			'update_post_term_cache' => false,
			'post_status' => 'any',
			'nopaging' => true,
			'posts_per_page' => 5000, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
		]);

		// Fields to update.
		$fields = [
			'inputInputFieldHideLabel',
			'textareaTextareaFieldHideLabel',
			'countryCountryFieldHideLabel',
			'dateDateFieldHideLabel',
			'phonePhoneFieldHideLabel',
			'selectSelectFieldHideLabel',
		];

		$forms = $theQuery->posts;
		\wp_reset_postdata();

		if ($forms) {
			foreach ($forms as $form) {
				$formId = (int) $form->ID;
				$content = $form->post_content; // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

				if (!$formId || !$content) {
					continue;
				}

				foreach ($fields as $field) {
					$content = \str_replace("\"{$field}\":true", "\"{$field}\":false", $content);
				}

				$output[] = \wp_update_post([
					'ID' => $formId,
					'post_content' => $content,
				]);
			}
		}

		$actionName = Filters::getFilterName(['migration', 'twoToThreeLabels']);
		if (\has_action($actionName)) {
			\do_action($actionName, SettingsMigration::VERSION_2_3_LABELS);
		}

		return $this->getApiSuccessOutput(
			\__('Migration version 2 to 3 labels finished with success.', 'eightshift-forms'),
			$output
		);
	}
}
