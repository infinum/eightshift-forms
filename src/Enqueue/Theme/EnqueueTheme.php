<?php

/**
 * The Theme/Frontend Enqueue specific functionality.
 *
 * @package EightshiftForms\Enqueue\Theme
 */

declare(strict_types=1);

namespace EightshiftForms\Enqueue\Theme;

use EightshiftForms\Config\Config;
use EightshiftForms\Enqueue\LocalizationConstants;
use EightshiftLibs\Manifest\ManifestInterface;
use EightshiftLibs\Enqueue\Theme\AbstractEnqueueTheme;

/**
 * Class EnqueueTheme
 */
class EnqueueTheme extends AbstractEnqueueTheme
{

	/**
	 * Localization constants object.
	 *
	 * @var LocalizationConstants
	 */
	private $localizationConstants;

	/**
	 * Create a new admin instance.
	 *
	 * @param ManifestInterface     $manifest Inject manifest which holds data about assets from manifest.json.
	 * @param LocalizationConstants $localizationConstants Localization constants object.
	 */
	public function __construct(ManifestInterface $manifest, LocalizationConstants $localizationConstants)
	{
		$this->manifest = $manifest;
		$this->localizationConstants = $localizationConstants;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('wp_enqueue_scripts', [$this, 'enqueueStyles'], 10);
		\add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
	}

	/**
	 * Method that returns assets name used to prefix asset handlers.
	 *
	 * @return string
	 */
	public function getAssetsPrefix(): string
	{
		return Config::getProjectName();
	}

	/**
	 * Method that returns assets version for versioning asset handlers.
	 *
	 * @return string
	 */
	public function getAssetsVersion(): string
	{
		return Config::getProjectVersion();
	}

	/**
	 * Get localizations.
	 *
	 * @return array
	 */
	public function getLocalizations(): array
	{
		return $this->localizationConstants->getLocalizations();
	}
}
