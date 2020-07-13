import { __ } from '@wordpress/i18n';
import { InnerBlocks } from '@wordpress/editor';
import { Fragment } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';
import { LabelEditor } from '../../../components/label/components/label-editor';

const hasSelectedInnerBlock = (props) => {
  const select = wp.data.select('core/editor');
  const selected = select.getBlockSelectionStart();
  const inner = select.getBlock(props.clientId).innerBlocks ? select.getBlock(props.clientId).innerBlocks : [];
  for (let i = 0; i < inner.length; i++) {
    if (inner[i].clientId === selected || inner[i].innerBlocks.length && hasSelectedInnerBlock(inner[i])) {
      return true;
    }
  }
  return false;
};

export const SelectEditor = (props) => {
  const {
    attributes,
    attributes: {
      blockFullName,
      blockClass,
      label,
      allowedBlocks,
      name,
      id,
      classes,
      isDisabled,
    },
    isSelected,
  } = props;

  const isBlockOrChildrenSelected = isSelected || hasSelectedInnerBlock(props);

  console.log('is select selected: ', {
    isSelected,
    hasSelectedInnerBlock: hasSelectedInnerBlock(props),
    isBlockOrChildrenSelected,
  });
  
  return (
    <div className={`${blockClass}`}>
      <LabelEditor
        blockClass={blockClass}
        label={label}
      />
      <div className={`${blockClass}__content-wrap`}>
        {!isBlockOrChildrenSelected &&
          <ServerSideRender
            block={blockFullName}
            attributes={attributes}
            urlQueryArgs={{ cacheBusting: JSON.stringify(attributes) }}
          />
        }
        {isBlockOrChildrenSelected &&
          <div className={`${blockClass}__editor`}>
            <h2>{__('Add select options', 'd66')}</h2>
            <p>{__('Unselect this block to render it', 'd66')}</p>
            <InnerBlocks
              allowedBlocks={(typeof allowedBlocks === 'undefined') || allowedBlocks}
              templateLock={false}
            />
          </div>
        }
      </div>
    </div>
  );
};
