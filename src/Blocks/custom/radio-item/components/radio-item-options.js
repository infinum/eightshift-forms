import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';

export const RadioItemOptions = (props) => {
  const {
    attributes: {
      label,
      value,
      id,
      classes,
      isChecked,
      isDisabled,
      isReadOnly,
    },
    actions: {
      onChangeLabel,
      onChangeValue,
      onChangeId,
      onChangeClasses,
      onChangeIsChecked,
      onChangeIsDisabled,
      onChangeIsReadOnly,
    },
  } = props;

  return (
    <PanelBody title={__('Radio Item Settings', 'eightshift-forms')}>
      {onChangeLabel &&
        <TextControl
          label={__('Label', 'eightshift-forms')}
          value={label}
          onChange={onChangeLabel}
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
