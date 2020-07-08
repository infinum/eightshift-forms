import { __ } from '@wordpress/i18n';

import { LabelEditor } from '../../../components/label/components/label-editor';

export const TextareaEditor = (props) => {
  const {
    attributes: {
      blockClass,
      label,
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
  } = props;

  return (
    <div className={`${blockClass}`}>
      <LabelEditor
        blockClass={blockClass}
        label={label}
        id={id}
      />
      <div className={`${blockClass}__content-wrap`}>
        <textarea
          name={name}
          placeholder={placeholder}
          id={id}
          rows={rows}
          cols={cols}
          className={`${blockClass}__textarea ${classes}`}
          value={value}
          disabled={isDisabled}
          readOnly={isReadOnly}
        />
      </div>
    </div>
  );
};
