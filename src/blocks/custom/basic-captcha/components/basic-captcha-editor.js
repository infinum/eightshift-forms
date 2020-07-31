import { __ } from '@wordpress/i18n';

import { LabelEditor } from '../../../components/label/components/label-editor';

export const BasicCaptchaEditor = (props) => {
  const {
    attributes: {
      blockClass,
      label,
      name,
      value,
      id,
      placeholder,
      classes,
      type,
      isDisabled,
      isReadOnly,
      theme = '',
    },
  } = props;

  return (
    <div className={`${blockClass}`}>
      <LabelEditor
        blockClass={blockClass}
        label={label}
        id={id}
      />
      <div className={`${blockClass}__content-wrap ${blockClass}__theme--${theme}`}>
        <input
          name={name}
          placeholder={placeholder}
          id={id}
          className={`${blockClass}__basic-captcha ${classes}`}
          value={value}
          type={type}
          disabled={isDisabled}
          readOnly={isReadOnly}
        />
      </div>
    </div>
  );
};
