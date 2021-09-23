<?php

/**
 * Mailchimp Mapper integration class.
 *
 * @package EightshiftForms\Integrations\Mailchimp
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailchimp;

use EightshiftFormsPluginVendor\PHPHtmlParser\Dom;
use EightshiftForms\Helpers\TraitHelper;
use EightshiftForms\Integrations\AbstractMapper;

/**
 * MailchimpMapper integration class.
 */
class MailchimpMapper extends AbstractMapper
{
	/**
	 * Use General helper trait.
	 */
	use TraitHelper;

	// Filter name.
	public const MAILCHIMP_MAPPER_FILTER_NAME = 'es_mailchimp_mapper_filter';

	public const MAILCHIMP_MAPPER_TRANSIENT_NAME = 'es_mailchimp_mapper_cache';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{

		// Blocks string to value filter name constant.
		\add_filter(static::MAILCHIMP_MAPPER_FILTER_NAME, [$this, 'getMapper']);
	}

	/**
	 * Map Mailchimp form to our components.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	public function getMapper(string $formId)
	{

		$form = $this->getCachedVersion($formId);

		// var_dump($form);
		$dom = new Dom;
		$dom->loadStr($form);
		$form = $dom->find('form')[0];

		$action = $form->getAttribute('action');

		var_dump($action);

		// echo $links->value;

		return $form;
	}

	/**
	 * Cached Version for optimisations.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	private function getCachedVersion(string $formId) {
		$form = get_transient(self::MAILCHIMP_MAPPER_TRANSIENT_NAME);

		if ($form) {
			return $form;
		}

		$form = $this->getRmoteForm(\get_post_meta($formId, $this->getSettingsName(SettingsMailchimp::MAILCHIMP_FORM_URL), true));

		$body = $form['body'] ?? '';

		if (!empty($body)) {
			set_transient(self::MAILCHIMP_MAPPER_TRANSIENT_NAME, $body, 3600);
		}

		return $body;

	}
}
