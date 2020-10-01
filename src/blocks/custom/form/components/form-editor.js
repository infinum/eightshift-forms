import { __ } from '@wordpress/i18n';
import { InnerBlocks } from '@wordpress/editor';

export const FormEditor = (props) => {
  const {
    attributes: {
      blockClass,
      classes,
      id,
    },
  } = props;

  return (
    <form className={`${blockClass} ${classes}`} id={id}>
      <InnerBlocks
        templateLock={false}
      />
    </form>
  );
};
