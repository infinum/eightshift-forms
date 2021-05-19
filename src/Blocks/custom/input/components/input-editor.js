import classNames from 'classnames'; // eslint-disable-line no-unused-vars
import { TextControl } from '@wordpress/components';
import { LabelEditor } from './../../../components/label/components/label-editor';

export const InputEditor = (props) => {
  const {
    attributes: {
      blockClass,
      label,
      value,
      id,
      type,
      theme = '',
    },
    actions: {
      onChangeLabel,
      onChangeValue,
    },
  } = props;

  const isHidden = type === 'hidden';
  const blockClasses = classNames(
    blockClass,
    isHidden ? `${blockClass}--hidden` : '',
  );

  const wrapClasses = classNames(
    `${blockClass}__content-wrap`,
    `${blockClass}__theme--${theme}`,
  );

  return (
    <div className={blockClasses}>
      {!isHidden &&
        <LabelEditor
          blockClass={blockClass}
          label={label}
          id={id}
          onChangeLabel={onChangeLabel}
        />
      }
      <div className={wrapClasses}>
        <TextControl
          label={label}
          value={value}
          onChange={onChangeValue}
        />
      </div>
    </div>
  );
};
