import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { SelectControl } from '@wordpress/components';


export const FormDynamicsCrmOptions = (props) => {
  const {
    type,
    crmEntitiesAsOptions,
    dynamicsEntity,
    isDynamicsCrmUsed,
    onChangeDynamicsEntity,
  } = props;

  return (
    <Fragment>
      {onChangeDynamicsEntity && isDynamicsCrmUsed && type === 'dynamics-crm' &&
        <SelectControl
          label={__('CRM Entity', 'eightshift-forms')}
          help={__('Please enter the name of the entity record to which you wish to add records.', 'eightshift-forms')}
          value={dynamicsEntity}
          options={crmEntitiesAsOptions}
          onChange={onChangeDynamicsEntity}
        />
      }

    </Fragment>
  );
};
