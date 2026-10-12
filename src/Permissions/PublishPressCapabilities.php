<?php

/**
 * File that holds class for PublishPress Capabilities integration.
 *
 * @package EightshiftForms\Permissions
 */

declare(strict_types=1);

namespace EightshiftForms\Permissions;

use EightshiftForms\Config\Config;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * PublishPressCapabilities class.
 */
class PublishPressCapabilities implements ServiceInterface
{
	/**
	 * Tab title in the PublishPress Capabilities role editor.
	 */
	private const string TAB_TITLE = 'Eightshift Forms';

	/**
	 * Register custom hooks.
	 */
	public function register(): void
	{
		\add_filter('cme_plugin_capabilities', $this->addPluginCapabilities(...));
		\add_filter('cme_capability_descriptions', $this->addCapabilityDescriptions(...));
	}

	/**
	 * Group plugin capabilities under a dedicated tab.
	 *
	 * Post type capabilities already listed in the PublishPress post type tables are skipped by PublishPress.
	 *
	 * @param array<string, array<string>> $pluginCaps Capabilities grouped by plugin title.
	 *
	 * @return array<string, array<string>>
	 */
	public function addPluginCapabilities(array $pluginCaps): array
	{
		$pluginCaps[self::TAB_TITLE] = Config::CAPS;

		return $pluginCaps;
	}

	/**
	 * Add capability descriptions. PublishPress outputs them unescaped.
	 *
	 * @param array<string, string> $descriptions Descriptions keyed by capability.
	 *
	 * @return array<string, string>
	 */
	public function addCapabilityDescriptions(array $descriptions): array
	{
		$formsDescriptions = [
			Config::CAP_LISTING => \__('View the forms admin menu and forms listing', 'eightshift-forms'),
			Config::CAP_SETTINGS => \__('Manage individual form settings', 'eightshift-forms'),
			Config::CAP_SETTINGS_GLOBAL => \__('Manage global forms settings and integrations', 'eightshift-forms'),
			Config::CAP_RESULTS => \__('View the result outputs listing', 'eightshift-forms'),
			Config::CAP_FORM => \__('Show the forms admin bar menu and front-end form edit links', 'eightshift-forms'),
			Config::CAP_FORM_EDIT => \__('Edit any single form, checked per post', 'eightshift-forms'),
			Config::CAP_FORM_READ => \__('Read any single form, checked per post', 'eightshift-forms'),
			Config::CAP_FORM_DELETE => \__('Delete any single form, checked per post', 'eightshift-forms'),
			Config::CAP_FORM_EDIT_MULTIPLE => \__('Edit forms', 'eightshift-forms'),
			Config::CAP_FORM_EDIT_OTHERS => \__('Edit forms created by other users', 'eightshift-forms'),
			Config::CAP_FORM_DELETE_MULTIPLE => \__('Delete forms', 'eightshift-forms'),
			Config::CAP_FORM_PUBLISH => \__('Publish forms', 'eightshift-forms'),
			Config::CAP_FORM_READ_PRIVATE => \__('Read private forms', 'eightshift-forms'),
			Config::CAP_FORM_RESULT => \__('Capability type base for the result outputs post type, not checked on its own', 'eightshift-forms'),
			Config::CAP_FORM_RESULT_EDIT => \__('Edit any single result output, checked per post', 'eightshift-forms'),
			Config::CAP_FORM_RESULT_READ => \__('Read any single result output, checked per post', 'eightshift-forms'),
			Config::CAP_FORM_RESULT_DELETE => \__('Delete any single result output, checked per post', 'eightshift-forms'),
			Config::CAP_FORM_RESULT_EDIT_MULTIPLE => \__('Edit result outputs', 'eightshift-forms'),
			Config::CAP_FORM_RESULT_EDIT_OTHERS => \__('Edit result outputs created by other users', 'eightshift-forms'),
			Config::CAP_FORM_RESULT_DELETE_MULTIPLE => \__('Delete result outputs', 'eightshift-forms'),
			Config::CAP_FORM_RESULT_PUBLISH => \__('Publish result outputs', 'eightshift-forms'),
			Config::CAP_FORM_RESULT_READ_PRIVATE => \__('Read private result outputs', 'eightshift-forms'),
		];

		foreach ($formsDescriptions as $cap => $description) {
			$descriptions[$cap] = \esc_html($description);
		}

		return $descriptions;
	}
}
