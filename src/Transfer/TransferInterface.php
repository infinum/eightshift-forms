<?php

/**
 * Interface that holds all methods for getting forms Transfer usage.
 *
 * @package EightshiftForms\Transfer
 */

declare(strict_types=1);

namespace EightshiftForms\Transfer;

use EightshiftForms\CustomPostType\Forms;

/**
 * Interface for TransferInterface
 */
interface TransferInterface
{
	/**
	 * Export global settings.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getExportGlobalSettings(): array;

	/**
	 * Export Custom post types with settings.
	 *
	 * @param array<int, string> $items Specify items to query.
	 * @param string $postType Specify post type to query.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getExportCpts(array $items = [], string $postType = Forms::POST_TYPE_SLUG): array;

	/**
	 * Export one custom post type with settings.
	 *
	 * @param string $item Specify item id to query.
	 * @param string $postType Specify post type to query.
	 *
	 * @return array<int, mixed>
	 */
	public function getExportCpt(string $item, string $postType = Forms::POST_TYPE_SLUG): array;

	/**
	 * Import uploaded file.
	 *
	 * @param string $upload Upload file.
	 * @param bool $override Override existing form.
	 *
	 * @return boolean
	 */
	public function getImport(string $upload, bool $override): bool;

	/**
	 * Import forms by form object.
	 *
	 * @param array<int, array<string, mixed>> $form Forms export details.
	 * @param bool $override Override existing form.
	 *
	 * @return boolean
	 */
	public function getImportByFormArray(array $form, bool $override): bool;
}
