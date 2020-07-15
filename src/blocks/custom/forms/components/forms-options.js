import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { PostSelect } from '../../../components/post-select/components/post-select-options';

export const FormsOptions = (props) => {
  const {
    attributes: {
      selectedFormId,
    },
    actions: {
      onChangeSelectedFormId,
    },
  } = props;

  return (
    <PanelBody title={__('Form Settings', 'eightshift-forms')}>
      {onChangeSelectedFormId &&
        <PostSelect
          selectedPostId={selectedFormId}
          onChange={onChangeSelectedFormId}
          type="eightshift-forms"
        />
      }
    </PanelBody>
  );
};
