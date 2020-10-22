import { __ } from '@wordpress/i18n';
import { Fragment, useState } from '@wordpress/element';
import { SelectControl, BaseControl, ToggleControl, CheckboxControl, Notice } from '@wordpress/components';
import { RichText } from '@wordpress/block-editor';

/**
 * Single checkbox for form type.
 *
 * @param {Object} props Props.
 */
const TypeCheckbox = (props) => {

  const {
    onChangeTypes,
    onChangeTypesRedirect,
    value,
    selectedTypes,
    selectedTypesRedirect,
    isChecked,
    isRedirect,
    setIsError,
  } = props;

  const [isCheckedState, setChecked] = useState(isChecked);

  return (
    <CheckboxControl
      {...props}
      checked={isCheckedState}
      onChange={(isNowChecked) => {

        // Redirects have an array of their own.
        if (!isRedirect) {
          if (isNowChecked && !selectedTypes.includes(value)) {
            onChangeTypes([...selectedTypes, value]);
          }

          if (!isNowChecked && selectedTypes.includes(value)) {
            onChangeTypes(selectedTypes.filter((type) => type !== value));
          }

        } else {
          if (isNowChecked && !selectedTypesRedirect.includes(value)) {

            if (selectedTypesRedirect.length > 0) {
              setIsError(true);
              return null;
            }
            onChangeTypesRedirect([...selectedTypesRedirect, value]);
          }

          if (!isNowChecked && selectedTypesRedirect.includes(value)) {
            onChangeTypesRedirect(selectedTypesRedirect.filter((type) => type !== value));
          }
        }

        setChecked(isNowChecked);
      }}
    />
  );
};

/**
 * Component for selecting multiple form types.
 *
 * @param {object} props Props
 */
const ComplexTypeSelector = (props) => {
  const {
    blockClass,
    label,
    typesComplex,
    typesComplexRedirect,
    types,
    help,
    onChangeTypes,
    onChangeTypesRedirect,
  } = props;

  const [isError, setIsError] = useState(false);

  const dismissError = () => {
    setIsError(false);
  };

  return (
    <BaseControl label={label} help={help}>

      {isError &&
        <Notice status="error" onRemove={dismissError}>
          {__('Unable to select multiple types that redirect.', 'eightshift-forms')}
        </Notice>
      }

      <div className={`${blockClass}__types-wrapper`}>
        {types.map((type, key) => {
          return (
            <TypeCheckbox
              key={key}
              value={type.value}
              label={type.label}
              isRedirect={type.redirects || false}
              isChecked={typesComplex.includes(type.value) || typesComplexRedirect.includes(type.value)}
              selectedTypes={typesComplex}
              selectedTypesRedirect={typesComplexRedirect}
              onChangeTypes={onChangeTypes}
              onChangeTypesRedirect={onChangeTypesRedirect}
              setIsError={setIsError}
            />
          );
        })}
      </div>
    </BaseControl>
  );

};

export const FormGeneralOptions = (props) => {
  const {
    blockClass,
    type,
    typesComplex,
    typesComplexRedirect,
    isComplexType,
    formTypes,
    richTextClass,
    successMessage,
    errorMessage,
    onChangeType,
    onChangeTypesComplex,
    onChangeTypesComplexRedirect,
    onChangeIsComplexType,
    onChangeSuccessMessage,
    onChangeErrorMessage,
  } = props;

  return (
    <Fragment>
      {onChangeIsComplexType &&
        <ToggleControl
          label={__('Multiple types?', 'eightshift-forms')}
          help={__('Enabled if your form needs to do multiple things on submit.', 'eightshift-forms')}
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
      {onChangeTypesComplex && isComplexType &&
        <ComplexTypeSelector
          blockClass={blockClass}
          label={__('Types (Multiple)', 'eightshift-forms')}
          typesComplex={typesComplex}
          typesComplexRedirect={typesComplexRedirect}
          help={__('Choose what will this form do on submit', 'eightshift-forms')}
          types={formTypes}
          onChangeTypes={onChangeTypesComplex}
          onChangeTypesRedirect={onChangeTypesComplexRedirect}
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
