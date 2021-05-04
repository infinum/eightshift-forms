<?php

/**
 * The file that defines the project entry point class.
 *
 * A class definition that includes attributes and functions used across both the
 * public side of the site and the admin area.
 *
 * @package EightshiftForms\Config
 */

declare(strict_types=1);

namespace EightshiftForms\Config;

use EightshiftLibs\Config\AbstractConfigData;

/**
 * The project config class.
 */
class Config extends AbstractConfigData
{

	/**
	 * Value for the form type when it's sending an email
	 *
	 * @var string
	 */
	public const EMAIL_METHOD = 'email';

	/**
	 * Value for the form type when it's submitting to Dynamics CRM.
	 *
	 * @var string
	 */
	public const DYNAMICS_CRM_METHOD = 'dynamics-crm';

	/**
	 * Value for the form type when it's submitting to Buckaroo.
	 *
	 * @var string
	 */
	public const BUCKAROO_METHOD = 'buckaroo';

	/**
	 * Value for the form type when it's submitting to Mailchimp.
	 *
	 * @var string
	 */
	public const MAILCHIMP_METHOD = 'mailchimp';

	/**
	 * Value for the form type when it's submitting to Mailerlite.
	 *
	 * @var string
	 */
	public const MAILERLITE_METHOD = 'mailerlite';

	/**
	 * Value for the form type when it's submitting to Mailchimp.
	 *
	 * @var string
	 */
	public const CUSTOM_EVENT_METHOD = 'custom-event';

	/**
	 * Method that returns project name.
	 *
	 * Generally used for naming assets handlers, languages, etc.
	 */
	public static function getProjectName(): string
	{
		return 'eightshift-libs';
	}

	/**
	 * Method that returns project version.
	 *
	 * Generally used for versioning asset handlers while enqueueing them.
	 */
	public static function getProjectVersion(): string
	{
		return '1.0.0';
	}

	/**
	 * Method that returns project REST-API namespace.
	 *
	 * Used for namespacing projects REST-API routes and fields.
	 *
	 * @return string Project name.
	 */
	public static function getProjectRoutesNamespace(): string
	{
		return static::getProjectName();
	}

	/**
	 * Method that returns project REST-API version.
	 *
	 * Used for versioning projects REST-API routes and fields.
	 *
	 * @return string Project route version.
	 */
	public static function getProjectRoutesVersion(): string
	{
		return 'v1';
	}
}
