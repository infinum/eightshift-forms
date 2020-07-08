import { __ } from '@wordpress/i18n';
import { InnerBlocks } from '@wordpress/editor';

import { LabelEditor } from '../../../components/label/components/label-editor';

export const RadioEditor = (props) => {
  const {
    attributes: {
      blockClass,
      label,
      allowedBlocks,
    },
  } = props;

  return (
    <div className={`${blockClass}`}>
      <LabelEditor
        blockClass={blockClass}
        label={label}
      />
      <div className={`${blockClass}__content-wrap`}>
        <InnerBlocks
          allowedBlocks={(typeof allowedBlocks === 'undefined') || allowedBlocks}
        />
      </div>
    </div>
  );
};
