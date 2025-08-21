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
use EightshiftForms\Helpers\GeneralHelpers;
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
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Settings\SettingsSettings;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftForms\Troubleshooting\SettingsFallback;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftForms\Config\Config;
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Helpers\HooksHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use WP_Query;

/**
 * Class MigrationRoute
 */
class MigrationRoute extends AbstractSimpleFormSubmit
{
	/**
	 * Use Migration helper trait.
	 */
	use MigrationHelper;

	/**
	 * Instance variable for HubSpot form data.
	 *
	 * @var IntegrationSyncInterface
	 */
	protected $integrationSyncDiff;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param SecurityInterface $security Inject security methods.
	 * @param ValidatorInterface $validator Inject validation methods.
	 * @param LabelsInterface $labels Inject labels.
	 * @param IntegrationSyncInterface $integrationSyncDiff Inject IntegrationSyncDiff which holds sync data.
	 */
	public function __construct(
		SecurityInterface $security,
		ValidatorInterface $validator,
		LabelsInterface $labels,
		IntegrationSyncInterface $integrationSyncDiff
	) {
		$this->security = $security;
		$this->validator = $validator;
		$this->labels = $labels;
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
	 * Check if the route is admin protected.
	 *
	 * @return boolean
	 */
	protected function isRouteAdminProtected(): bool
	{
		return true;
	}

	/**
	 * Get mandatory params.
	 *
	 * @param array<string, mixed> $params Params passed from the request.
	 *
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(array $params): array
	{
		return [
			'type' => 'string',
		];
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $params Prepared params.
	 *
	 * @throws BadRequestException If migration type is not found.
	 *
	 * @return array<string, mixed>
	 */
	protected function submitAction(array $params): array
	{
		$type = $params['type'] ?? '';

		switch ($type) {
			case SettingsMigration::VERSION_2_3_GENERAL:
				return $this->getMigration2To3General();
			case SettingsMigration::VERSION_2_3_FORMS:
				return $this->getMigration2To3Forms();
			case SettingsMigration::VERSION_2_3_LOCALE:
				return $this->getMigration2To3Locale();
			case SettingsMigration::VERSION_CLEARBIT:
				return $this->getMigrationClearbit();
			default:
				// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
				throw new BadRequestException(
					$this->getLabels()->getLabel('migrationTypeNotFound'),
					[
						AbstractBaseRoute::R_DEBUG_KEY => 'migrationTypeNotFound',
					]
				);
				// phpcs:enable
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
		$globalFallback = SettingsHelpers::getOptionValue($config['options']['old']);

		if ($globalFallback) {
			\update_option(SettingsHelpers::getOptionName($config['options']['new']), \maybe_unserialize($globalFallback));
			\update_option(SettingsHelpers::getOptionName($config['options']['use']), \maybe_unserialize($config['options']['use']));
			\delete_option(SettingsHelpers::getOptionName($config['options']['old']));
		}

		// Migrate each integration fallback.
		foreach (\apply_filters(Config::FILTER_SETTINGS_DATA, []) as $key => $value) {
			$type = $value['type'] ?? '';
			if ($type !== Config::SETTINGS_INTERNAL_TYPE_INTEGRATION) {
				continue;
			}

			$globalIntegrationFallback = SettingsHelpers::getOptionValue($config['options']['old'] . '-' . $key);

			if ($globalIntegrationFallback) {
				\update_option(SettingsHelpers::getOptionName($config['options']['new'] . '-' . $key), \maybe_unserialize($globalIntegrationFallback));
				\delete_option(SettingsHelpers::getOptionName($config['options']['old'] . '-' . $key));
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
			$option = SettingsHelpers::getOptionValue($key);
			if ($option) {
				$option = \explode(', ', $option);
				$option = \implode(Config::DELIMITER, $option);
				\update_option(SettingsHelpers::getOptionName($key), \maybe_unserialize($option));
			}
		}

		$actionName = HooksHelpers::getActionName(['migration', 'twoToThreeGeneral']);
		if (\has_action($actionName)) {
			\do_action($actionName, SettingsMigration::VERSION_2_3_GENERAL);
		}

		return [
			AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel('migrationSuccess'),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG_KEY => 'migrationSuccess2To3General',
			],
		];
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
			return ApiHelpers::getApiErrorPublicOutput(\__('We could not find any forms in your project so there is nothing to migrate.', 'eightshift-forms'));
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

			$type = GeneralHelpers::getFormTypeById($id);

			// If there is nothing in the content, skip this form.
			if (!$type) {
				continue;
			}

			// Bailout integrations that are disabled.
			$use = \apply_filters(Config::FILTER_SETTINGS_DATA, [])[$type]['use'] ?? '';

			// Skip deactivated integrations.
			if (SettingsHelpers::isOptionCheckboxChecked($use, $use)) {
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

				\delete_option(SettingsHelpers::getOptionName("{$type}-clearbit-email-field"));
				\delete_option(SettingsHelpers::getOptionName("{$type}-integration-fields"));
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

		$actionName = HooksHelpers::getActionName(['migration', 'twoToThreeForms']);
		if (\has_action($actionName)) {
			\do_action($actionName, SettingsMigration::VERSION_2_3_FORMS);
		}

		if (!$outputFinal['items']) {
			return ApiHelpers::getApiErrorPublicOutput(
				\__('All forms returned and error. It looks like you already migrated everything.', 'eightshift-forms'),
				$outputFinal,
			);
		}

		return [
			AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel('migrationSuccess'),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG_KEY => 'migrationSuccess2To3Forms',
			],
			AbstractBaseRoute::R_DATA => [
				UtilsHelper::getStateResponseOutputKey('adminMigration') => $outputFinal,
			],
		];
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

		$actionName = HooksHelpers::getActionName(['migration', 'twoToThreeLocale']);
		if (\has_action($actionName)) {
			\do_action($actionName, SettingsMigration::VERSION_2_3_LOCALE);
		}

		return [
			AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel('migrationSuccess'),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG_KEY => 'migrationSuccess2To3Locale',
			],
			AbstractBaseRoute::R_DATA => [
				UtilsHelper::getStateResponseOutputKey('adminMigration') => $output,
			],
		];
	}

	/**
	 * Migration version Clearbit
	 *
	 * @return array<string, mixed>
	 */
	private function getMigrationClearbit(): array
	{
		global $wpdb;

		$output = [
			'options' => [],
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
						AND meta_key = 'es-forms-hubspot-use-clearbit'", // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
						$form->ID
					)
				);

				foreach ($settings as $setting) {
					$name = $setting->name ?? '';
					$value = $setting->value ?? '';

					\add_post_meta($formId, 'es-forms-clearbit-settings-use', $value);
					\delete_post_meta($formId, $name);

					$output['forms'][] = $formId;
				}
			}
		}

		$options = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			"SELECT option_name as name, option_value as value
				FROM $wpdb->options
				WHERE option_name = 'es-forms-hubspot-clearbit-map-keys'"
		);

		if ($options) {
			foreach ($options as $option) {
				$name = $option->name ?? '';
				$value = $option->value ?? '';


				\add_option('es-forms-clearbit-map-keys-hubspot', $value);
				\delete_option($name);

				$output['options'][] = $name;
			}
		}

		$actionName = HooksHelpers::getActionName(['migration', 'clearbit']);
		if (\has_action($actionName)) {
			\do_action($actionName, SettingsMigration::VERSION_CLEARBIT);
		}

		return [
			AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel('migrationSuccess'),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG_KEY => 'migrationSuccessClearbit',
			],
			AbstractBaseRoute::R_DATA => [
				UtilsHelper::getStateResponseOutputKey('adminMigration') => $output,
			],
		];
	}
}
