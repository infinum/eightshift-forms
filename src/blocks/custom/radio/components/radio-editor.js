import { __ } from '@wordpress/i18n';
import { InnerBlocks } from '@wordpress/editor';
import { Fragment } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';

import { LabelEditor } from '../../../components/label/components/label-editor';

export const RadioEditor = (props) => {
  const {
    attributes,
    attributes: {
      blockFullName,
      className,
      blockClass,
      label,
      allowedBlocks,
      theme = '',
      prefillData,
      prefillDataSource,
    },
    actions: {
      onChangeLabel,
    },
  } = props;

  const isPrefillUsed = prefillData && prefillDataSource;

  return (
    <Fragment>

      {isPrefillUsed &&
        <ServerSideRender
          block={blockFullName}
          attributes={attributes}
          urlQueryArgs={{ cacheBusting: JSON.stringify(attributes) }}
        />
      }

      {!isPrefillUsed &&
        <div className={`${blockClass} ${className} ${blockClass}__theme--${theme}`}>
          <LabelEditor
            blockClass={blockClass}
            label={label}
            onChangeLabel={onChangeLabel}
          />
          <div className={`${blockClass}__content-wrap`}>
            <InnerBlocks
              allowedBlocks={(typeof allowedBlocks === 'undefined') || allowedBlocks}
              templateLock={false}
            />
          </div>
        </div>
      }
    </Fragment>
  );
};
