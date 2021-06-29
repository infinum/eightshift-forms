<?php

/**
 * Enqueue class used to define all script and style enqueue for Gutenberg blocks.
 *
 * @package EightshiftForms\Enqueue
 */

declare(strict_types=1);

namespace EightshiftForms\Enqueue;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\Mailchimp\Mailchimp;
use EightshiftForms\Integrations\Mailerlite\Mailerlite;
use EightshiftForms\Rest\ActiveRouteInterface;

/**
 * Handles setting constants we need to add to both editor and frontend.
 */
class LocalizationConstants implements Filters
{

	public const LOCALIZATION_KEY = 'eightshiftForms';

	/**
	 * Key under which all localizations are held. window.${LOCALIZATION_KEY}. Only loaded in admin.
	 *
	 * @var string
	 */
	public const LOCALIZATION_ADMIN_KEY = 'eightshiftFormsAdmin';

	/**
	 * Dynamics CRM route obj.
	 *
	 * @var ActiveRouteInterface
	 */
	private $dynamicsCrmRoute;

	/**
	 * Buckaroo iDEAL route obj.
	 *
	 * @var ActiveRouteInterface
	 */
	private $buckarooIdealRoute;

	/**
	 * Buckaroo Emandate route obj.
	 *
	 * @var ActiveRouteInterface
	 */
	private $buckarooEmandateRoute;

	/**
	 * Buckaroo Pay By Email route obj.
	 *
	 * @var ActiveRouteInterface
	 */
	private $buckarooPayByEmailRoute;

	/**
	 * Send email route object.
	 *
	 * @var ActiveRouteInterface
	 */
	private $sendEmailRoute;

	/**
	 * Mailchimp route object.
	 *
	 * @var ActiveRouteInterface
	 */
	private $mailchimpRoute;

	/**
	 * Mailchimp client implementation.
	 *
	 * @var Mailchimp
	 */
	private $mailchimp;

	/**
	 * Mailerlite route object.
	 *
	 * @var ActiveRouteInterface
	 */
	private $mailerliteRoute;

	/**
	 * Mailerlite client implementation.
	 *
	 * @var Mailerlite
	 */
	private $mailerlite;

	/**
	 * Create a new admin instance.
	 *
	 * @param ActiveRouteInterface $dynamicsCrmRoute          Dynamics CRM route object which holds values we need to localize.
	 * @param ActiveRouteInterface $buckarooIdealRoute        Buckaroo (Ideal) route object which holds values we need to localize.
	 * @param ActiveRouteInterface $buckarooEmandateRoute     Buckaroo (Emandate) route object which holds values we need to localize.
	 * @param ActiveRouteInterface $buckarooPayByEmailRoute Buckaroo (Pay By Email) route object which holds values we need to localize.
	 * @param ActiveRouteInterface $sendEmailRoute            Send Email route object which holds values we need to localize.
	 * @param ActiveRouteInterface $mailchimpRoute             Mailchimp route object which holds values we need to localize.
	 * @param Mailchimp            $mailchimp                   Mailchimp implementation.
	 * @param ActiveRouteInterface $mailerliteRoute            Mailerlite route object which holds values we need to localize.
	 * @param Mailerlite           $mailerlite                  Mailerlite implementation.
	 */
	public function __construct(
		ActiveRouteInterface $dynamicsCrmRoute,
		ActiveRouteInterface $buckarooIdealRoute,
		ActiveRouteInterface $buckarooEmandateRoute,
		ActiveRouteInterface $buckarooPayByEmailRoute,
		ActiveRouteInterface $sendEmailRoute,
		ActiveRouteInterface $mailchimpRoute,
		Mailchimp $mailchimp,
		ActiveRouteInterface $mailerliteRoute,
		Mailerlite $mailerlite
	) {
		$this->dynamicsCrmRoute = $dynamicsCrmRoute;
		$this->buckarooIdealRoute = $buckarooIdealRoute;
		$this->buckarooEmandateRoute = $buckarooEmandateRoute;
		$this->buckarooPayByEmailRoute = $buckarooPayByEmailRoute;
		$this->sendEmailRoute = $sendEmailRoute;
		$this->mailchimpRoute = $mailchimpRoute;
		$this->mailchimp = $mailchimp;
		$this->mailerliteRoute = $mailerliteRoute;
		$this->mailerlite = $mailerlite;
	}

	/**
	 * Define all variables we need in both editor and frontend.
	 *
	 * @return array
	 */
	public function getLocalizations(): array
	{
		$localization = [
			self::LOCALIZATION_KEY => [
				'siteUrl'           => get_site_url(),
				'isDynamicsCrmUsed' => has_filter(Filters::DYNAMICS_CRM),
				'isBuckarooUsed'    => has_filter(Filters::BUCKAROO),
				'isMailchimpUsed'   => has_filter(Filters::MAILCHIMP),
				'isMailerliteUsed'  => has_filter(Filters::MAILERLITE),
				'hasThemes'         => has_filter(Filters::GENERAL),
				'content' => [
					'formLoading' => esc_html__('Form is submitting, please wait.', 'eightshift-forms'),
					'formSuccess' => esc_html__('Form successfully submitted.', 'eightshift-forms'),
				],
				'sendEmail' => [
					'restUri' => $this->sendEmailRoute->getRouteUri(),
				],
				'internalServerErrorMessage' => esc_html__('Internal server error', 'eightshift-forms'),
			],
		];

		if (has_filter(Filters::GENERAL)) {
			$localization = $this->addGeneralConstants($localization);
		}

		if (has_filter(Filters::DYNAMICS_CRM)) {
			$localization = $this->addDynamicsCrmConstants($localization);
		}

		if (has_filter(Filters::BUCKAROO)) {
			$localization = $this->addBuckarooConstants($localization);
		}

		if (has_filter(Filters::MAILCHIMP)) {
			$localization = $this->addMailchimpConstants($localization);
		}

		if (has_filter(Filters::MAILERLITE)) {
			$localization = $this->addMailerliteConstants($localization);
		}

		if (has_filter(Filters::PREFILL_GENERIC_MULTI)) {
			$localization[self::LOCALIZATION_KEY]['prefill']['multi'] = $this->addPrefillGenericMultiConstants();
		}

		if (has_filter(Filters::PREFILL_GENERIC_SINGLE)) {
			$localization[self::LOCALIZATION_KEY]['prefill']['single'] = $this->addPrefillGenericSingleConstants();
		}

		return $localization;
	}

	/**
	 * Define all variables we need in both editor and frontend.
	 *
	 * @return array
	 */
	public function getAdminLocalizations(): array
	{
		$localization = [
		self::LOCALIZATION_ADMIN_KEY => [],
		];

		if (has_filter(Filters::MAILCHIMP)) {
			$localization = $this->addMailchimpAdminConstants($localization);
		}

		if (has_filter(Filters::MAILERLITE)) {
			$localization = $this->addMailerliteConstantsAdmin($localization);
		}

		return $localization;
	}

	/**
	 * Localize all constants required for Dynamics CRM integration.
	 *
	 * @param  array $localization Existing localizations.
	 * @return array
	 */
	private function addGeneralConstants(array $localization): array
	{
		$localization[self::LOCALIZATION_KEY]['themes'] = apply_filters(Filters::GENERAL, 'themes');
		return $localization;
	}


	/**
	 * Localize all constants required for Dynamics CRM integration.
	 *
	 * @param  array $localization Existing localizations.
	 * @return array
	 */
	private function addDynamicsCrmConstants(array $localization): array
	{
		$entities = apply_filters(Filters::DYNAMICS_CRM, 'available_entities');
		if (empty($entities)) {
			$availableEntities = [
				/* translators: %s will be replaced with filter name (string). */
				sprintf(esc_html__('No options found, please set available options in %s filter as availableEntities', 'eightshift-forms'), self::DYNAMICS_CRM),
			];
		} else {
			$availableEntities = $entities;
		}

		$localization[self::LOCALIZATION_KEY]['dynamicsCrm'] = [
			'restUri' => $this->dynamicsCrmRoute->getRouteUri(),
			'availableEntities' => $availableEntities,
		];

		return $localization;
	}

	/**
	 * Localize all constants required for Buckaroo integration.
	 *
	 * @param  array $localization Existing localizations.
	 * @return array
	 */
	private function addBuckarooConstants(array $localization): array
	{
		$localization[self::LOCALIZATION_KEY]['buckaroo'] = [
			'restUri' => [
				'ideal' => $this->buckarooIdealRoute->getRouteUri(),
				'emandate' => $this->buckarooEmandateRoute->getRouteUri(),
				'payByEmail' => $this->buckarooPayByEmailRoute->getRouteUri(),
			],
		];

		return $localization;
	}

	/**
	 * Localize all constants required for Mailchimp integration.
	 *
	 * @param  array $localization Existing localizations.
	 * @return array
	 */
	private function addMailchimpConstants(array $localization): array
	{
		$localization[self::LOCALIZATION_KEY]['mailchimp'] = [
			'restUri' => $this->mailchimpRoute->getRouteUri(),
		];

		return $localization;
	}

	/**
	 * Localize all constants required for Mailchimp integration (only available in admin)
	 *
	 * @param  array $localization Existing localizations.
	 * @return array
	 */
	protected function addMailchimpAdminConstants(array $localization): array
	{
		$localization[self::LOCALIZATION_ADMIN_KEY]['mailchimp'] = [
			'audiences' => $this->fetchMailchimpAudiences(),
		];

		return $localization;
	}

	/**
	 * Localize all constants required for Mailerlite integration.
	 *
	 * @param  array $localization Existing localizations.
	 * @return array
	 */
	private function addMailerliteConstants(array $localization): array
	{
		$localization[self::LOCALIZATION_KEY]['mailerlite'] = [
			'restUri' => $this->mailerliteRoute->getRouteUri(),
		];

		return $localization;
	}

	/**
	 * Localize all constants required for Mailerlite integration.
	 *
	 * @param  array $localization Existing localizations.
	 * @return array
	 */
	private function addMailerliteConstantsAdmin(array $localization): array
	{
		$localization[self::LOCALIZATION_ADMIN_KEY]['mailerlite'] = [
			'groups' => $this->fetchMailerliteGroups(),
		];

		return $localization;
	}

	/**
	 * Reads the list of audiences from Mailchimp. Used in form options to
	 * select which audience does this form post to.
	 *
	 * @return array
	 */
	private function fetchMailchimpAudiences(): array
	{
		$audiences = [];

		try {
			$response = $this->mailchimp->getAllLists();
		} catch (\Exception $e) {
			return $audiences;
		}

		foreach ($response->lists as $listObj) {
			$audiences[] = [
				'value' => $listObj->id,
				'label' => $listObj->name,
			];
		}

		return $audiences;
	}

	/**
	 * Reads the list of groups from Mailerlite. Used in form options to
	 * select which group does this form post to.
	 *
	 * @return array
	 */
	private function fetchMailerliteGroups(): array
	{
		$groups = [];

		try {
			$response = $this->mailerlite->getAllGroups();
		} catch (\Exception $e) {
			return $groups;
		}

		foreach ($response as $listObj) {
			$groups[] = [
				'value' => $listObj->id,
				'label' => $listObj->name,
			];
		}

		return $groups;
	}

	/**
	 * Localize all constants required for Dynamics CRM integration.
	 * Adds prefill options to multi option blocks (select, radio, etc).
	 *
	 * @return array
	 */
	private function addPrefillGenericMultiConstants(): array
	{
		$prefillMulti = apply_filters(Filters::PREFILL_GENERIC_MULTI, []);

		if (! is_array($prefillMulti)) {
			return [];
		}

		$prefillMultiFormatted = [];
		foreach ($prefillMulti as $sourceName => $prefillMultiSource) {
			if (isset($prefillMultiSource['data'])) {
				unset($prefillMultiSource['data']);
			}

			$prefillMultiFormatted[] = $prefillMultiSource;
		}

		return $prefillMultiFormatted;
	}

	/**
	 * Adds prefill options to single option blocks (input, etc).
	 *
	 * @return array
	 */
	protected function addPrefillGenericSingleConstants(): array
	{
		$prefillSingle = apply_filters(Filters::PREFILL_GENERIC_SINGLE, []);

		if (! is_array($prefillSingle)) {
			return [];
		}

		$prefillSingleFormatted = [];
		foreach ($prefillSingle as $sourceName => $prefillSingleSource) {
			if (isset($prefillSingleSource['data'])) {
				unset($prefillSingleSource['data']);
			}

			$prefillSingleFormatted[] = $prefillSingleSource;
		}

		return $prefillSingleFormatted;
	}
}
