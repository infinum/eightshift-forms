<?php

/**
 * Template for the Form Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Form\Form;
use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Mailer\SettingsMailer;

$manifest = Components::getManifest(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$formFormPostId = Components::checkAttr('formFormPostId', $attributes, $manifest);
$formFormPostIdDecoded = Helper::encryptor('decode', $formFormPostId);

// Check if mailer data is set and valid.
$isSettingsValid = \apply_filters(SettingsMailer::FILTER_SETTINGS_IS_VALID_NAME, $formFormPostIdDecoded);

$formClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	Components::selector(!$isSettingsValid, $blockClass, '', 'invalid')
]);

?>

<div class="<?php echo esc_attr($formClass); ?>">
	<?php
	// Bailout if settings are not ok.
	if ($isSettingsValid) {
		echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'form',
			Components::props('form', $attributes, array_merge(
				[
					'formContent' => $innerBlockContent,
				],
				\apply_filters(
					Form::FILTER_FORM_SETTINGS_OPTIONS_NAME,
					$formFormPostId
				)
			))
		);
	} else {
		esc_html_e('Sorry, it looks like the form settings are not configured correctly. Please contact your admin.', 'eightshift-forms');
	}
	?>
</div>
