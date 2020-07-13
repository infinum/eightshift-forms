import { __ } from '@wordpress/i18n';

const rerenderParent = (clientId) => {
  const select = wp.data.select('core/block-editor');
  const parentId = select.getBlockRootClientId(clientId);
  const parentBlock = select.getBlock(parentId);
  wp.data.dispatch('core/block-editor').updateBlock(parentId, parentBlock);
};

export const SelectOptionEditor = (props) => {
  const {
    attributes: {
      blockClass,
      label,
      value,
      isOptionSelected,
      isDisabled,
    },
    isSelected,
    clientId,
  } = props;

  if (!isSelected) {
    rerenderParent(clientId);
  }

  return (
    <option
      className={`${blockClass}__option`}
      value={value}
      selected={isOptionSelected}
      disabled={isDisabled}
    >
      {label}
    </option>
  );
};
