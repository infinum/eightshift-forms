import { __ } from '@wordpress/i18n';
import { Fragment, useState } from '@wordpress/element';
import { SelectControl, BaseControl, ToggleControl, CheckboxControl, Notice, TextControl } from '@wordpress/components';
import { RichText } from '@wordpress/block-editor';
import { CantHaveMultipleRedirects } from './cant-have-multiple-redirects-notice';

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
    shouldRedirectOnSuccess,
    setIsErrorOnComplexTypeSelect,
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

            // Prevent checking if new value is redirect but we already have redirection enabled.
            if (shouldRedirectOnSuccess) {
              setIsErrorOnComplexTypeSelect(true);
              return null;
            }

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

        return true;
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
    setIsErrorOnComplexTypeSelect,
    shouldRedirectOnSuccess,
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
              setIsErrorOnComplexTypeSelect={setIsErrorOnComplexTypeSelect}
              shouldRedirectOnSuccess={shouldRedirectOnSuccess}
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
    typesComplex = [],
    typesComplexRedirect = [],
    isComplexType,
    formTypes,
    richTextClass,
    successMessage,
    errorMessage,
    shouldRedirectOnSuccess,
    redirectSuccess,
    onChangeType,
    onChangeTypesComplex,
    onChangeTypesComplexRedirect,
    onChangeIsComplexType,
    onChangeSuccessMessage,
    onChangeErrorMessage,
    onChangeShouldRedirectOnSuccess,
    onChangeRedirectSuccess,
  } = props;

  const doesTypeRedirect = formTypes.filter((curType) => {
    if (curType.value !== type || !curType.redirects) {
      return false;
    }

    return true;
  }).length > 0;

  const doesOneOfComplexTypesRedirects = typesComplexRedirect.length > 0;
  const hasRedirectTypes = (!isComplexType && doesTypeRedirect) || (isComplexType && doesOneOfComplexTypesRedirects);
  const [isErrorOnRedirectToggle, setIsErrorOnRedirectToggle] = useState(false);
  const [isErrorOnTypeSelect, seIsErrorOnTypeSelect] = useState(false);
  const [isErrorOnComplexTypeSelect, setIsErrorOnComplexTypeSelect] = useState(false);

  const dismissErrorOnRedirectToggle = () => {
    setIsErrorOnRedirectToggle(false);
  };

  const dismissErrorOnTypeSelect = () => {
    seIsErrorOnTypeSelect(false);
  };

  const dismissErrorOnComplexTypeSelect = () => {
    setIsErrorOnComplexTypeSelect(false);
  };

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
      {isErrorOnTypeSelect &&
        <CantHaveMultipleRedirects dismissError={dismissErrorOnTypeSelect} forSelects={true} />
      }
      {onChangeType && !isComplexType &&
        <SelectControl
          label={__('Type', 'eightshift-forms')}
          value={type}
          help={__('Choose what will this form do on submit', 'eightshift-forms')}
          options={formTypes}
          onChange={(newValue) => {
            if (!shouldRedirectOnSuccess) {
              onChangeType(newValue);
            } else {
              seIsErrorOnTypeSelect(true);
            }
          }}
        />
      }

      {isErrorOnComplexTypeSelect &&
        <CantHaveMultipleRedirects dismissError={dismissErrorOnComplexTypeSelect} forSelects={true} />
      }
      {onChangeTypesComplex && isComplexType &&
        <ComplexTypeSelector
          blockClass={blockClass}
          label={__('Types (Multiple)', 'eightshift-forms')}
          help={__('Choose what will this form do on submit', 'eightshift-forms')}
          typesComplex={typesComplex}
          typesComplexRedirect={typesComplexRedirect}
          types={formTypes}
          onChangeTypes={onChangeTypesComplex}
          onChangeTypesRedirect={onChangeTypesComplexRedirect}
          setIsErrorOnComplexTypeSelect={setIsErrorOnComplexTypeSelect}
          shouldRedirectOnSuccess={shouldRedirectOnSuccess}
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

      {isErrorOnRedirectToggle &&
        <CantHaveMultipleRedirects dismissError={dismissErrorOnRedirectToggle} />
      }

      {onChangeShouldRedirectOnSuccess &&
        <ToggleControl
          label={__('Redirect on success?', 'eightshift-forms')}
          help={__('Enable if you wish for the user to be redirected to success page after submitting form. Cannot be used if your form type redirects.', 'eightshift-forms')}
          checked={shouldRedirectOnSuccess}
          onChange={(newValue) => {
            if (!hasRedirectTypes) {
              onChangeShouldRedirectOnSuccess(newValue);
            } else {
              setIsErrorOnRedirectToggle(true);
            }
          }}
        />
      }

      {onChangeRedirectSuccess && shouldRedirectOnSuccess &&
        <TextControl
          label={__('Redirect URL', 'eightshift-forms')}
          help={__('Redirect to which user will be redirected on success. Needs to be on the same domain.', 'eightshift-forms')}
          value={redirectSuccess}
          onChange={onChangeRedirectSuccess}
        />
      }

    </Fragment>
  );
};
