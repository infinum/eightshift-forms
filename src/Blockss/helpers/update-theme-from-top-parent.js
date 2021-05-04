import { useSelect } from '@wordpress/data';

/**
 * Updates this block's theme from it's top lvl parent (should be form block).
 *
 * @param {string} clientId Current block's clientId.
 * @param {*} onChange onChange method for changing theme.
 */
export const updateThemeFromTopParent = (clientId, onChange) => {

  const parentClientId = useSelect((select) => {
    const parents = select('core/block-editor').getBlockParents(clientId);
    return parents[0] || '';
  });

  const {
    attributes: {
      theme = '',
    },
  } = useSelect((select) => {
    return select('core/block-editor').getBlocksByClientId(parentClientId)[0] || {};
  });

  onChange(theme);
};
