import { __ } from '@wordpress/i18n';
import { InnerBlocks } from '@wordpress/block-editor';
import { useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { Placeholder } from '@wordpress/components';

/**
 * Form editor.
 *
 * @param {object} props Props.
 */

export const FormEditor = (props) => {
  const {
    clientId,
    attributes: {
      blockClass,
      classes,
      id,
    },
  } = props;

  const [hasInnerBlocks, setHasInnerBlocks] = useState(false);

  useSelect((select) => {
    const hasInner = select('core/block-editor').getBlock(clientId).innerBlocks.length > 0;

    if (
      (!hasInnerBlocks && hasInner) ||
      (hasInnerBlocks && !hasInner)
    ) {
      setHasInnerBlocks(hasInner);
    }
  });

  return (
    <form className={`${blockClass} ${classes}`} id={id}>
      {!hasInnerBlocks &&
        <Placeholder
          icon="welcome-write-blog"
          label={__('This is an empty form. Click on me to start building your form.', 'eightshift-forms')}
        />
      }
      <InnerBlocks
        templateLock={false}
      />
    </form>
  );
};
