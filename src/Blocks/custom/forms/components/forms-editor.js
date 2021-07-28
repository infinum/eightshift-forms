import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { Placeholder } from '@wordpress/components';

export const FormsEditor = (props) => {
  const {
    attributes: {
      selectedFormId,
    },
  } = props;

  const isFormSelected = selectedFormId && selectedFormId !== '0';

  return (
    <>
      {!isFormSelected &&
        <Placeholder
          icon="media-document"
          label={__('Please select form from dropdown in the sidebar.', 'eightshift-forms')}
        />
      }
      {isFormSelected &&
        <Placeholder
          icon="saved"
          label={__('Form will be rendered here.', 'eightshift-forms')}
        />
      }
    </>
  );
};
