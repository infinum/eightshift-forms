import { __ } from '@wordpress/i18n';
import classNames from 'classnames'; // eslint-disable-line no-unused-vars
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
      theme = '',
    },
  } = props;

  const blockClasses = classNames(
    blockClass,
    type === 'hidden' ? `${blockClass}--hidden` : '',
  );

  const wrapClasses = classNames(
    `${blockClass}__content-wrap`,
    `${blockClass}__theme--${theme}`,
  );

  const inputClasses = classNames(
    `${blockClass}__input`,
    classes,
  );

  return (
    <div className={blockClasses}>
      <LabelEditor
        blockClass={blockClass}
        label={label}
        id={id}
      />
      <div className={wrapClasses}>
        <input
          name={name}
          placeholder={placeholder}
          id={id}
          className={inputClasses}
          value={value}
          type={type !== 'hidden' ? type : 'input'}
          disabled={isDisabled}
          readOnly={isReadOnly}
        />
      </div>
    </div>
  );
};
