<?php

/**
 * Template for settings form option page.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\Helpers\Components;
use EightshiftForms\Settings\AbstractFormBuilder;

$manifest = Components::getManifest(__DIR__);

$settingsFormOptionPageTitle = Components::checkAttr('settingsFormOptionPageTitle', $attributes, $manifest);
$settingsFormOptionSubTitle = Components::checkAttr('settingsFormOptionSubTitle', $attributes, $manifest);
$settingsFormOptionForm = Components::checkAttr('settingsFormOptionForm', $attributes, $manifest);

$formId = $_GET['formId'];

$form = \apply_filters(AbstractFormBuilder::SETTINGS_PAGE_FORM_BUILDER, $settingsFormOptionForm, $formId);

?>

<h1>
	<?php echo esc_html($settingsFormOptionPageTitle); ?>
</h1>

<p>
	<?php echo sprintf("%s - %s", esc_html($settingsFormOptionSubTitle), esc_html($formId)); ?>
</p>

<?php echo $form; ?>
