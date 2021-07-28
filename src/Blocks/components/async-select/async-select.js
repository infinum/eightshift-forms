import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { SelectControl } from '@wordpress/components';
import { EditorSpinner } from '../editor-spinner/editor-spinner-editor';

export const AsyncSelectControl = (props) => {

  const {
    label,
    defaultOptionLabel = __('Please select option', 'eightshift-forms'),
    noOptionsLabel = __('No options available', 'eightshift-forms'),
    help = '',
    value,
    options,
    onChange,
    isLoading,
  } = props;

  const selectOptions = options && options.length ? [
    {
      label: defaultOptionLabel,
      value: '',
    },
    ...options,
  ] : [
    {
      label: noOptionsLabel,
      value: '',
    },
  ];

  return (
    <>
      {isLoading &&
        <EditorSpinner label={label} help={help} />
      }
      {!isLoading &&
        <SelectControl
          multiple={true}
          label={label}
          help={help}
          value={value}
          options={selectOptions}
          onChange={onChange}
        />
      }

    </>
  );
};

