import { __ } from '@wordpress/i18n';

export const RadioItemEditor = (props) => {
  const {
    attributes: {
      blockClass,
      label,
      name,
      value,
      id,
      classes,
      isChecked,
      isDisabled,
      isReadOnly,
    },
  } = props;

  return (
    <div className={`${blockClass}`}>
      <input
        name={name}
        id={id}
        className={`${blockClass}__radio ${classes}`}
        value={value}
        type='radio'
        checked={isChecked}
        disabled={isDisabled}
        readOnly={isReadOnly}
      />
      <label for={id} class={`${blockClass}__label`}>
        {label}
      </label>
    </div>
  );
};
