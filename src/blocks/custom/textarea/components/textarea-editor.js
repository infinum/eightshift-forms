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
      theme = '',
    },
    actions: {
      onChangeLabel,
    },
  } = props;

  return (
    <div className={`${blockClass} ${blockClass}__theme--${theme}`}>
      <LabelEditor
        blockClass={blockClass}
        label={label}
        onChangeLabel={onChangeLabel}
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
          tabIndex={'-1'}
        />
      </div>
    </div>
  );
};
