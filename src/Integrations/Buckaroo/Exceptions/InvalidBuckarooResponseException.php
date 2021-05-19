<?php

/**
 * InvalidBuckarooResponseException class.
 *
 * @package EightshiftForms\Integrations\Buckaroo\Exceptions
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Buckaroo\Exceptions;

use EightshiftLibs\Exception\GeneralExceptionInterface;

/**
 * InvalidBuckarooResponseException class.
 */
class InvalidBuckarooResponseException extends \RuntimeException implements GeneralExceptionInterface
{

	/**
	 * Message to throw.
	 *
	 * @var string
	 */
	private $errorMessage = '';

	/**
	 * Constructs object
	 *
	 * @param string $errorMessage Message to throw.
	 */
	public function __construct(string $errorMessage)
	{
		$this->errorMessage = $errorMessage;
		parent::__construct('InvalidBuckarooResponseException');
	}

	/**
	 * $this->errorMessage getter.
	 *
	 * @return string
	 */
	public function getErrorMessage(): string
	{
		return $this->errorMessage;
	}
}
