<?php

/**
 * Class that holds all filter used in the component and blocks regarding form.
 *
 * @package EightshiftLibs\Form
 */

declare(strict_types=1);

namespace EightshiftForms\Form;

use EightshiftForms\Mailer\SettingsMailer;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Form class.
 */
class Form extends AbstractFormBuilder implements ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings option value key.
	 */
	public const FILTER_FORM_SETTINGS_OPTIONS_NAME = 'es_forms_form_settings_options';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_FORM_SETTINGS_OPTIONS_NAME, [$this, 'updateFormAttributesBeforeOutput']);
	}

	/**
	 * Modify original attributes before final output in form.
	 *
	 * @param array<string, mixed> $attributes Attributes to update.
	 *
	 * @return array<string, mixed>
	 */
	public function updateFormAttributesBeforeOutput(array $attributes): array
	{
		$prefix = $attributes['prefix'] ?? '';
		$blockName = $attributes['blockName'] ?? '';
		$postId = $attributes["{$prefix}PostId"] ?? '';

		if (!$prefix || !$blockName || !$postId) {
			return $attributes;
		}

		// Change form type depending if it is mailer empty.
		if ($blockName === SettingsMailer::SETTINGS_TYPE_KEY && isset($attributes["{$prefix}Action"])) {
			$attributes["{$prefix}Type"] = SettingsMailer::SETTINGS_TYPE_CUSTOM_KEY;
		}

		// Additional props from settings.
		return \array_merge($attributes, $this->getFormAdditionalPropsFromSettings($postId, $blockName, $prefix));
	}
}
