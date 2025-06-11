<?php

/**
 * Template for the Form edit actions component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\AdminMenus\FormSettingsAdminSubMenu;
use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Dashboard\SettingsDashboard;
use EightshiftForms\General\SettingsGeneral;
use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';

$formEditActionsFormPostId = Helpers::checkAttr('formEditActionsFormPostId', $attributes, $manifest);
$formEditActionsFormHasSteps = Helpers::checkAttr('formEditActionsFormHasSteps', $attributes, $manifest);

$twClasses = FormsHelper::getTwSelectors($attributes, ['form-edit-actions']);

?>

<div class="<?php echo esc_attr(FormsHelper::getTwBase($twClasses, 'form-edit-actions', "{$componentClass}__edit-wrap")) ?>">
	<?php if (current_user_can(Forms::POST_CAPABILITY_TYPE)) { ?>
		<a
			class="<?php echo esc_attr(FormsHelper::getTwPart($twClasses, 'form-edit-actions', 'link', "{$componentClass}__edit-link")) ?>"
			href="<?php echo esc_url(GeneralHelpers::getFormEditPageUrl($formEditActionsFormPostId)) ?>"
			title="<?php esc_html_e('Edit form', 'eightshift-forms'); ?>">
			<?php echo UtilsHelper::getUtilsIcons('edit'); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
			?>
		</a>
	<?php } ?>

	<?php if (current_user_can(FormSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY)) { ?>
		<a
			class="<?php echo esc_attr(FormsHelper::getTwPart($twClasses, 'form-edit-actions', 'link', "{$componentClass}__edit-link")) ?>"
			href="<?php echo esc_url(GeneralHelpers::getSettingsPageUrl($formEditActionsFormPostId, SettingsGeneral::SETTINGS_TYPE_KEY)) ?>"
			title="<?php esc_html_e('Edit settings', 'eightshift-forms'); ?>">
			<?php echo UtilsHelper::getUtilsIcons('settings'); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
			?>
		</a>
	<?php } ?>

	<?php if (current_user_can(Forms::POST_CAPABILITY_TYPE)) { ?>
		<a
			class="<?php echo esc_attr(FormsHelper::getTwPart($twClasses, 'form-edit-actions', 'link', "{$componentClass}__edit-link")) ?>"
			href="<?php echo esc_url(GeneralHelpers::getSettingsGlobalPageUrl(SettingsDashboard::SETTINGS_TYPE_KEY)) ?>"
			title="<?php esc_html_e('Edit global settings', 'eightshift-forms'); ?>">
			<?php echo UtilsHelper::getUtilsIcons('dashboard'); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
			?>
		</a>

		<?php if ($formEditActionsFormHasSteps) { ?>
			<a
				href="#"
				class="<?php echo esc_attr(FormsHelper::getTwPart($twClasses, 'form-edit-actions', 'link', "{$componentClass}__edit-link " . UtilsHelper::getStateSelector('stepDebugPreview'))); ?>"
				title="<?php esc_html_e('Debug form', 'eightshift-forms'); ?>">
				<?php echo UtilsHelper::getUtilsIcons('debug'); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
				?>
			</a>
		<?php } ?>
	<?php } ?>
</div>
