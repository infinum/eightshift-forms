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

?>

<h1>
	<?php echo esc_html($settingsFormOptionPageTitle); ?>
</h1>

<p>
	<?php echo esc_html($settingsFormOptionSubTitle); ?>
</p>

<?php echo \apply_filters(AbstractFormBuilder::SETTINGS_PAGE_FORM_BUILDER, $settingsFormOptionForm); ?>
