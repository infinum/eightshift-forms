<?php

/**
 * The class register route for public form submiting endpoint
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Helpers\UploadHelper;
use EightshiftForms\Integrations\Greenhouse\GreenhouseClientInterface;
use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;
use EightshiftForms\Integrations\Mailchimp\MailchimpClientInterface;
use EightshiftForms\Integrations\Mailchimp\SettingsMailchimp;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Mailer\MailerInterface;
use EightshiftForms\Mailer\SettingsMailer;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class FormSubmitRoute
 */
class FormSubmitRoute extends AbstractBaseRoute
{
	/**
	 * Use trait Upload_Helper inside class.
	 */
	use UploadHelper;

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
	 * Instance variable of MailerInterface data.
	 *
	 * @var MailerInterface
	 */
	public $mailer;

	/**
	 * Instance variable of LabelsInterface data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Instance variable of GreenhouseClientInterface data.
	 *
	 * @var GreenhouseClientInterface
	 */
	protected $greenhouseClient;

	/**
	 * Instance variable for Mailchimp data.
	 *
	 * @var MailchimpClientInterface
	 */
	protected $mailchimpClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 * @param MailerInterface $mailer Inject MailerInterface which holds mailer methods.
	 * @param LabelsInterface $labels Inject LabelsInterface which holds labels data.
	 * @param GreenhouseClientInterface $greenhouseClient Inject GreenhouseClientInterface which holds Greenhouse connect data.
	 * @param MailchimpClientInterface $mailchimpClient Inject Mailchimp which holds Mailchimp connect data.
	 */
	public function __construct(
		ValidatorInterface $validator,
		MailerInterface $mailer,
		LabelsInterface $labels,
		GreenhouseClientInterface $greenhouseClient,
		MailchimpClientInterface $mailchimpClient
	) {
		$this->validator = $validator;
		$this->mailer = $mailer;
		$this->labels = $labels;
		$this->greenhouseClient = $greenhouseClient;
		$this->mailchimpClient = $mailchimpClient;
	}

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/form-submit';

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return self::ROUTE_SLUG;
	}

	/**
	 * Get callback arguments array
	 *
	 * @return array<string, mixed> Either an array of options for the endpoint, or an array of arrays for multiple methods.
	 */
	protected function getCallbackArguments(): array
	{
		return [
			'methods' => $this->getMethods(),
			'callback' => [$this, 'routeCallback'],
			'permission_callback' => [$this, 'permissionCallback'],
		];
	}

	/**
	 * Method that returns rest response
	 *
	 * @param \WP_REST_Request $request Data got from endpoint url.
	 *
	 * @return \WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function routeCallback(\WP_REST_Request $request)
	{
		$files = [];

		// Try catch request.
		try {
			// Get encripted form ID and decrypt it.
			$formId = $this->getFormId($request->get_body_params(), true);

			// Determin form type.
			$formType = $this->getFormType($request->get_body_params());

			// Validate request.
			$postParams = $this->verifyRequest($request, $formId);

			// Prepare fields.
			$params = $this->removeUneceseryParams($postParams['post']);

			// Prepare files.
			$files = $postParams['files'];

			// Upload files to temp folder.
			$files = $this->prepareFiles($files);

			// Determine form type to use.
			switch ($formType) {
				case SettingsMailer::SETTINGS_TYPE_KEY:
					return $this->sendEmail($formId, $params, $files);

				case SettingsGreenhouse::SETTINGS_TYPE_KEY:
					return $this->sendGreenhouse($formId, $params, $files);

				case SettingsMailchimp::SETTINGS_TYPE_KEY:
					return $this->sendMailchimp($formId, $params);
			}
		} catch (UnverifiedRequestException $e) {
			// Die if any of the validation fails.
			return \rest_ensure_response(
				[
					'code' => 400,
					'status' => 'error_validation',
					'message' => $e->getMessage(),
					'validation' => $e->getData(),
				]
			);
		} finally {
			// Always delete the files from the disk.
			if ($files) {
				$this->deleteFiles($files);
			}
		}
	}

	/**
	 * Use mailer function.
	 *
	 * @param string $formId Form ID.
	 * @param array<string, mixed> $params Params array.
	 * @param array<string, mixed> $files Files array.
	 *
	 * @return mixed
	 */
	private function sendEmail(string $formId, array $params = [], $files = [])
	{
		$isUsed = (bool) $this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_USE_KEY, $formId);

		// If Mailer system is not used just respond with success.
		if (!$isUsed) {
			return \rest_ensure_response([
				'code' => 200,
				'status' => 'success',
				'message' => $this->labels->getLabel('mailerSuccessNoSend', $formId),
			]);
		}

		// Check if Mailer data is set and valid.
		$isSettingsValid = \apply_filters(SettingsMailer::FILTER_SETTINGS_IS_VALID_NAME, $formId);

		// Bailout if settings are not ok.
		if (!$isSettingsValid) {
			return \rest_ensure_response([
				'code' => 404,
				'status' => 'error',
				'message' => $this->labels->getLabel('mailerErrorSettingsMissing', $formId),
			]);
		}

		// Send email.
		$mailer = $this->mailer->sendFormEmail(
			$formId,
			$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_TO_KEY, $formId),
			$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_SUBJECT_KEY, $formId),
			$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_TEMPLATE_KEY, $formId),
			$files,
			$params
		);

		// If email fails.
		if (!$mailer) {
			return \rest_ensure_response([
				'code' => 404,
				'status' => 'error',
				'message' => $this->labels->getLabel('mailerErrorEmailSend', $formId),
			]);
		}

		if (isset($params['sender-email'])) {
			$senderEmail = json_decode($params['sender-email'], true)['value'];

			// Send email.
			$mailerConfirmation = $this->mailer->sendFormEmail(
				$formId,
				$senderEmail,
				$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_SENDER_SUBJECT_KEY, $formId),
				$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_SENDER_TEMPLATE_KEY, $formId),
				$files,
				$params
			);

			// If email fails.
			if (!$mailerConfirmation) {
				return \rest_ensure_response([
					'code' => 404,
					'status' => 'error',
					'message' => $this->labels->getLabel('mailerErrorEmailConfirmationSend', $formId),
				]);
			}
		}

		// If email success.
		return \rest_ensure_response([
			'code' => 200,
			'status' => 'success',
			'message' => $this->labels->getLabel('mailerSuccess', $formId),
		]);
	}

	/**
	 * Use Greenhouse function.
	 *
	 * @param string $formId Form ID.
	 * @param array<string, mixed> $params Params array.
	 * @param array<string, mixed> $files Files array.
	 *
	 * @return mixed
	 */
	private function sendGreenhouse(string $formId, array $params = [], $files = [])
	{

		// Check if Greenhouse data is set and valid.
		$isSettingsValid = \apply_filters(SettingsGreenhouse::FILTER_SETTINGS_IS_VALID_NAME, $formId);

		// Bailout if settings are not ok.
		if (!$isSettingsValid) {
			return \rest_ensure_response([
				'code' => 404,
				'status' => 'error',
				'message' => $this->labels->getLabel('greenhouseErrorSettingsMissing', $formId),
			]);
		}

		// Send application to Greenhouse.
		$response = $this->greenhouseClient->postGreenhouseApplication(
			$this->getSettingsValue(SettingsGreenhouse::SETTINGS_GREENHOUSE_JOB_ID_KEY, $formId),
			$params,
			$files
		);

		$status = $response['status'] ?? 200;
		$message = $response['error'] ?? '';

		if (!$response) {
			return \rest_ensure_response([
				'code' => 404,
				'status' => 'error',
				'message' => $this->labels->getLabel('greenhouseWpError', $formId),
			]);
		}

		// Bailout if Greenhouse returns error.
		if ($status !== 200) {
			return \rest_ensure_response([
				'code' => $status,
				'status' => 'error',
				'message' => $message
			]);
		}

		// Finish with success.
		return \rest_ensure_response([
			'code' => 200,
			'status' => 'success',
			'message' => $this->labels->getLabel('greenhouseSuccess', $formId),
		]);
	}

	/**
	 * Use Mailchimp function.
	 *
	 * @param string $formId Form ID.
	 * @param array<string, mixed> $params Params array.
	 *
	 * @return mixed
	 */
	private function sendMailchimp(string $formId, array $params = [])
	{

		// Check if Mailchimp data is set and valid.
		$isSettingsValid = \apply_filters(SettingsMailchimp::FILTER_SETTINGS_IS_VALID_NAME, $formId);

		// Bailout if settings are not ok.
		if (!$isSettingsValid) {
			return \rest_ensure_response([
				'code' => 404,
				'status' => 'error',
				'message' => $this->labels->getLabel('mailchimpErrorSettingsMissing', $formId),
			]);
		}

		// Send application to Mailchimp.
		$response = $this->mailchimpClient->postMailchimpSubscription(
			$this->getSettingsValue(SettingsMailchimp::SETTINGS_MAILCHIMP_LIST_KEY, $formId),
			$params
		);

		$status = $response['status'] ?? 'subscribed';
		$message = $response['title'] ?? '';

		if (!$response) {
			return \rest_ensure_response([
				'code' => 404,
				'status' => 'error',
				'message' => $this->labels->getLabel('mailchimpWpError', $formId),
			]);
		}

		// Bailout if Mailchimp returns error.
		if ($status !== 'subscribed') {
			return \rest_ensure_response([
				'code' => $status,
				'status' => 'error',
				'message' => $message
			]);
		}

		// Finish with success.
		return \rest_ensure_response([
			'code' => 200,
			'status' => 'success',
			'message' => $this->labels->getLabel('mailchimpSuccess', $formId),
		]);
	}
}
