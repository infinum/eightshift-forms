import { __ } from '@wordpress/i18n';

export const LabelEditor = (props) => {
  const {
    blockClass,
    id,
    label,
  } = props;

  const componentClass = 'label';

  return (
    <div className={`${componentClass}__label-wrap ${blockClass}__label-wrap`}>
      <label for={id} className={`${componentClass} ${blockClass}__label`}>
        {label}
      </label>
    </div>
  );
};
