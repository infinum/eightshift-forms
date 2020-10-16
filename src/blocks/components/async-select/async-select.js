import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { SelectControl, Spinner, BaseControl } from '@wordpress/components';

export const AsyncSelectControl = (props) => {

  const {
    label,
    defaultOptionLabel = __('Please select option', 'eightshift-forms'),
    noOptionsOption = [
      {
        label: __('No options available', 'eightshift-forms'),
        value: '',
      },
    ],
    help = '',
    value,
    options,
    onChange,
    isLoading,
  } = props;

  const selectOptions = [
    {
      label: defaultOptionLabel,
      value: null,
    },
    ...options,
  ];

  const hasOptions = options && options.length;

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
          options={hasOptions ? selectOptions : noOptionsOption}
          onChange={onChange}
        />
      }

    </Fragment>
  );
};

