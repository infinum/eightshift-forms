<?php

/**
 * Template for the Form Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Form\Form;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$formFormPostId = Components::checkAttr('formFormPostId', $attributes, $manifest);
$formFormAction = Components::checkAttr('formFormAction', $attributes, $manifest);

// Check if mailer data is set and valid.
$formClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
]);

$customFormType = !empty($formFormAction) ? ['formType' => 'custom'] : [];

?>

<div class="<?php echo esc_attr($formClass); ?>">
	<?php
	// There is no bailout here in case of missing settings because custom form can be used only to redirecto to another page with form data.
	echo Components::render(
		'form',
		Components::props('form', $attributes, array_merge(
			apply_filters(
				Form::FILTER_FORM_SETTINGS_OPTIONS_NAME,
				$formFormPostId
			),
			[
				'formContent' => $innerBlockContent,
			],
			$customFormType
		))
	);
	?>
</div>
