import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { AsyncFormTokenField } from '../../../components/async-form-token-field/async-form-token-field';


/**
 * Options component
 *
 * @param {object} props Component props.
 */
export const FormCustomEventOptions = (props) => {
  const {
    eventNames,
    onChangeEventNames,
    suggestions,
    isLoading = false,
  } = props;

  return (
    <Fragment>
      <AsyncFormTokenField
        label={__('Events', 'eightshift-forms')}
        placeholder={__('Start typing to add events', 'eightshift-forms')}
        value={eventNames}
        suggestions={suggestions}
        isLoading={isLoading}
        onChange={onChangeEventNames}
      />
      <p>{__('Add custom JS events which will be fired when this form is submitted', 'eightshift-forms')}</p>
    </Fragment>
  );
};
