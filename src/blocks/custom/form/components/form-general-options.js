import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { SelectControl, BaseControl } from '@wordpress/components';
import { RichText } from '@wordpress/block-editor';

/**
 * Custom action which changes the "theme" attribute for this block and all it's innerBlocks.
 *
 * @param {string} newTheme New value for theme attribute
 * @param {function} onChangeTheme Prebuilt action for form block.
 */
const updateThemeForAllInnerBlocks = (newTheme, onChangeTheme) => {
  const thisBlock = wp.data.select('core/block-editor').getSelectedBlock();
  if (thisBlock.innerBlocks) {
    thisBlock.innerBlocks.forEach((innerBlock) => {
      innerBlock.attributes.theme = newTheme;
      wp.data.dispatch('core/block-editor').updateBlock(innerBlock.clientId, innerBlock);
    });
  }
  onChangeTheme(newTheme);
};

export const FormGeneralOptions = (props) => {
  const {
    type,
    formTypes,
    theme,
    themeAsOptions,
    hasThemes,
    richTextClass,
    successMessage,
    errorMessage,
    onChangeType,
    onChangeTheme,
    onChangeSuccessMessage,
    onChangeErrorMessage,
  } = props;

  return (
    <Fragment>
      {onChangeType &&
        <SelectControl
          label={__('Type', 'eightshift-forms')}
          value={type}
          help={__('Choose what will this form do on submit', 'eightshift-forms')}
          options={formTypes}
          onChange={onChangeType}
        />
      }
      {onChangeTheme && hasThemes &&
        <SelectControl
          label={__('Theme', 'eightshift-forms')}
          help={__('Choose your form theme.', 'eightshift-forms')}
          value={theme}
          options={themeAsOptions}
          onChange={(newTheme) => {
            updateThemeForAllInnerBlocks(newTheme, onChangeTheme);
          }}
        />
      }

      {onChangeSuccessMessage &&
        <BaseControl
          label={__('Success message', 'eightshift-forms')}
          help={__('Message that the user will see if forms successfully submits.', 'eightshift-forms')}
        >
          <RichText
            className={richTextClass}
            placeholder={__('Add your success message', 'eightshift-forms')}
            onChange={onChangeSuccessMessage}
            value={successMessage}
          />
        </BaseControl>
      }

      {onChangeErrorMessage &&
        <BaseControl
          label={__('Error message', 'eightshift-forms')}
          help={__('Message that the user will see if forms fails to submit for whatever reason.', 'eightshift-forms')}
        >
          <RichText
            className={richTextClass}
            placeholder={__('Add your error message', 'eightshift-forms')}
            onChange={onChangeErrorMessage}
            value={errorMessage}
          />
        </BaseControl>
      }

    </Fragment>
  );
};
