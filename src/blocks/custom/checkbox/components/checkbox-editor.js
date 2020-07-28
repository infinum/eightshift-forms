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
      description,
      id,
      classes,
      isChecked,
      isDisabled,
      isReadOnly,
      theme = '',
    },
    actions: {
      onChangeDescription,
    },
  } = props;

  return (
    <div className={`${blockClass} ${blockClass}__theme--${theme}`}>
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
        <RichText
          className={`${blockClass}__description`}
          placeholder={__('Add your description', 'eightshift-forms')}
          onChange={onChangeDescription}
          value={description}
        />
      </div>
    </div>
  );
};
