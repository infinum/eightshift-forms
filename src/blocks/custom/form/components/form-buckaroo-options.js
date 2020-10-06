import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { SelectControl } from '@wordpress/components';


export const FormBuckarooOptions = (props) => {
  const {
    service,
    onChangeService,
  } = props;

  const buckarooOptions = [
    { label: 'iDEAL', value: 'ideal' },
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

    </Fragment>
  );
};
