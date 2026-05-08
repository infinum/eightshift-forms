<?php

/**
 * Theme frontend class.
 *
 * @package EightshiftForms\Theme
 */

declare(strict_types=1);

namespace EightshiftForms\Theme;

use EightshiftForms\Helpers\HooksHelpers;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Settings\SettingsSettings;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Theme frontend class.
 */
class ThemeFrontend extends AbstractTheme implements ServiceInterface
{
	/**
	 * Selectors cache.
	 *
	 * @var array<string, mixed>|null
	 */
	private ?array $selectorsCache = null;

	/**
	 * Register the hooks.
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(HooksHelpers::getFilterName(['blocks', 'tailwindSelectors']), [$this, 'getSelectors']);
	}

	/**
	 * Get the tailwind selectors for frontend.
	 *
	 * @return array<string, mixed>
	 */
	public function getSelectors(): array
	{
		if ($this->selectorsCache !== null) {
			return $this->selectorsCache;
		}

		if (SettingsHelpers::isOptionCheckboxChecked(SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_SELECTORS_KEY, SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY)) {
			return [];
		}

		$this->selectorsCache = $this->getTheme();

		return $this->selectorsCache;
	}
}
