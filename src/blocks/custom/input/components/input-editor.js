import { __ } from '@wordpress/i18n';

import { LabelEditor } from './../../../components/label/components/label-editor';

export const InputEditor = (props) => {
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
    },
  } = props;

  return (
    <div className={`${blockClass}`}>
      <LabelEditor
        blockClass={blockClass}
        label={label}
        id={id}
      />
      <div className={`${blockClass}__content-wrap`}>
        <input
          name={name}
          placeholder={placeholder}
          id={id}
          className={`${blockClass}__input ${classes}`}
          value={value}
          type={type}
          disabled={isDisabled}
          readOnly={isReadOnly}
        />
      </div>
    </div>
  );
};
