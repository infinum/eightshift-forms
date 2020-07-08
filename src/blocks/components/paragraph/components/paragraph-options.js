import React from 'react'; // eslint-disable-line no-unused-vars
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { ColorPaletteCustom } from '@eightshift/frontend-libs/scripts/components';
import { SelectControl, Icon } from '@wordpress/components';
import { icons } from '@eightshift/frontend-libs/scripts/editor';

export const paragraphSizes = [
  { label: __('Default (22px)', 'eightshift-forms'), value: 'default' },
  { label: __('Small (18px)', 'eightshift-forms'), value: 'small' },
];

export const ParagraphOptions = (props) => {
  const {
    paragraph: {
      styleColor,
      styleSize,
    },
    onChangeStyleColor,
    onChangeStyleSize,
  } = props;

  return (
    <Fragment>

      {onChangeStyleColor &&
        <ColorPaletteCustom
          label={
            <Fragment>
              <Icon icon={() => icons.color} />
              {__('Paragraph Color', 'eightshift-forms')}
            </Fragment>
          }
          help={__('Change Paragraph color.', 'eightshift-forms')}
          value={styleColor}
          onChange={onChangeStyleColor}
        />
      }

      {onChangeStyleSize &&
        <SelectControl
          label={__('Paragraph Font Size', 'eightshift-forms')}
          value={styleSize}
          options={paragraphSizes}
          onChange={onChangeStyleSize}
        />
      }

    </Fragment>
  );
};

