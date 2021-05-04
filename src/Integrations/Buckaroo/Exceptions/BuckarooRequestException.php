<?php

/**
 * Exception for when something is not ok with Buckaroo response.
 *
 * @package EightshiftForms\Integrations\Buckaroo\Exceptions
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Buckaroo\Exceptions;

use EightshiftLibs\Exception\GeneralExceptionInterface;

/**
 * BuckarooRequestException class.
 */
class BuckarooRequestException extends \RuntimeException implements GeneralExceptionInterface
{

  /**
   * Message to throw.
   *
   * @var string
   */
	protected $message = '';

  /**
   * Data to expose.
   *
   * @var array
   */
	private $data = [];

  /**
   * Constructs object
   *
   * @param string $message Exception message.
   * @param array  $data (Optional) additional data we can provide.
   */
	public function __construct(string $message = '', array $data = [])
	{
		$this->message = $message;
		$this->data    = $data;
		parent::__construct('BuckarooRequestException');
	}

  /**
   * Returns message and data from exception. Used in rest apis.
   *
   * @return array
   */
	public function getExceptionForRestResponse(): array
	{
		return [
			'message' => $this->getCustomMessage(),
			'data' => $this->getData(),
		];
	}

  /**
   * $this->data getter.
   *
   * @return array
   */
	public function getData(): array
	{
		return $this->data;
	}

  /**
   * $this->message getter.
   *
   * @return string
   */
	public function getCustomMessage(): string
	{
		return $this->message;
	}
}
