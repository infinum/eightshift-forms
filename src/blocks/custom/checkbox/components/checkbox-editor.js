import { __ } from '@wordpress/i18n';
import { RichText } from '@wordpress/block-editor';
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
      theme = '',
    },
    actions: {
      onChangeLabel,
    },
  } = props;

  return (
    <div className={`${blockClass} ${blockClass}__theme--${theme}`}>
      <label className={`${blockClass}__label`}>
        <span className={`${blockClass}__label-content`}>
          <RichText
            className={`${blockClass}__label`}
            placeholder={__('Add your label', 'eightshift-forms')}
            onChange={onChangeLabel}
            value={label}
          />
        </span>
        <input
          name={name}
          id={id}
          className={`${blockClass}__checkbox ${classes}`}
          value={value}
          type="checkbox"
          checked={isChecked}
          disabled={isDisabled}
          readOnly={isReadOnly}
        />
        <span className={`${blockClass}__checkmark`}></span>
      </label>
    </div>
  );
};
