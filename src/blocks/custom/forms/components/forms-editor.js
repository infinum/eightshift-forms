import { __ } from '@wordpress/i18n';
import { InnerBlocks } from '@wordpress/editor';

export const FormsEditor = (props) => {
  const {
    attributes: {
      blockClass,
      classes,
      id,
      allowedBlocks,
    },
  } = props;

  return (
    <forms className={`${blockClass} ${classes}`} id={id}>
      <InnerBlocks
        allowedBlocks={(typeof allowedBlocks === 'undefined') || allowedBlocks}
        templateLock={false}
      />
    </forms>
  );
};
