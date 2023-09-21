<?php

/**
 * Interface that holds all methods for getting forms Transfer usage.
 *
 * @package EightshiftForms\Transfer
 */

declare(strict_types=1);

namespace EightshiftForms\Transfer;

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
	 * Export Forms with settings.
	 *
	 * @param array<int, string> $items Specify items to query.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getExportForms(array $items = []): array;

	/**
	 * Export one form with settings.
	 *
	 * @param string $item Specify item id to query.
	 *
	 * @return array<int, mixed>
	 */
	public function getExportForm(string $item): array;

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
