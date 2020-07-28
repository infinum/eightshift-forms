import { __ } from '@wordpress/i18n';
import { InnerBlocks } from '@wordpress/editor';

import { LabelEditor } from '../../../components/label/components/label-editor';

export const RadioEditor = (props) => {
  const {
    attributes: {
      blockClass,
      label,
      allowedBlocks,
      theme = '',
    },
  } = props;

  return (
    <div className={`${blockClass} ${blockClass}__theme--${theme}`}>
      <LabelEditor
        blockClass={blockClass}
        label={label}
      />
      <div className={`${blockClass}__content-wrap`}>
        <InnerBlocks
          allowedBlocks={(typeof allowedBlocks === 'undefined') || allowedBlocks}
          templateLock={false}
        />
      </div>
    </div>
  );
};
