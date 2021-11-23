<?php

/**
 * Template for the Form Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;
$componentJsClass = $manifest['componentJsClass'] ?? '';

$formName = Components::checkAttr('formName', $attributes, $manifest);
$formAction = Components::checkAttr('formAction', $attributes, $manifest);
$formMethod = Components::checkAttr('formMethod', $attributes, $manifest);
$formId = Components::checkAttr('formId', $attributes, $manifest);
$formPostId = Components::checkAttr('formPostId', $attributes, $manifest);
$formContent = Components::checkAttr('formContent', $attributes, $manifest);
$formSuccessRedirect = Components::checkAttr('formSuccessRedirect', $attributes, $manifest);
$formTrackingEventName = Components::checkAttr('formTrackingEventName', $attributes, $manifest);
$formType = Components::checkAttr('formType', $attributes, $manifest);
$formTypeSelector = Components::checkAttr('formTypeSelector', $attributes, $manifest);

$formClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($componentJsClass, $componentJsClass),
]);

$formAttrs = [];
if ($formTypeSelector) {
	$formAttrs['data-type-selector'] = esc_attr($formTypeSelector);
}

if ($formSuccessRedirect) {
	$formAttrs['data-success-redirect'] = esc_attr($formSuccessRedirect);
}

if ($formTrackingEventName) {
	$formAttrs['data-tracking-event-name'] = esc_attr($formTrackingEventName);
}

if ($formPostId) {
	$formAttrs['data-form-post-id'] = esc_attr($formPostId);
}

if ($formType) {
	$formAttrs['data-form-type'] = esc_html($formType);
}

if ($formId) {
	$formAttrs['id'] = esc_attr($formId);
}

if ($formName) {
	$formAttrs['name'] = esc_attr($formName);
}

if ($formAction) {
	$formAttrs['action'] = esc_attr($formAction);
}

if ($formMethod) {
	$formAttrs['method'] = esc_attr($formMethod);
}

$formAttrsOutput = '';
if ($formAttrs) {
	foreach ($formAttrs as $key => $value) {
		$formAttrsOutput .= \wp_kses_post(" {$key}='" . $value . "'");
	}
}

?>

<form
	class="<?php echo esc_attr($formClass); ?>"
	<?php echo $formAttrsOutput; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
>
	<?php
	echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'global-msg',
		Components::props('globalMsg', $attributes)
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
		Components::props('loader', $attributes)
	);
	?>
</form>
