import { __ } from '@wordpress/i18n';
import { InnerBlocks } from '@wordpress/editor';

import { LabelEditor } from '../../../components/label/components/label-editor';

export const SelectEditor = (props) => {
  const {
    attributes: {
      blockClass,
      label,
      allowedBlocks,
      name,
      id,
      classes,
      isDisabled,
    },
  } = props;

  return (
    <div className={`${blockClass}`}>
      <LabelEditor
        blockClass={blockClass}
        label={label}
      />
      <div className={`${blockClass}__content-wrap`}>
        <select
          name={name}
          disabled={isDisabled}
          id={id}
          className={`${blockClass}__select ${classes}`}
        >
          <InnerBlocks
            allowedBlocks={(typeof allowedBlocks === 'undefined') || allowedBlocks}
          />
        </select>
      </div>
    </div>
  );
};
