<?php

/**
 * Template for settings form optiosn page.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$settingsFormOptionsPageTitle = Components::checkAttr('settingsFormOptionsPageTitle', $attributes, $manifest);
$settingsFormOptionsSubTitle = Components::checkAttr('settingsFormOptionsSubTitle', $attributes, $manifest);
$settingsFormOptionsForms = Components::checkAttr('settingsFormOptionsForms', $attributes, $manifest);

?>

<h1>
	<?php echo esc_html($settingsFormOptionsPageTitle); ?>
</h1>

<p>
	<?php echo esc_html($settingsFormOptionsSubTitle); ?>
</p>

<?php if ($settingsFormOptionsForms) { ?>
	<ul>
		<?php foreach ($settingsFormOptionsForms as $form) { ?>
			<?php
			$id = $form['id'];
			$editLink = $form['editLink'];
			$settingsLink = $form['settingsLink'];
			$slug = $form['slug'];
			$title = $form['title'];
			?>
			<li>
				<a href="<?php echo esc_html($editLink); ?>">
					<?php echo esc_html($title); ?>
				</a>
				- 
				<a href="<?php echo esc_html($settingsLink); ?>">
					<?php echo esc_html('Settings', 'eightshift-forms'); ?>
				</a>
			</li>
		<?php } ?>
	</ul>
<?php } ?>
