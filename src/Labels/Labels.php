<?php

/**
 * Class that holds all labels.
 *
 * @package EightshiftLibs\Labels
 */

declare(strict_types=1);

namespace EightshiftForms\Labels;

use EightshiftForms\Settings\SettingsHelper;

/**
 * Labels class.
 */
class Labels implements LabelsInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Get all labels
	 *
	 * @return array
	 */
	public function getLabels(): array
	{
		return [
			// Validation.
			'validationRequired' => __('This field is required!', 'eightshift-forms'),
			'validationEmail' => __('This field is not a valid email!', 'eightshift-forms'),
			'validationUrl' => __('This field is not a valid url!', 'eightshift-forms'),
			// translators: %s used for displaying number value.
			'validationAccept' => __('Your file type is not supported. Please use only %s file type.', 'eightshift-forms'),
			// translators: %s used for displaying number value.
			'validationMinSize' => __('Your file is smaller than allowed. Minimum file size is %s kb.', 'eightshift-forms'),
			// translators: %s used for displaying number value.
			'validationMaxSize' => __('Your file is larget than allowed. Maximum file size is %s kb.', 'eightshift-forms'),

			// Mailer.
			'mailerErrorSettingsMissing' => __('Mailer settings are not configured correctly. Please contact your admin.', 'eightshift-forms'),
			'mailerErrorEmailSend' => __('Email not sent due to unknown issue. Please contact your admin.', 'eightshift-forms'),
			'mailerSuccess' => __('Email sent successfully.', 'eightshift-forms'),

			// Greenhouse.
			'greenhouseWpError' => __('There was some problem with saving your application. Please contact your admin.', 'eightshift-forms'),
			'greenhouseErrorSettingsMissing' => __('Greenhouse integration is not configured correctly. Please contact your admin.', 'eightshift-forms'),
			'greenhouseErrorJobIdMissing' => __('Greenhouse Job Id is missing in the configuration. Please contact your admin.', 'eightshift-forms'),
			'greenhouseSuccess' => __('Candidate saved successfully.', 'eightshift-forms'),
		];
	}

	/**
	 * Return one label by key
	 *
	 * @param string $key Label key.
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	public function getLabel(string $key, string $formId = ''): string
	{
		// If form ID is not missing check form settings for the overrides.
		if (!empty($formId)) {
			$dbLabel = $this->getSettingsValue($key, $formId);

			// If there is an override in the DB use that.
			if (!empty($dbLabel)) {
				return $dbLabel;
			}
		}

		$labels = $this->getLabels();

		return $labels[$key] ?? '';
	}
}