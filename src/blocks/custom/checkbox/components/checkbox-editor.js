import { __ } from '@wordpress/i18n';

import { LabelEditor } from '../../../components/label/components/label-editor';

export const CheckboxEditor = (props) => {
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
      <LabelEditor
        blockClass={blockClass}
        label={label}
        id={id}
      />
      <div className={`${blockClass}__content-wrap`}>
        <input
          name={name}
          id={id}
          className={`${blockClass}__checkbox ${classes}`}
          value={value}
          type='checkbox'
          checked={isChecked}
          disabled={isDisabled}
          readOnly={isReadOnly}
        />
      </div>
    </div>
  );
};
