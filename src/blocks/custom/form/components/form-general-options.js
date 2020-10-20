import { __ } from '@wordpress/i18n';
import { Fragment, useState } from '@wordpress/element';
import { SelectControl, BaseControl, ToggleControl, CheckboxControl } from '@wordpress/components';
import { RichText } from '@wordpress/block-editor';

const TypeCheckbox = (props) => {

  const {
    onChange,
    value,
    selectedTypes,
    isChecked,
  } = props;

  const [isCheckedState, setChecked] = useState(isChecked);

  return (
    <CheckboxControl
      {...props}
      checked={isCheckedState}
      onChange={(isNowChecked) => {
        if (isNowChecked && !selectedTypes.includes(value)) {
          onChange([...selectedTypes, value]);
        } else if (!isNowChecked && selectedTypes.includes(value)) {
          onChange(selectedTypes.filter((type) => type !== value));
        }

        setChecked(isNowChecked);
      }}
    />
  );
};

const ComplexTypeSelector = (props) => {
  const {
    label,
    value = [],
    types,
    help,
    onChange,
  } = props;

  return (
    <Fragment>
      <BaseControl label={label} help={help}>
        {types.map((type, key) => {
          return (
            <TypeCheckbox
              key={key}
              value={type.value}
              label={type.label}
              isChecked={value.includes(type.value)}
              selectedTypes={value}
              onChange={onChange}
            />
          );
        })}
      </BaseControl>
    </Fragment>
  );

};

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
    typeComplex,
    isComplexType,
    formTypes,
    theme,
    themeAsOptions,
    hasThemes,
    richTextClass,
    successMessage,
    errorMessage,
    onChangeType,
    onChangeTypeComplex,
    onChangeIsComplexType,
    onChangeTheme,
    onChangeSuccessMessage,
    onChangeErrorMessage,
  } = props;

  return (
    <Fragment>
      {onChangeIsComplexType &&
        <ToggleControl
          label={__('Is form complex?', 'eightshift-forms')}
          help={__('Complex forms are those that can do multiple things on submit (for example: first add member to Mailchimp and then redirect to Buckaroo for payment)', 'eightshift-forms')}
          checked={isComplexType}
          onChange={onChangeIsComplexType}
        />
      }
      {onChangeType && !isComplexType &&
        <SelectControl
          label={__('Type', 'eightshift-forms')}
          value={type}
          help={__('Choose what will this form do on submit', 'eightshift-forms')}
          options={formTypes}
          onChange={onChangeType}
        />
      }
      {onChangeTypeComplex && isComplexType &&
        <ComplexTypeSelector
          label={__('Type (Complex)', 'eightshift-forms')}
          value={typeComplex}
          help={__('Choose what will this form do on submit', 'eightshift-forms')}
          types={formTypes}
          onChange={onChangeTypeComplex}
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
