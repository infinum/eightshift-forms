<?php

/**
 * Template for the Mailchimp Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;
use EightshiftForms\Integrations\Mailchimp\MailchimpMapper;

$manifest = Components::getManifest(__DIR__);

$formPostId = Components::checkAttr('formPostId', $attributes, $manifest);

echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	MailchimpMapper::FILTER_MAPPER_NAME,
	[
		'formPostId' => $formPostId
	]
);
