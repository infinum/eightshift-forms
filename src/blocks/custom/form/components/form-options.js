import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';

export const FormOptions = (props) => {
  const {
    attributes: {
      action,
      method,
      target,
      id,
      classes,
      type,
      dynamicsEntity,
    },
    actions: {
      onChangeAction,
      onChangeMethod,
      onChangeTarget,
      onChangeId,
      onChangeClasses,
      onChangeType,
      onChangeDynamicsEntity,
    },
  } = props;

  return (
    <PanelBody title={__('Form Settings', 'eightshift-forms')}>
      {onChangeType &&
        <SelectControl
          label={__('Type', 'eightshift-forms')}
          value={type}
          help={__('Choose what will this form do on submit', 'eightshift-forms')}
          options={[
            { label: __('Email', 'eightshift-forms'), value: 'email' },
            { label: __('Microsoft Dynamics CRM 365', 'eightshift-forms'), value: 'dynamics-crm' },
            { label: __('Custom', 'eightshift-forms'), value: 'custom' },
          ]}
          onChange={onChangeType}
        />
      }

      {onChangeDynamicsEntity && type === 'dynamics-crm' &&
        <TextControl
          label={__('CRM Entity', 'eightshift-forms')}
          help={__('Please enter the name of the entity record to which you wish to add records.', 'eightshift-forms')}
          value={dynamicsEntity}
          onChange={onChangeDynamicsEntity}
        />
      }

      {onChangeAction && type === 'custom' &&
        <TextControl
          label={__('Action', 'eightshift-forms')}
          value={action}
          onChange={onChangeAction}
        />
      }

      {onChangeMethod && type === 'custom' &&
        <TextControl
          label={__('Method', 'eightshift-forms')}
          value={method}
          onChange={onChangeMethod}
        />
      }

      {onChangeTarget && type === 'custom' &&
        <TextControl
          label={__('Target', 'eightshift-forms')}
          value={target}
          onChange={onChangeTarget}
        />
      }

      {onChangeClasses &&
        <TextControl
          label={__('Classes', 'eightshift-forms')}
          value={classes}
          onChange={onChangeClasses}
        />
      }

      {onChangeId &&
        <TextControl
          label={__('ID', 'eightshift-forms')}
          value={id}
          onChange={onChangeId}
        />
      }
    </PanelBody>
  );
};
