import { __ } from '@wordpress/i18n';

export const SelectOptionEditor = (props) => {
  const {
    attributes: {
      blockClass,
      label,
      value,
      isSelected,
      isDisabled,
    },
  } = props;

  return (
    <option
      className={`${blockClass}__option`}
      value={value}
      selected={isSelected}
      disabled={isDisabled}
    >
      {label}
    </option>
  );
};
