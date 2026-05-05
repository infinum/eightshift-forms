<?php

/**
 * The class for media.
 *
 * @package EightshiftForms\Media
 */

declare(strict_types=1);

namespace EightshiftForms\Media;

use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Class Media
 */
class Media implements ServiceInterface
{
	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter('mime_types', [$this, 'enableMimeTypes']);
	}

	/**
	 * Enable JSON upload in media.
	 *
	 * @param array<string, string> $mimes Load all mimes types.
	 *
	 * @return array<string, string>
	 */
	public function enableMimeTypes(array $mimes): array
	{
		$mimes['json'] = 'application/json';
		return $mimes;
	}
}
