import { __ } from '@wordpress/i18n';
import { RichText } from '@wordpress/block-editor';

export const LabelEditor = (props) => {
  const {
    blockClass,
    label,
    onChangeLabel,
  } = props;

  const componentClass = 'label';

  return (
    <div className={`${componentClass}__label-wrap ${blockClass}__label-wrap`}>
      {onChangeLabel &&
        <RichText
          className={`${blockClass}__label`}
          placeholder={__('Add your label', 'eightshift-forms')}
          onChange={onChangeLabel}
          value={label}
        />
      }
    </div>
  );
};
