<?php

/**
 * Template for the Form Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Integrations\Integrations;
use EightshiftForms\Integrations\Mailchimp\MailchimpMapper;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;
$blockJsClass = $manifest['blockJsClass'] ?? '';

$formName = Components::checkAttr('formName', $attributes, $manifest);
$formAction = Components::checkAttr('formAction', $attributes, $manifest);
$formMethod = Components::checkAttr('formMethod', $attributes, $manifest);
$formId = Components::checkAttr('formId', $attributes, $manifest);
$formPostId = Components::checkAttr('formPostId', $attributes, $manifest);
$formContent = Components::checkAttr('formContent', $attributes, $manifest);
$formSuccessRedirect = Components::checkAttr('formSuccessRedirect', $attributes, $manifest);
$formTrackingEventName = Components::checkAttr('formTrackingEventName', $attributes, $manifest);

$formClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($blockJsClass, $blockJsClass),
]);

?>

<form
	class="<?php echo esc_attr($formClass); ?>"
	name="<?php echo esc_attr($formName); ?>"
	id="<?php echo esc_attr($formId); ?>"
	action="<?php echo esc_attr($formAction); ?>"
	method="<?php echo esc_attr($formMethod); ?>"
	data-success-redirect="<?php echo esc_attr($formSuccessRedirect); ?>"
	data-tracking-event-name="<?php echo esc_attr($formTrackingEventName); ?>"
	data-form-post-id="<?php echo esc_attr($formPostId); ?>"
>
	<?php
	echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'global-msg',
		Components::props('globalMsg', $attributes, [
			'blockClass' => $componentClass
		])
	);
	?>

	<div class="<?php echo esc_attr("{$componentClass}__fields"); ?>">
		<?php
		echo $formContent; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
		?>
	</div>

	<?php
	echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'loader',
		Components::props('loader', $attributes, [
			'blockClass' => $componentClass
		])
	);
	?>
</form>
