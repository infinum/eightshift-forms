import { useState } from '@wordpress/element';
import { InnerBlocks } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { getAllChildrenRecursive, updateThemeForAllBlocks } from './update-theme-helpers';

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
    theme,
  } = props;

  // Update theme for any new block.
  const [allChildren, setAllChildren] = useState([]);
  const block = useSelect((select) => {
    return select('core/block-editor').getBlock(clientId);
  });
  const currentChildren = getAllChildrenRecursive(block);
  if (currentChildren.length > allChildren.length) {
    updateThemeForAllBlocks(currentChildren, theme);
    setAllChildren(currentChildren);
  }


  return (
    <form className={`${blockClass} ${classes}`} id={id}>
      <InnerBlocks
        templateLock={false}
      />
    </form>
  );
};
