import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { SelectControl, TextControl } from '@wordpress/components';


export const FormBuckarooOptions = (props) => {
  const {
    service,
    redirectUrl,
    redirectUrlCancel,
    redirectUrlError,
    redirectUrlReject,
    onChangeService,
    onChangeRedirectUrl,
    onChangeRedirectUrlCancel,
    onChangeRedirectUrlError,
    onChangeRedirectUrlReject,
  } = props;

  const buckarooOptions = [
    { label: 'iDEAL', value: 'ideal' },
    { label: 'Emandate', value: 'emandate' },
  ];

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

      {onChangeRedirectUrl &&
        <TextControl
          label={__('Redirect url (on success)', 'eightshift-forms')}
          value={redirectUrl}
          onChange={onChangeRedirectUrl}
        />
      }
      {onChangeRedirectUrlCancel &&
        <TextControl
          label={__('Redirect url (when payment cancelled)', 'eightshift-forms')}
          value={redirectUrlCancel}
          onChange={onChangeRedirectUrlCancel}
        />
      }
      {onChangeRedirectUrlError &&
        <TextControl
          label={__('Redirect url (on error)', 'eightshift-forms')}
          value={redirectUrlError}
          onChange={onChangeRedirectUrlError}
        />
      }
      {onChangeRedirectUrlReject &&
        <TextControl
          label={__('Redirect url (when payment rejected)', 'eightshift-forms')}
          value={redirectUrlReject}
          onChange={onChangeRedirectUrlReject}
        />
      }

    </Fragment>
  );
};
