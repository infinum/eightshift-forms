import { __ } from '@wordpress/i18n';
import { Fragment, useState } from '@wordpress/element';
import { SelectControl, BaseControl, ToggleControl, CheckboxControl, Notice, TextControl } from '@wordpress/components';
import { RichText } from '@wordpress/block-editor';
import { CantHaveMultipleRedirects } from './cant-have-multiple-redirects-notice';
import { getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

/**
 * Single checkbox for form type.
 *
 * @param {Object} props Props.
 */
const TypeCheckbox = (props) => {

  const {
    attributes,
    setAttributes,
    value,
    selectedTypes,
    selectedTypesRedirect,
    isChecked,
    isRedirect,
    setIsError,
    formShouldRedirectOnSuccess,
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
            setAttributes({ [getAttrKey('formTypesComplex', attributes, manifest)]: [...selectedTypes, value] });
          }

          if (!isNowChecked && selectedTypes.includes(value)) {
            setAttributes({ [getAttrKey('formTypesComplex', attributes, manifest)]: selectedTypes.filter((type) => type !== value) });
          }

        } else {
          if (isNowChecked && !selectedTypesRedirect.includes(value)) {

            // Prevent checking if new value is redirect but we already have redirection enabled.
            if (formShouldRedirectOnSuccess) {
              setIsErrorOnComplexTypeSelect(true);
              return null;
            }

            if (selectedTypesRedirect.length > 0) {
              setIsError(true);
              return null;
            }

            setAttributes({ [getAttrKey('formTypesComplexRedirect', attributes, manifest)]: [...selectedTypesRedirect, value] });
          }

          if (!isNowChecked && selectedTypesRedirect.includes(value)) {
            setAttributes({ [getAttrKey('formTypesComplexRedirect', attributes, manifest)]: selectedTypesRedirect.filter((type) => type !== value) });
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
    attributes,
    setAttributes,
    blockClass,
    label,
    formTypesComplex,
    formTypesComplexRedirect,
    types,
    help,
    setIsErrorOnComplexTypeSelect,
    formShouldRedirectOnSuccess,
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
              attributes={attributes}
              setAttributes={setAttributes}
              key={key}
              value={type.value}
              label={type.label}
              isRedirect={type.redirects || false}
              isChecked={formTypesComplex.includes(type.value) || formTypesComplexRedirect.includes(type.value)}
              selectedTypes={formTypesComplex}
              selectedTypesRedirect={formTypesComplexRedirect}
              setIsError={setIsError}
              setIsErrorOnComplexTypeSelect={setIsErrorOnComplexTypeSelect}
              formShouldRedirectOnSuccess={formShouldRedirectOnSuccess}
            />
          );
        })}
      </div>
    </BaseControl>
  );

};

export const FormGeneralOptions = (attributes) => {
  const {
    blockClass,
    formType,
    formTypesComplex = [],
    formTypesComplexRedirect = [],
    formIsComplexType,
    formTypes,
    richTextClass,
    formSuccessMessage,
    formErrorMessage,
    formShouldRedirectOnSuccess,
    formRedirectSuccess,
    setAttributes,
  } = attributes;

  const doesTypeRedirect = formTypes.filter((curType) => {
    if (curType.value !== formType || !curType.redirects) {
      return false;
    }

    return true;
  }).length > 0;

  const doesOneOfComplexTypesRedirects = formTypesComplexRedirect.length > 0;
  const hasRedirectTypes = (!formIsComplexType && doesTypeRedirect) || (formIsComplexType && doesOneOfComplexTypesRedirects);
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

      <ToggleControl
        label={__('Multiple types?', 'eightshift-forms')}
        help={__('Enabled if your form needs to do multiple things on submit.', 'eightshift-forms')}
        checked={formIsComplexType}
        onChange={(value) => setAttributes({ [getAttrKey('formIsComplexType', attributes, manifest)]: value })}
      />

      {isErrorOnTypeSelect &&
        <CantHaveMultipleRedirects dismissError={dismissErrorOnTypeSelect} forSelects={true} />
      }
      {!formIsComplexType &&
        <SelectControl
          label={__('Type', 'eightshift-forms')}
          value={formType}
          help={__('Choose what will this form do on submit', 'eightshift-forms')}
          options={formTypes}
          onChange={(newValue) => {
            if (!formShouldRedirectOnSuccess) {
              setAttributes({ [getAttrKey('formType', attributes, manifest)]: newValue });
            } else {
              seIsErrorOnTypeSelect(true);
            }
          }}
        />
      }

      {isErrorOnComplexTypeSelect &&
        <CantHaveMultipleRedirects dismissError={dismissErrorOnComplexTypeSelect} forSelects={true} />
      }
      {formIsComplexType &&
        <ComplexTypeSelector
          setAttributes={setAttributes}
          attributes={attributes}
          blockClass={blockClass}
          label={__('Types (Multiple)', 'eightshift-forms')}
          help={__('Choose what will this form do on submit', 'eightshift-forms')}
          formTypesComplex={formTypesComplex}
          formTypesComplexRedirect={formTypesComplexRedirect}
          types={formTypes}
          setIsErrorOnComplexTypeSelect={setIsErrorOnComplexTypeSelect}
          formShouldRedirectOnSuccess={formShouldRedirectOnSuccess}
        />
      }

      <BaseControl
        label={__('Success message', 'eightshift-forms')}
        help={__('Message that the user will see if forms successfully submits.', 'eightshift-forms')}
      >
        <RichText
          className={richTextClass}
          placeholder={__('Add your success message', 'eightshift-forms')}
          onChange={(value) => setAttributes({ [getAttrKey('formSuccessMessage', attributes, manifest)]: value })}
          value={formSuccessMessage}
        />
      </BaseControl>

      <BaseControl
        label={__('Error message', 'eightshift-forms')}
        help={__('Message that the user will see if forms fails to submit for whatever reason.', 'eightshift-forms')}
      >
        <RichText
          className={richTextClass}
          placeholder={__('Add your error message', 'eightshift-forms')}
          onChange={(value) => setAttributes({ [getAttrKey('formErrorMessage', attributes, manifest)]: value })}
          value={formErrorMessage}
        />
      </BaseControl>

      {isErrorOnRedirectToggle &&
        <CantHaveMultipleRedirects dismissError={dismissErrorOnRedirectToggle} />
      }

      <ToggleControl
        label={__('Redirect on success?', 'eightshift-forms')}
        help={__('Enable if you wish for the user to be redirected to success page after submitting form. Cannot be used if your form type redirects.', 'eightshift-forms')}
        checked={formShouldRedirectOnSuccess}
        onChange={(newValue) => {
          if (!hasRedirectTypes) {
            setAttributes({ [getAttrKey('formShouldRedirectOnSuccess', attributes, manifest)]: newValue });
          } else {
            setIsErrorOnRedirectToggle(true);
          }
        }}
      />

      {formShouldRedirectOnSuccess &&
        <TextControl
          label={__('Redirect URL', 'eightshift-forms')}
          help={__('Redirect to which user will be redirected on success. Needs to be on the same domain.', 'eightshift-forms')}
          value={formRedirectSuccess}
          onChange={(value) => setAttributes({ [getAttrKey('formRedirectSuccess', attributes, manifest)]: value })}
        />
      }

    </Fragment>
  );
};
