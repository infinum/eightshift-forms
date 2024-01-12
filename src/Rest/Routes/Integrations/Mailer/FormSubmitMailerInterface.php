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
	 * @param array<string, mixed> $formDataReference Form reference got from abstract helper.
	 * @param array<string, mixed> $additionalData Additonal data to pass.
	 *
	 * @return array<string, array<mixed>|int|string>
	 */
	public function sendEmails(array $formDataReference, $additionalData = []): array;

	/**
	 * Send fallback email.
	 *
	 * @param array<mixed> $response API response data.
	 *
	 * @return boolean
	 */
	public function sendFallbackEmail(array $response): bool;
}
