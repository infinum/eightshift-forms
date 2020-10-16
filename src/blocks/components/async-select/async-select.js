import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { SelectControl, Spinner, BaseControl } from '@wordpress/components';

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
      value: null,
    },
    ...options,
  ] : [
    {
      label: noOptionsLabel,
      value: '',
    },
  ];

  return (
    <Fragment>
      {isLoading &&
        <BaseControl label={label} help={help}>
          <div className="async-select__spinner-wrapper">
            <Spinner />
          </div>
        </BaseControl>
      }
      {!isLoading &&
        <SelectControl
          label={label}
          help={help}
          value={value}
          options={selectOptions}
          onChange={onChange}
        />
      }

    </Fragment>
  );
};

