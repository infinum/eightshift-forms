import React from 'react'; // eslint-disable-line no-unused-vars
import { __, sprintf } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { SelectControl, RangeControl, ToggleControl, Icon } from '@wordpress/components';
import { icons } from '@eightshift/frontend-libs/scripts/editor';
import globalSettings from '../../manifest.json';

export const WrapperResponsiveTabContent = (props) => {
  const {
    type,
    contentWidth,
    contentOffset,
    containerWidth,
    containerSpacing,
    spacingTop,
    spacingBottom,
    hideBlock,
    onChangeContentWidth,
    onChangeContentOffset,
    onChangeContainerWidth,
    onChangeContainerSpacing,
    onChangeSpacingTop,
    onChangeSpacingBottom,
    onChangeHideBlock,
  } = props;

  const spacingOptions = {
    min: -10,
    max: globalSettings.globalVariables.sectionSpacing.max,
    step: globalSettings.globalVariables.sectionSpacing.step,
  };

  const widthOptions = {
    min: -1,
    max: globalSettings.globalVariables.maxCols,
    step: 1,
    initial: globalSettings.globalVariables.maxCols,
  };

  const contentOffsetOptions = [
    { label: __('Not Set', 'eightshift-forms'), value: '' },
    { label: __('Content Spacing', 'eightshift-forms'), value: 'content-spacing' },
    { label: __('Center', 'eightshift-forms'), value: 'center' },
  ];

  const containerWidthOptions = [
    { label: __('Not Set', 'eightshift-forms'), value: '' },
    { label: __('Default', 'eightshift-forms'), value: 'default' },
  ];
  
  const containerSpacingOptions = [
    { label: __('Not Set', 'eightshift-forms'), value: '' },
    { label: __('Default', 'eightshift-forms'), value: 'default' },
    { label: __('No Spacing', 'eightshift-forms'), value: 'no-spacing' },
  ];

  return (
    <Fragment>
      {onChangeContentWidth && (
        <RangeControl
          label={
            <Fragment>
              <Icon icon={() => icons.width} />
              {__('Content Width', 'eightshift-forms')}
            </Fragment>
          }
          help={sprintf(__('Change block width in %d columns range. Example 6 = 50% screen width. If you set a value to -1 it will not be used and the parent brakepoint will be used.', 'eightshift-forms'), globalSettings.maxCols)}
          value={contentWidth[type]}
          onChange={onChangeContentWidth}
          min={widthOptions.min}
          max={widthOptions.max}
          step={widthOptions.step}
          initialPosition={widthOptions.initial}
        />
      )}

      {onChangeContentOffset && (
        <SelectControl
          label={
            <Fragment>
              <Icon icon={() => icons.offset} />
              {__('Content Offset', 'eightshift-forms')}
            </Fragment>
          }
          help={__('Change content position inside a block.', 'eightshift-forms')}
          value={contentOffset[type]}
          options={contentOffsetOptions}
          onChange={onChangeContentOffset}
        />
      )}

      {onChangeContainerWidth &&
        <SelectControl
          label={
            <Fragment>
              <Icon icon={() => icons.containerWidth} />
              {__('Container Width', 'eightshift-forms')}
            </Fragment>
          }
          help={__('Change Container width. Changing this option will affect total width for Content Width option.', 'eightshift-forms')}
          value={containerWidth[type]}
          options={containerWidthOptions}
          onChange={onChangeContainerWidth}
        />
      }

      {onChangeContainerSpacing &&
        <SelectControl
          label={
            <Fragment>
              <Icon icon={() => icons.containerSpacing} />
              {__('Container Spacing', 'eightshift-forms')}
            </Fragment>
          }
          help={__('Change Container spacing on the left and right.', 'eightshift-forms')}
          value={containerSpacing[type]}
          options={containerSpacingOptions}
          onChange={onChangeContainerSpacing}
        />
      }

      {onChangeSpacingTop &&
        <RangeControl
          label={
            <Fragment>
              <Icon icon={() => icons.spacingTop} />
              {__('Spacing Top', 'eightshift-forms')}
            </Fragment>
          }
          help={__('Change Block Spacing from the top. If you set a value to -10 it will not be used and the parent brakepoint will be used.', 'eightshift-forms')}
          value={spacingTop[type]}
          onChange={onChangeSpacingTop}
          min={spacingOptions.min}
          max={spacingOptions.max}
          step={spacingOptions.step}
        />
      }

      {onChangeSpacingBottom &&
        <RangeControl
          label={
            <Fragment>
              <Icon icon={() => icons.spacingBottom} />
              {__('Spacing Bottom', 'eightshift-forms')}
            </Fragment>
          }
          help={__('Change Block Spacing from the bottom. If you set a value to -10 it will not be used and the parent brakepoint will be used.', 'eightshift-forms')}
          value={spacingBottom[type]}
          onChange={onChangeSpacingBottom}
          min={spacingOptions.min}
          max={spacingOptions.max}
          step={spacingOptions.step}
        />
      }


      {onChangeHideBlock &&
        <ToggleControl
          label={__('Hide Block', 'eightshift-forms')}
          help={__('Toggle block visibility.', 'eightshift-forms')}
          checked={hideBlock[type]}
          onChange={onChangeHideBlock}
        />
      }
    </Fragment>
  );
};
