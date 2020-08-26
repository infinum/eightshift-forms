import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, SelectControl, ToggleControl } from '@wordpress/components';

export const InputOptions = (props) => {
  const {
    attributes: {
      name,
      value,
      id,
      placeholder,
      classes,
      type,
      isDisabled,
      isReadOnly,
      isRequired,
    },
    actions: {
      onChangeName,
      onChangeValue,
      onChangeId,
      onChangePlaceholder,
      onChangeClasses,
      onChangeType,
      onChangeIsDisabled,
      onChangeIsReadOnly,
      onChangeIsRequired,
    },
  } = props;

  return (
    <PanelBody title={__('Input Settings', 'eightshift-forms')}>
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

      {onChangePlaceholder &&
        <TextControl
          label={__('Placeholder', 'eightshift-forms')}
          value={placeholder}
          onChange={onChangePlaceholder}
        />
      }

      {onChangeType &&
        <SelectControl
          label={__('Type', 'eightshift-forms')}
          value={type}
          options={[
            { label: __('Text', 'eightshift-forms'), value: 'text' },
            { label: __('Hidden', 'eightshift-forms'), value: 'hidden' },
            { label: __('Url', 'eightshift-forms'), value: 'url' },
            { label: __('Email', 'eightshift-forms'), value: 'email' },
            { label: __('Number', 'eightshift-forms'), value: 'number' },
            { label: __('Password', 'eightshift-forms'), value: 'password' },
            { label: __('Color', 'eightshift-forms'), value: 'color' },
            { label: __('Date', 'eightshift-forms'), value: 'date' },
            { label: __('Date Time Local', 'eightshift-forms'), value: 'datetime-local' },
            { label: __('Image', 'eightshift-forms'), value: 'image' },
            { label: __('Month', 'eightshift-forms'), value: 'month' },
            { label: __('Range', 'eightshift-forms'), value: 'range' },
            { label: __('Search', 'eightshift-forms'), value: 'search' },
            { label: __('Tel', 'eightshift-forms'), value: 'tel' },
            { label: __('Time', 'eightshift-forms'), value: 'time' },
            { label: __('Week', 'eightshift-forms'), value: 'week' },
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

      {onChangeIsReadOnly &&
        <ToggleControl
          label={__('Readonly', 'eightshift-forms')}
          checked={isReadOnly}
          onChange={onChangeIsReadOnly}
        />
      }

      {onChangeIsRequired &&
        <ToggleControl
          label={__('Required', 'eightshift-forms')}
          checked={isRequired}
          onChange={onChangeIsRequired}
        />
      }
    </PanelBody>
  );
};
