<?php

/**
 * The class used to send all emails that is used in multiple integrations.
 *
 * @package EightshiftForms\Rest\Route\Integrations\Mailer
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Mailer;

/**
 * Class FormSubmitMailer
 */
interface FormSubmitMailerInterface
{
	/**
	 * Send emails method.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 * @param boolean $useSuccessAction If success action should be used.
	 *
	 * @return array<string, array<mixed>|int|string>
	 */
	public function sendEmails(array $formDetails, bool $useSuccessAction = false): array;

	/**
	 * Send fallback email.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return boolean
	 */
	public function sendfallbackIntegrationEmail(array $formDetails): bool;
}
