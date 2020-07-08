import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';

export const CheckboxOptions = (props) => {
  const {
    attributes: {
      name,
      value,
      id,
      classes,
      isChecked,
      isDisabled,
      isReadOnly,
    },
    actions: {
      onChangeName,
      onChangeValue,
      onChangeId,
      onChangeClasses,
      onChangeIsChecked,
      onChangeIsDisabled,
      onChangeIsReadOnly,
    },
  } = props;

  return (
    <PanelBody title={__('Checkbox Settings', 'eightshift-forms')}>
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

      {onChangeIsChecked &&
        <ToggleControl
          label={__('Checked', 'eightshift-forms')}
          checked={isChecked}
          onChange={onChangeIsChecked}
        />
      }

      {onChangeIsDisabled &&
        <ToggleControl
          label={__('Disabled', 'eightshift-forms')}
          checked={isDisabled}
          onChange={onChangeIsDisabled}
        />
      }

      {onChangeIsReadOnly &&
        <ToggleControl
          label={__('Readonly', 'eightshift-forms')}
          checked={isReadOnly}
          onChange={onChangeIsReadOnly}
        />
      }
    </PanelBody>
  );
};
