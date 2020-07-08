import React from 'react'; // eslint-disable-line no-unused-vars
import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { ToolbarGroup } from '@wordpress/components';

export const ImageToolbar = (props) => {
  const {
    media: {
      url,
    },
    onChangeMedia,
  } = props;

  const removeMedia = () => {
    onChangeMedia({});
  };

  return (
    <Fragment>
      {url &&
        <ToolbarGroup
          controls={[
            {
              icon: 'trash',
              title: __('Remove image', 'eightshift-forms'),
              isActive: false,
              onClick: removeMedia,
            },
          ]}
        />
      }
    </Fragment>
  );
};
