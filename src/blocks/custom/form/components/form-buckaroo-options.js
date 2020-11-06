import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { SelectControl, TextControl, TextareaControl, BaseControl, ToggleControl } from '@wordpress/components';


export const FormBuckarooOptions = (props) => {
  const {
    blockClass,
    service,
    emandateDescription,
    sequenceType,
    isSequenceTypeOnFrontend,
    redirectUrl,
    redirectUrlError,
    redirectUrlReject,
    onChangeIsSequenceTypeOnFrontend,
    onChangeService,
    onChangeEmandateDescription,
    onChangeSequenceType,
    onChangeRedirectUrl,
    onChangeRedirectUrlError,
    onChangeRedirectUrlReject,
  } = props;

  const MAX_CHARS_IN_EMANDATE_DESCRIPTION_FIELD = 70;

  const buckarooOptions = [
    { label: 'iDEAL', value: 'ideal' },
    { label: 'Emandate', value: 'emandate' },
  ];

  const sequenceTypeOptions = [
    { label: __('Recurring', 'eightshift-forms'), value: '0' },
    { label: __('One Off', 'eightshift-forms'), value: '1' },
  ];

  const fieldsForService = {
    ideal: [
      {
        name: 'donation-amount',
        required: true,
      },
      {
        name: 'issuer',
      },
    ],
    emandate: [
      {
        name: 'issuer',
      },
    ],
  };

  return (
    <Fragment>
      {onChangeService &&
        <SelectControl
          label={__('Service', 'eightshift-forms')}
          help={__('Please select which Buckaroo service you wish to use', 'eightshift-forms')}
          value={service}
          options={buckarooOptions}
          onChange={onChangeService}
        />
      }
      {fieldsForService[service] &&
        <BaseControl>
          <div className={`${blockClass}__fields-for-service`}>
            <h3>{__('When using this service, you should add fields with the following names: ', 'eightshift-forms')}</h3>
            <ul className={`${blockClass}__fields-for-service-list`}>
              {fieldsForService[service].map((serviceField, key) => {
                return (
                  <li key={key}>{!serviceField.required ? <i>{__('(Optional)', 'eightshift-forms')}</i> : ''} {serviceField.name}</li>
                );
              })}
            </ul>
          </div>
        </BaseControl>
      }
      {onChangeIsSequenceTypeOnFrontend && service === 'emandate' &&
        <ToggleControl
          label={__('Allow user to set Recurring / One time in form?', 'eightshift-forms')}
          help={__('If enabled you need to allow the user to select the recurring / one-time on frontend. You need to add a pre-defined field for this OR a field with name "sequence-type" to the form.', 'eightshift-forms')}
          checked={isSequenceTypeOnFrontend}
          onChange={onChangeIsSequenceTypeOnFrontend}
        />
      }
      {onChangeSequenceType && !isSequenceTypeOnFrontend && service === 'emandate' &&
        <SelectControl
          label={__('Recurring / One off?', 'eightshift-forms')}
          help={__('Set if this form will create a recurring or one-off emandate.', 'eightshift-forms')}
          value={sequenceType}
          options={sequenceTypeOptions}
          onChange={onChangeSequenceType}
        />
      }

      {onChangeEmandateDescription && service === 'emandate' &&
        <TextareaControl
          label={__('Emandate description', 'eightshift-forms')}
          value={emandateDescription}
          help={__('A description of the (purpose) of the emandate. This will be shown in the emandate information of the customers\' bank account. Max 70 characters.', 'eightshift-forms')}
          onChange={(newValue) => {
            onChangeEmandateDescription(newValue.substring(0, MAX_CHARS_IN_EMANDATE_DESCRIPTION_FIELD));
          }}
        />
      }

      {onChangeRedirectUrl &&
        <TextControl
          label={__('Redirect url (on success)', 'eightshift-forms')}
          value={redirectUrl}
          onChange={onChangeRedirectUrl}
        />
      }
      {onChangeRedirectUrlError &&
        <TextControl
          label={__('Redirect url (on error)', 'eightshift-forms')}
          value={redirectUrlError}
          onChange={onChangeRedirectUrlError}
        />
      }
      {onChangeRedirectUrlReject && service === 'ideal' &&
        <TextControl
          label={__('Redirect url (when payment rejected)', 'eightshift-forms')}
          value={redirectUrlReject}
          onChange={onChangeRedirectUrlReject}
        />
      }

    </Fragment>
  );
};
