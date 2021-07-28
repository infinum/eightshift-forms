import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { AsyncFormTokenField } from '../../../components/async-form-token-field/async-form-token-field';
import { getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

/**
 * Options component
 *
 * @param {object} props Component props.
 */
export const FormCustomEventOptions = (props) => {
  const {
		attributes,
		setAttributes,
    formEventNames,
    suggestions,
    isLoading = false,
  } = props;

  return (
    <>
      <AsyncFormTokenField
        label={__('Events', 'eightshift-forms')}
        placeholder={__('Start typing to add events', 'eightshift-forms')}
        value={formEventNames}
        suggestions={suggestions}
        isLoading={isLoading}
				onChange={(value) => setAttributes({ [getAttrKey('formEventNames', attributes, manifest)]: value })}
      />
      <p>{__('Add custom JS events which will be fired when this form is submitted', 'eightshift-forms')}</p>
    </>
  );
};
