<?php

/**
 * The class register route for public form submiting endpoint
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Helpers\TraitHelper;
use EightshiftForms\Helpers\UploadHelper;
use EightshiftForms\Integrations\Greenhouse\GreenhouseClientInterface;
use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;
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
	use TraitHelper;

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
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 * @param MailerInterface $mailer Inject MailerInterface which holds mailer methods.
	 * @param LabelsInterface $labels Inject LabelsInterface which holds labels data.
	 * @param GreenhouseClientInterface $greenhouseClient Inject GreenhouseClientInterface which holds Greenhouse connect data.
	 */
	public function __construct(
		ValidatorInterface $validator,
		MailerInterface $mailer,
		LabelsInterface $labels,
		GreenhouseClientInterface $greenhouseClient
	) {
		$this->validator = $validator;
		$this->mailer = $mailer;
		$this->labels = $labels;
		$this->greenhouseClient = $greenhouseClient;
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
	 * @return array Either an array of options for the endpoint, or an array of arrays for multiple methods.
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

			switch ($formType) {
				case SettingsMailer::SETTINGS_TYPE_KEY:
					return $this->sendEmail($formId, $params, $files);
					break;

				case SettingsGreenhouse::SETTINGS_TYPE_KEY:
					return $this->sendGreenhouse($formId, $params, $files);
					break;
			}
		} catch (UnverifiedRequestException $e) {
			// Die if any of the validation fails.
			return \rest_ensure_response($e->getData());
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
	 * @param string $formId Form ID
	 * @param array $params Params array.
	 * @param array $files Files array.
	 *
	 * @return mixed
	 */
	private function sendEmail(string $formId, array $params = [], $files = []) {
		// Send email.
		$mailer = $this->mailer->sendFormEmail(
			$formId,
			$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_TO_KEY, $formId),
			$files,
			$params
		);

		// If email fails.
		if (!$mailer) {
			return \rest_ensure_response([
				'code' => 404,
				'status' => 'error',
				'message' => $this->labels->getLabel('mailerErrorEmail', $formId),
			]);
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
	 * @param string $formId Form ID
	 * @param array $params Params array.
	 * @param array $files Files array.
	 *
	 * @return mixed
	 */
	private function sendGreenhouse(string $formId, array $params = [], $files = []) {
		// Check if greenhouse data is set.
		$greenhouseUse = $this->getOptionValue(SettingsGreenhouse::SETTINGS_TYPE_KEY . 'Use');

		// Send email if everything is ok.
		if (!$greenhouseUse) {
			return \rest_ensure_response([
				'code' => 404,
				'status' => 'error',
				'message' => $this->labels->getLabel('greenhouseErrorUseMissing', $formId),
			]);
		}

		// Check if greenhouse data is set.
		$greenhouseJobId = $this->getSettingsValue(SettingsGreenhouse::SETTINGS_GREENHOUSE_JOB_ID_KEY, $formId);

		// Send email if everything is ok.
		if (!$greenhouseUse) {
			return \rest_ensure_response([
				'code' => 404,
				'status' => 'error',
				'message' => $this->labels->getLabel('greenhouseErrorJobIdMissing', $formId),
			]);
		}

		$response = $this->greenhouseClient->postGreenhouseApplication(
			$greenhouseJobId,
			$params,
			$files
		);

		error_log( print_r( ( $response ), true ) );

		$status = $response['status'] ?? 200;
		$message = $response['error'] ?? '';

		if ($status !== 200) {
			return \rest_ensure_response([
				'code' => $status,
				'status' => 'error',
				'message' => $message
			]);
		}

		return \rest_ensure_response([
			'code' => 200,
			'status' => 'success',
			'message' => $this->labels->getLabel('greenhouseSuccess', $formId),
		]);
	}
}
