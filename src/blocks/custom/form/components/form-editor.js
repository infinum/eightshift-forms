import { InnerBlocks } from '@wordpress/block-editor';

/**
 * Form editor.
 *
 * @param {object} props Props.
 */
export const FormEditor = (props) => {
  const {
    attributes: {
      blockClass,
      classes,
      id,
    },
  } = props;

  return (
    <form className={`${blockClass} ${classes}`} id={id}>
      <InnerBlocks
        templateLock={false}
      />
    </form>
  );
};
