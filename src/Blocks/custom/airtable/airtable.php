<?php

/**
 * Template for the Airtable Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Integrations\Airtable\Airtable;
use EightshiftForms\Integrations\Airtable\SettingsAirtable;

$manifest = Components::getManifest(__DIR__);
$manifestInvalid = Components::getManifest(dirname(__DIR__, 2) . '/components/invalid');

$blockClass = $attributes['blockClass'] ?? '';
$invalidClass = $manifestInvalid['componentClass'] ?? '';

$airtableFormServerSideRender = Components::checkAttr('airtableFormServerSideRender', $attributes, $manifest);
$airtableFormPostId = Components::checkAttr('airtableFormPostId', $attributes, $manifest);
$airtableFormDataTypeSelector = Components::checkAttr('airtableFormDataTypeSelector', $attributes, $manifest);

// Check if airtable data is set and valid.
$isSettingsValid = apply_filters(
	SettingsAirtable::FILTER_SETTINGS_IS_VALID_NAME,
	$airtableFormPostId
);

$airtableClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	Components::selector(!$isSettingsValid, $invalidClass),
]);

// Bailout if settings are not ok but show msg only in editor.
if (!$isSettingsValid && $airtableFormServerSideRender) {
	?>
		<div class="<?php echo esc_attr($airtableClass); ?>">
			<svg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 20 20' fill='none'><path d='M1.5 13.595V7.88c0-.18.184-.3.348-.23l6.157 2.639a.25.25 0 0 1 .013.453L1.862 13.82a.25.25 0 0 1-.362-.224ZM9.91 2.783 3.087 5.285a.25.25 0 0 0-.013.464l6.83 2.96a.25.25 0 0 0 .193.002l6.823-2.729a.25.25 0 0 0 0-.464l-6.831-2.732a.25.25 0 0 0-.179-.003Zm8.59 11.546V8.115a.25.25 0 0 0-.34-.233l-7.25 2.806a.25.25 0 0 0-.16.233v6.214a.25.25 0 0 0 .34.233l7.25-2.806a.25.25 0 0 0 .16-.233Z' stroke='currentColor' stroke-width='1.5' stroke-linejoin='round' fill='none'/></svg>
			<br />
			<b><?php esc_html_e('The Airtable integration is not configured correctly.', 'eightshift-forms'); ?></b>
			<br />
			<?php esc_html_e('Check the form settings and try again.', 'eightshift-forms'); ?>
		</div>
	<?php

	return;
}

// Output form.
echo apply_filters( // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
	Airtable::FILTER_MAPPER_NAME,
	$airtableFormPostId,
	[
		'formDataTypeSelector' => $airtableFormDataTypeSelector,
		'ssr' => $airtableFormServerSideRender,
	]
);
