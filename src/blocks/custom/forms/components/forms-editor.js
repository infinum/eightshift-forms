import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { Placeholder } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export const FormsEditor = (props) => {
  const {
    attributes: {
      blockFullName,
      selectedFormId,
    },
    attributes,
  } = props;

  const isFormSelected = selectedFormId && selectedFormId !== '0';

  return (
    <Fragment>
      {!isFormSelected &&
        <Placeholder
          icon="media-document"
          label={__('Please select form from dropdown in the sidebar.', 'eightshift-forms')}
        />
      }
      {isFormSelected &&
        <ServerSideRender
          block={blockFullName}
          attributes={attributes}
          urlQueryArgs={{ cacheBusting: JSON.stringify(attributes) }}
        />
      }
    </Fragment>
  );
};
