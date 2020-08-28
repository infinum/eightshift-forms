import { __ } from '@wordpress/i18n';
import { LabelEditor } from './../../../components/label/components/label-editor';

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
      theme = '',
    },
    actions: {
      onChangeLabel,
    },
  } = props;

  return (
    <div className={`${blockClass} ${blockClass}__theme--${theme}`}>
      <input
        name={name}
        id={id}
        className={`${blockClass}__radio ${classes}`}
        value={value}
        type="radio"
        checked={isChecked}
        disabled={isDisabled}
        readOnly={isReadOnly}
      />
      <LabelEditor
        blockClass={`${blockClass}__label`}
        label={label}
        id={id}
        onChangeLabel={onChangeLabel}
      />
    </div>
  );
};
