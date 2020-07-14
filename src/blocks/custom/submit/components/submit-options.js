import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, SelectControl, ToggleControl } from '@wordpress/components';

export const SubmitOptions = (props) => {
  const {
    attributes: {
      name,
      value,
      id,
      classes,
      type,
      isDisabled,
    },
    actions: {
      onChangeName,
      onChangeValue,
      onChangeId,
      onChangeClasses,
      onChangeType,
      onChangeIsDisabled,
    },
  } = props;

  return (
    <PanelBody title={__('Submit Settings', 'eightshift-forms')}>
      {onChangeName &&
        <TextControl
          label={__('Name', 'eightshift-forms')}
          value={name}
          onChange={onChangeName}
        />
      }

      {onChangeValue &&
        <TextControl
          label={__('Value', 'eightshift-forms')}
          value={value}
          onChange={onChangeValue}
        />
      }

      {onChangeType &&
        <SelectControl
          label={__('Type', 'eightshift-forms')}
          value={type}
          options={[
            { label: __('Submit', 'infinum'), value: 'submit' },
            { label: __('Submit', 'infinum'), value: 'submit' },
            { label: __('Reset', 'infinum'), value: 'reset' },
          ]}
          onChange={onChangeType}
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

      {onChangeIsDisabled &&
        <ToggleControl
          label={__('Disabled', 'eightshift-forms')}
          checked={isDisabled}
          onChange={onChangeIsDisabled}
        />
      }
    </PanelBody>
  );
};
