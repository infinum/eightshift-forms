import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { SelectControl } from '@wordpress/components';
import { getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FormDynamicsCrmOptions = (props) => {
  const {
		attributes,
		setAttributes,
    formType,
    formDynamicsEntity,
    crmEntitiesAsOptions,
    isDynamicsCrmUsed,
  } = props;

  return (
    <>
      {isDynamicsCrmUsed && formType === 'dynamics-crm' &&
        <SelectControl
          label={__('CRM Entity', 'eightshift-forms')}
          help={__('Please enter the name of the entity record to which you wish to add records.', 'eightshift-forms')}
          value={formDynamicsEntity}
          options={crmEntitiesAsOptions}
          onChange={(value) => setAttributes({ [getAttrKey('formDynamicsEntity', attributes, manifest)]: value })}
        />
      }

    </>
  );
};
