import { __ } from '@wordpress/i18n';

export const SubmitEditor = (props) => {
  const {
    attributes: {
      blockClass,
      name,
      value,
      id,
      classes,
      type,
      isDisabled,
    },
  } = props;

  return (
    <div className={`${blockClass}`}>
      {type === 'button' ?
        <button
          name={name}
          id={id}
          className={`${blockClass}__button ${classes}`}
          disabled={isDisabled}
        >
          {value}
        </button> :
        <input
          name={name}
          id={id}
          className={`${blockClass}__input ${classes}`}
          value={value}
          type={type}
          disabled={isDisabled}
        />}
    </div>
  );
};
