<?php

/**
 * Template for settings form option page.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$settingsFormOptionPageTitle = Components::checkAttr('settingsFormOptionPageTitle', $attributes, $manifest);
$settingsFormOptionSubTitle = Components::checkAttr('settingsFormOptionSubTitle', $attributes, $manifest);
$settingsFormOptionId = Components::checkAttr('settingsFormOptionId', $attributes, $manifest);

?>

<h1>
	<?php echo esc_html($settingsFormOptionPageTitle); ?>
</h1>

<p>
	<?php echo esc_html($settingsFormOptionSubTitle); ?>
	<?php echo esc_html($settingsFormOptionId); ?>
</p>
