import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';

export const TextareaOptions = (props) => {
  const {
    attributes: {
      name,
      value,
      id,
      placeholder,
      classes,
      rows,
      cols,
      isDisabled,
      isReadOnly,
    },
    actions: {
      onChangeName,
      onChangeValue,
      onChangeId,
      onChangePlaceholder,
      onChangeClasses,
      onChangeRows,
      onChangeCols,
      onChangeIsDisabled,
      onChangeIsReadOnly,
    },
  } = props;

  return (
    <PanelBody title={__('Textarea Settings', 'eightshift-forms')}>
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

      {onChangeRows &&
        <TextControl
          label={__('Rows', 'eightshift-forms')}
          value={rows}
          onChange={onChangeRows}
        />
      }

      {onChangeCols &&
        <TextControl
          label={__('Columns', 'eightshift-forms')}
          value={cols}
          onChange={onChangeCols}
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
