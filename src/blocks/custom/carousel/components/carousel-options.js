import React from 'react'; // eslint-disable-line no-unused-vars
import { __ } from '@wordpress/i18n';
import { PanelBody, ToggleControl } from '@wordpress/components';

export const CarouselOptions = ({ attributes, actions }) => {
  const {
    isLoop,
    isFreeMode,
  } = attributes;

  const {
    onChangeIsLoop,
    onChangeIsFreeMode,
  } = actions;

  return (
    <PanelBody title={__('Carousel Details', 'eightshift-forms')}>

      {onChangeIsFreeMode &&
        <ToggleControl
          label={__('Free Mode', 'eightshift-forms')}
          checked={isFreeMode}
          onChange={onChangeIsFreeMode}
        />
      }

      {onChangeIsLoop &&
        <ToggleControl
          label={__('Looped Mode', 'eightshift-forms')}
          checked={isLoop}
          onChange={onChangeIsLoop}
        />
      }

    </PanelBody>
  );
};
