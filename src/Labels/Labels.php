<?php

/**
 * Class that holds all labels.
 *
 * @package EightshiftLibs\Labels
 */

declare(strict_types=1);

namespace EightshiftForms\Labels;

use EightshiftForms\Helpers\TraitHelper;

/**
 * Labels class.
 */
class Labels implements InterfaceLabels
{
	/**
	 * Use General helper trait.
	 */
	use TraitHelper;

	/**
	 * Get all labels
	 *
	 * @return array
	 */
	public function getLabels(): array
	{
		return [
			'validationRequired' => __('This field is required!', 'eightshift-forms'),
			'validationEmail' => __('This field is not a valid email!', 'eightshift-forms'),
			'validationUrl' => __('This field is not a valid url!', 'eightshift-forms'),
			// translators: %s used for displaying number value.
			'validationAccept' => __('Your file type is not supported. Please use only %s file type.', 'eightshift-forms'),
			// translators: %s used for displaying number value.
			'validationMinSize' => __('Your file is smaller than allowed. Minimum file size is %s kb.', 'eightshift-forms'),
			// translators: %s used for displaying number value.
			'validationMaxSize' => __('Your file is larget than allowed. Maximum file size is %s kb.', 'eightshift-forms'),

			'mailerErrorEmailNotSent' => __('Email not sent due to configuration issue. Please contact your admin.', 'eightshift-forms'),
			'mailerSuccessSend' => __('Success', 'eightshift-forms'),
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
	public function getLabel(string $key, string $formId): string
	{
		$dbLabel = $this->getSettingsValue($key, $formId);

		if (!empty($dbLabel)) {
			return $dbLabel;
		}

		$labels = $this->getLabels();

		return $labels[$key] ?? '';
	}
}
