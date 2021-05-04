import { BaseControl } from '@wordpress/components';

export const NewSection = (props) => {
  const {
    label,
  } = props;

  return (
    <BaseControl>
      <hr />
      <div className={'notice-title'}>{label}</div>
    </BaseControl>
  );
};
