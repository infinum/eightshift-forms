<?php

/**
 * Endpoint for adding / updating requests in Greenhouse.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/greenhouse
 *
 * @package EightshiftForms\Rest
 */

declare(strict_types=1);

namespace EightshiftForms\Rest;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Exception\MissingFilterInfoException;
use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Helpers\UploadHelper;
use EightshiftForms\Helpers\Validation;
use EightshiftForms\Integrations\Greenhouse\GreenhouseClientInterface;

/**
 * Class GreenhouseRoute
 */
class GreenhouseRoute extends BaseRoute implements Filters
{
	/**
	 * Use trait Upload_Helper inside class.
	 */
	use UploadHelper;

	/**
	 * Route slug
	 *
	 * @var string
	 */
	public const ENDPOINT_SLUG = '/greenhouse';

	/**
	 * Parameter for email.
	 *
	 * @var string
	 */
	public const EMAIL_PARAM = 'email';

	/**
	 * Parameter for first name.
	 *
	 * @var string
	 */
	public const FIRST_NAME_PARAM = 'first_name';

	/**
	 * Parameter for last name.
	 *
	 * @var string
	 */
	public const LAST_NAME_PARAM = 'last_name';

	/**
	 * Parameter for phone.
	 *
	 * @var string
	 */
	public const PHONE_PARAM = 'phone';

	/**
	 * Parameter for resume.
	 *
	 * @var string
	 */
	public const RESUME_PARAM = 'resume';

	/**
	 * Parameter for cover_letter.
	 *
	 * @var string
	 */
	public const COVER_LETTER_PARAM = 'cover_letter';

	/**
	 * Parameter for job ID.
	 *
	 * @var string
	 */
	public const JOB_ID_PARAM = 'job_id';

	/**
	 * Parameter for valid file formats.
	 *
	 * @var string
	 */
	public const VALID_FILE_FORMATS = 'pdf,doc,docx,txt,rtf';

	/**
	 * Instance variable of GreenhouseClientInterface data.
	 *
	 * @var GreenhouseClientInterface
	 */
	public $greenhouse;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param GreenhouseClientInterface $greenhouse Inject GreenhouseClientInterface which holds data for greenhouse connection.
	 */
	public function __construct(GreenhouseClientInterface $greenhouse)
	{
		$this->greenhouse = $greenhouse;
	}

	/**
	 * Method that returns rest response
	 *
	 * @param  \WP_REST_Request $request Data got from endpoint url.
	 *
	 * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function routeCallback(\WP_REST_Request $request)
	{
		$files = [];

		// Try catch request to greenhouse. If fails send and email.
		try {
			// Validate request.
			$this->verifyRequest($request);

			$postParms = $request->get_body_params();
			$fileParms = $request->get_file_params();

			// Validate Fields and files.
			$this->validateFields($postParms, $fileParms);

			// Get Job ID.
			$jobId = $postParms[self::JOB_ID_PARAM];

			// Upload files to the disc in th tmp folder.
			if (!defined('IS_TEST')) {
				$files = $this->prepareFiles($fileParms);
			}

			// Merge Fields and Files in one request and post it to Greenhouse.
			$response = $this->greenhouse->postGreenhouseApplication(
				$jobId,
				array_merge(
					$this->prepareGreenhouseFields($postParms),
					$this->prepareGreenhouseFiles($files)
				)
			);

			// Check the response status and error msg.
			$status = $response['status'] ?? 200;
			$message = $response['error'] ?? '';

			// If confirmation email filter is set used use it.
			if (has_filter(Filters::GREENHOUSE_CONFIRMATION)) {
				// Check if filter keys exist and are valid.
				$this->verifyGreenhouseConfirmationInfoExists();

				// Send confirmation email.
				$this->sendUserConfirmationMail(
					$this->prepareEmailFields($postParms),
					$files
				);
			}

			// Return the status.
			return \rest_ensure_response([
				'code' => $status,
				'message' => ! empty($message) ? $message : \esc_html__('Application successfully saved to Greenhouse.', 'eightshift-forms'),
				'data' => []
			]);
		} catch (UnverifiedRequestException $e) {
			// Die if any of the validation fails.
			return \rest_ensure_response($e->getData());
		} catch (MissingFilterInfoException $e) {
			// Die if main keys are missing from.
			return $this->restResponseHandler('greenhouse-missing-keys', ['message' => $e->getMessage()]);
		} catch (\Exception $e) {
			// If failback email filter is set used use it.
			if (has_filter(Filters::GREENHOUSE_FALLBACK)) {
				// Check if filter keys exist and are valid.
				$this->verifyGreenhouseFallbackInfoExists();

				// Send failback email.
				$this->sendFailMail(
					$this->prepareEmailFields($postParms ?? []),
					$files,
					$e->getMessage()
				);
			}

			return $this->restResponseHandlerUnknownError(['error' => $e->getMessage()]);
		} finally {
			// Always delete the files from the disk.
			if ($files) {
				$this->deleteFiles($files);
			}
		}
	}

	/**
	 * Validate fields.
	 *
	 * @param array $fields Array of fields.
	 * @param array $files Array of files.
	 *
	 * @throws UnverifiedRequestException If validation is not correct.
	 *
	 * @return void
	 */
	protected function validateFields(array $fields, array $files)
	{
		// Get all the fields to validate.
		$jobId = $fields[self::JOB_ID_PARAM] ?? '';
		$firstName = $fields[self::FIRST_NAME_PARAM] ?? '';
		$lastName = $fields[self::LAST_NAME_PARAM] ?? '';
		$email = $fields[self::EMAIL_PARAM] ?? '';
		$phone = $fields[self::PHONE_PARAM] ?? '';
		$resume = $files[self::RESUME_PARAM] ?? [];
		$coverLetter = $files[self::COVER_LETTER_PARAM] ?? [];

		// Make sure we have an job id.
		if (empty($jobId)) {
			throw new UnverifiedRequestException(
				$this->restResponseHandler('greenhouse-missing-job-id')->data
			);
		}

		// Make sure we have an first name.
		if (empty($firstName)) {
			throw new UnverifiedRequestException(
				$this->restResponseHandler('greenhouse-missing-first-name')->data
			);
		}

		// Make sure we have an last name.
		if (empty($lastName)) {
			throw new UnverifiedRequestException(
				$this->restResponseHandler('greenhouse-missing-last-name')->data
			);
		}

		// Make sure we have an email.
		if (empty($email)) {
			throw new UnverifiedRequestException(
				$this->restResponseHandler('greenhouse-missing-email')->data
			);
		}

		// Make sure fields doesn't contain url.
		if (
			Validation::containsUrl($firstName) ||
			Validation::containsUrl($lastName) ||
			Validation::containsUrl($phone)
		) {
			throw new UnverifiedRequestException(
				$this->restResponseHandler('greenhouse-contains-url')->data
			);
		}

		// Make sure email is the correct format.
		if (!\is_email($email)) {
			throw new UnverifiedRequestException(
				$this->restResponseHandler('greenhouse-invalid-email')->data
			);
		}

		// Make sure resume is validated if exists.
		if ($resume) {
			$resumeName = $files[self::RESUME_PARAM]['name'] ?? '';
			$resumeSize = $files[self::RESUME_PARAM]['size'] ?? '';

			// Make sure input file is the correct format.
			if (!Validation::isFileTypeValid($resumeName, self::VALID_FILE_FORMATS)) {
				throw new UnverifiedRequestException(
					$this->restResponseHandler('greenhouse-file-invalid-format-resume')->data
				);
			}

			// Make sure input is the correct min size.
			if (!Validation::isFileMinSizeValid($resumeSize)) {
				throw new UnverifiedRequestException(
					$this->restResponseHandler('greenhouse-file-invalid-size-resume')->data
				);
			}
		}

		// Make sure cover letter is validated if exists.
		if ($coverLetter) {
			$coverLetterName = $files[self::COVER_LETTER_PARAM]['name'] ?? '';
			$coverLetterSize = $files[self::COVER_LETTER_PARAM]['size'] ?? '';

			// Make sure input file is the correct format.
			if (!Validation::isFileTypeValid($coverLetterName, self::VALID_FILE_FORMATS)) {
				throw new UnverifiedRequestException(
					$this->restResponseHandler('greenhouse-file-invalid-format-cover-letter')->data
				);
			}

			// Make sure input is the correct min size.
			if (!Validation::isFileMinSizeValid($coverLetterSize)) {
				throw new UnverifiedRequestException(
					$this->restResponseHandler('greenhouse-file-invalid-size-cover-letter')->data
				);
			}
		}
	}

	/**
	 * Get Job Question details for specific job.
	 * Filter the Job questions to get the correct field details.
	 *
	 * @param array $job Job details array.
	 * @param string $field Field to search.
	 *
	 * @return array
	 */
	protected function getJobQuestionDetails(array $job, string $field): array
	{
		$item = array_filter(
			$job['questions'],
			function ($item) use ($field) {
				if ($item['name'] === $field) {
					return $item;
				}
			}
		);

		return array_values($item)[0] ?? [];
	}

	/**
	 * Get Job questions Select Label from Value.
	 * Greenhouse stores every select value as ID so we need to find the option label based on that ID.
	 *
	 * @param array $options Array of options.
	 * @param string $value Value to search.
	 *
	 * @return string
	 */
	protected function getJobQuestionSelectLabel(array $options, string $value): string
	{
		$item = array_filter(
			$options,
			function ($item) use ($value) {
				if ((string) $item['value'] === $value) {
					return $item;
				}
			}
		);

		return array_values($item)[0]['label'] ?? '';
	}

	/**
	 * Prepare all fields for fallback email.
	 *
	 * @param array $fields Fields to prepare.
	 *
	 * @return array
	 */
	protected function prepareEmailFields(array $fields): array
	{
		$jobId = $fields['job_id'];

		// Make an initial output object.
		$output = [
			'sender' => '',
			'basic' => [],
			'additional' => [],
			'service' => [],
		];

		// Get the job details from storage.
		$job = $this->greenhouse->getJob($jobId);

		foreach ($fields as $key => $value) {
			switch ($key) {
				case 'first_name':
				case 'last_name':
				case 'email':
				case 'phone':
					$sectionType = 'basic';
					break;

				case 'latitude':
				case 'longitude':
				case 'job_id':
				case 'page_url':
					$sectionType = 'services';
					break;

				default:
					$sectionType = 'additional';
					break;
			}

			// Populate sender key to be able to know where to send an email later.
			if ($key === 'email') {
				$output['sender'] = $value;
			}

			// Get Current job questions detail by key.
			$detail = $this->getJobQuestionDetails($job, (string) $key);

			if ($detail) {
				// Greenhouse stores every select value as ID so we need to find the option label based on that ID.
				if ($detail['type'] === 'select') {
					$value = $this->getJobQuestionSelectLabel($detail['options'], $value);
				}

				// Simple output.
				$output[$sectionType][] = [
					'label' => $detail['label'],
					'value' => $value,
				];
			}
		}

		return $output;
	}

	/**
	 * Send email to applicant as a confirmation email.
	 *
	 * @param array $fields Array of form fields.
	 * @param array $files  Array of form files.
	 *
	 * @return void
	 */
	protected function sendUserConfirmationMail(array $fields, array $files): void
	{
		// Get the email data from the provided filter.
		$name = $this->greenhouse->getConfirmationName();
		$email = $this->greenhouse->getConfirmationEmail();
		$subject = $this->greenhouse->getConfirmationSubject();
		$sender = $fields['sender'];

		// Load Mail template.
		$template = $this->getEmailDefaultTemplate($fields);

		// Load custom template if provided by filter.
		if (has_filter(Filters::GREENHOUSE_CONFIRMATION_TEMPLATE)) {
			$template = \apply_filters(Filters::GREENHOUSE_CONFIRMATION_TEMPLATE, $fields);
		}

		$headers[] = 'Content-Type: text/html; charset=UTF-8';

		if (!empty($email)) {
			if (!empty($name)) {
				$headers[] = "From: {$name} <{$email}>";
			} else {
				$headers[] = "From: {$email}";
			}
		}

		\wp_mail($sender, $subject, $template, $headers, $files);
	}

	/**
	 * Send email if integration to greenhouse fails.
	 *
	 * @param array $fields Array of form fields.
	 * @param array $files Array of form files.
	 * @param string $errorMsg  Error message response.
	 *
	 * @return void
	 */
	protected function sendFailMail(array $fields, array $files, string $errorMsg): void
	{
		// Get the email data from the provided filter.
		$email = $this->greenhouse->getFallbackEmail();
		$subject = $this->greenhouse->getFallbackSubject();

		// Load Mail template.
		$template = $this->getEmailDefaultTemplate($fields);

		// Load custom template if provided by filter.
		if (has_filter(Filters::GREENHOUSE_FALLBACK_TEMPLATE)) {
			$template = \apply_filters(Filters::GREENHOUSE_FALLBACK_TEMPLATE, $fields);
		}

		// Ouput the error in the response for full debugging.
		$fields['services'][] = [
			'label' => esc_html__('Greenhouse Error Response', 'eightshift-form'),
			'value' => $errorMsg,
		];

		$headers[] = 'Content-Type: text/html; charset=UTF-8';

		\wp_mail($email, $subject, $template, $headers, $files);
	}

	/**
	 * Get Confirmation default email template as a simple list.
	 *
	 * @param array $fields Fields to loop.
	 * @param boolean $useServices To use services key or not.
	 *
	 * @return string
	 */
	protected function getEmailDefaultTemplate(array $fields, bool $useServices = false): string
	{
		$output = '';

		$basic = $fields['basic'] ?? [];
		$additional = $fields['additional'] ?? [];
		$services = $fields['services'] ?? [];

		$dataLoop = array_merge(
			$basic,
			$additional
		);

		if ($useServices) {
			$dataLoop = array_merge(
				$dataLoop,
				$services
			);
		}

		foreach ($dataLoop as $item) {
			$label = $item['label'] ?? '';
			$value = $item['value'] ?? '';

			$output .= "<li><strong>{$label}</strong>: {$value}</li>";
		}
		return "
			<ul>
				<li>{$output}</li>
			</ul>
		";
	}

	/**
	 * Prepare all fields for greenhouse api.
	 *
	 * @param array $fields Fields to prepare.
	 *
	 * @return array
	 */
	protected function prepareGreenhouseFields(array $fields): array
	{
		$output = [];

		foreach ($fields as $key => $value) {
			$output[$key] = $value;
		}

		return $output;
	}

	/**
	 * Prepare all files for greenhouse api via JSON format.
	 *
	 * @param array $files Files to prepare.
	 *
	 * @return array
	 */
	protected function prepareGreenhouseFiles(array $files): array
	{
		$output = [];

		foreach ($files as $key => $value) {
			$name = explode('/', $value);

			$output["{$key}_content"] = base64_encode((string) file_get_contents($value)); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$output["{$key}_content_filename"] = end($name);
		}

		return $output;
	}

	/**
	 * Make sure we have the data we need defined as filters for the confirmation email.
	 *
	 * @throws MissingFilterInfoException When not all required keys are set.
	 *
	 * @return void
	 */
	private function verifyGreenhouseConfirmationInfoExists(): void
	{
		if (empty(\apply_filters(Filters::GREENHOUSE_CONFIRMATION, 'name'))) {
			throw MissingFilterInfoException::viewException(Filters::GREENHOUSE_CONFIRMATION, 'name');
		}

		if (empty(\apply_filters(Filters::GREENHOUSE_CONFIRMATION, 'email'))) {
			throw MissingFilterInfoException::viewException(Filters::GREENHOUSE_CONFIRMATION, 'email');
		}

		if (empty(\apply_filters(Filters::GREENHOUSE_CONFIRMATION, 'subject'))) {
			throw MissingFilterInfoException::viewException(Filters::GREENHOUSE_CONFIRMATION, 'subject');
		}

		// Returning value can't be mocked via tests.
		if (defined('IS_TEST') && ! IS_TEST) {
			if (apply_filters(Filters::GREENHOUSE_CONFIRMATION, 'name') === 'name') {
				throw MissingFilterInfoException::viewException(Filters::GREENHOUSE_CONFIRMATION, 'name');
			}

			if (apply_filters(Filters::GREENHOUSE_CONFIRMATION, 'email') === 'email') {
				throw MissingFilterInfoException::viewException(Filters::GREENHOUSE_CONFIRMATION, 'email');
			}

			if (apply_filters(Filters::GREENHOUSE_CONFIRMATION, 'subject') === 'subject') {
				throw MissingFilterInfoException::viewException(Filters::GREENHOUSE_CONFIRMATION, 'subject');
			}
		}
	}

	/**
	 * Make sure we have the data we need defined as filters for the fallback email.
	 *
	 * @throws MissingFilterInfoException When not all required keys are set.
	 *
	 * @return void
	 */
	private function verifyGreenhouseFallbackInfoExists(): void
	{
		if (empty(\apply_filters(Filters::GREENHOUSE_FALLBACK, 'email'))) {
			throw MissingFilterInfoException::viewException(Filters::GREENHOUSE_FALLBACK, 'email');
		}

		if (empty(\apply_filters(Filters::GREENHOUSE_FALLBACK, 'subject'))) {
			throw MissingFilterInfoException::viewException(Filters::GREENHOUSE_FALLBACK, 'subject');
		}

		// Returning value can't be mocked via tests.
		if (defined('IS_TEST') && ! IS_TEST) {
			if (apply_filters(Filters::GREENHOUSE_CONFIRMATION, 'email') === 'email') {
				throw MissingFilterInfoException::viewException(Filters::GREENHOUSE_FALLBACK, 'email');
			}

			if (apply_filters(Filters::GREENHOUSE_CONFIRMATION, 'subject') === 'subject') {
				throw MissingFilterInfoException::viewException(Filters::GREENHOUSE_FALLBACK, 'subject');
			}
		}
	}

	/**
	 * Defines a list of required parameters which must be present in the request as GET parameters or it will error out.
	 *
	 * @return array
	 */
	protected function getRequiredPostParams(): array
	{
		return [
			self::EMAIL_PARAM,
			self::FIRST_NAME_PARAM,
			self::LAST_NAME_PARAM,
			self::JOB_ID_PARAM,
		];
	}

	/**
	 * Returns allowed methods for this route.
	 *
	 * @return string|array
	 */
	protected function getMethods()
	{
		return static::CREATABLE;
	}
}
