<?php

/**
 * Enqueue class used to define all script and style enqueues for Gutenberg blocks.
 *
 * @package EightshiftForms\Enqueue\Blocks
 */

declare(strict_types=1);

namespace EightshiftForms\Enqueue\Blocks;

use EightshiftForms\Config\Config;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftLibs\Manifest\ManifestInterface;
use EightshiftFormsVendor\EightshiftLibs\Enqueue\Blocks\AbstractEnqueueBlocks;

/**
 * Enqueue_Blocks class.
 */
class EnqueueBlocks extends AbstractEnqueueBlocks
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	public $validator;

	/**
	 * Filter additional blocks key.
	 */
	public const FILTER_ADDITIONAL_BLOCKS_NAME = 'es_forms_additional_blocks';

	/**
	 * Filter media breakpoints key.
	 */
	public const FILTER_MEDIA_BREAKPOINTS_NAME = 'es_forms_media_breakpoints';

	/**
	 * Filter block forms style options key.
	 */
	public const FILTER_BLOCK_FORMS_STYLE_OPTIONS_NAME = 'es_forms_block_forms_style_options';

	/**
	 * Create a new admin instance.
	 *
	 * @param ManifestInterface $manifest Inject manifest which holds data about assets from manifest.json.
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 */
	public function __construct(
		ManifestInterface $manifest,
		ValidatorInterface $validator
	) {
		$this->manifest = $manifest;
		$this->validator = $validator;
	}

	/**
	 * Register all the hooks
	 */
	public function register(): void
	{
		// Editor only script.
		\add_action('enqueue_block_editor_assets', [$this, 'enqueueBlockEditorScript']);

		// Editor only style.
		\add_action('enqueue_block_editor_assets', [$this, 'enqueueBlockEditorStyleLocal'], 50);

		// Editor and frontend style.
		\add_action('enqueue_block_assets', [$this, 'enqueueBlockStyleLocal'], 50);

		// Frontend only script.
		\add_action('wp_enqueue_scripts', [$this, 'enqueueBlockScript']);
	}

	/**
	 * Method that returns editor only style with check.
	 *
	 * @return mixed
	 */
	public function enqueueBlockEditorStyleLocal()
	{
		if ($this->isCheckboxOptionChecked(SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_STYLE_KEY, SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY)) {
			return null;
		}

		$this->enqueueBlockEditorStyle();
	}

	/**
	 * Method that returns editor and frontend style with check.
	 *
	 * @return mixed
	 */
	public function enqueueBlockStyleLocal()
	{
		if ($this->isCheckboxOptionChecked(SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_STYLE_KEY, SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY)) {
			return null;
		}

		$this->enqueueBlockStyle();
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
	 * Get script localizations
	 *
	 * @return array<string, mixed>
	 */
	protected function getLocalizations(): array
	{
		$output = [];

		// Only for block editor.
		if (is_admin()) {
			$output['additionalBlocks'] = apply_filters(self::FILTER_ADDITIONAL_BLOCKS_NAME, []);
			$output['formsBlockStyleOptions'] = apply_filters(self::FILTER_BLOCK_FORMS_STYLE_OPTIONS_NAME, []);
			$output['validationPatternsOptions'] = $this->validator->getValidationPatterns();
		}

		$output['mediaBreakpoints'] = apply_filters(self::FILTER_MEDIA_BREAKPOINTS_NAME, []);
		$output['postType'] = get_post_type() ?? '';

		return [
			'esFormsBlocksLocalization' => $output,
		];
	}
}
