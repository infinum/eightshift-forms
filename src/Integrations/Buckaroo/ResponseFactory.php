<?php

/**
 * Object representing a response from Buckaroo.
 *
 * @package EightshiftForms\Integrations\Buckaroo
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Buckaroo;

/**
 * Factory for generating responses from Buckaroo.
 */
class ResponseFactory
{

	/**
	 * Build Response object.
	 *
	 * @param array $buckarooParams Array of Buckaroo response params.
	 * @return Response
	 */
	public static function build(array $buckarooParams): Response
	{
		return new Response($buckarooParams);
	}
}
