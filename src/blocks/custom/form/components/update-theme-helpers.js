import { useSelect } from '@wordpress/data';

/**
 * Returns all innerBlocks (recursively) of a block.
 *
 * @param {object} block Block object.
 */
export const getAllChildrenRecursive = (block) => {
  let children = [];

  // If there's no innerBlocks, there's no need to do anything.
  if (!block.innerBlocks.length) {
    return children;
  }

  // Check if innerBlock if it has innerBlocks of it's own.
  block.innerBlocks.forEach((child) => {
    if (child.innerBlocks.length) {
      children = [...children, ...getAllChildrenRecursive(child)];
    }
  });

  return [...children, ...block.innerBlocks];
};

/**
 * Updates theme attribute for all blocks in array.
 *
 * @param {array} blocks Array of block objects.
 * @param {string} theme Theme attribute to update
 */
export const updateThemeForAllBlocks = (blocks, theme) => {
  blocks.forEach((block) => {
    if (!block.attributes.theme || block.attributes.theme !== theme) {
      wp.data.dispatch('core/block-editor').updateBlockAttributes(block.clientId, { theme });
    }
  });
};
