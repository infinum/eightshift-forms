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
      {type==='submit' ?
        <submit
          name={name}
          id={id}
          className={`${blockClass}__submit ${classes}`}
          disabled={isDisabled}
        >
          {value}
        </submit> :
        <input
          name={name}
          id={id}
          className={`${blockClass}__submit ${classes}`}
          value={value}
          type={type}
          disabled={isDisabled}
        /> }
    </div>
  );
};
