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
            { label: __('Text', 'infinum'), value: 'text' },
            { label: __('Url', 'infinum'), value: 'url' },
            { label: __('Email', 'infinum'), value: 'email' },
            { label: __('Number', 'infinum'), value: 'nmber' },
            { label: __('Password', 'infinum'), value: 'password' },
            { label: __('Color', 'infinum'), value: 'color' },
            { label: __('Date', 'infinum'), value: 'date' },
            { label: __('Date Time Local', 'infinum'), value: 'datetime-local' },
            { label: __('Image', 'infinum'), value: 'image' },
            { label: __('Month', 'infinum'), value: 'month' },
            { label: __('Range', 'infinum'), value: 'range' },
            { label: __('Search', 'infinum'), value: 'search' },
            { label: __('Tel', 'infinum'), value: 'tel' },
            { label: __('Time', 'infinum'), value: 'time' },
            { label: __('Week', 'infinum'), value: 'week' },
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
    </PanelBody>
  );
};
