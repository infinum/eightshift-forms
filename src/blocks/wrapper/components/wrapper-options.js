import { __ } from '@wordpress/i18n';
import { PanelBody, SelectControl } from '@wordpress/components';

import globalSettings from './../../manifest.json';

export const WrapperOptions = (props) => {
  const {
    attributes: {
      styleContentWidth,
    },
    actions: {
      onChangeStyleContentWidth,
    },
  } = props;

  const { maxCols } = globalSettings;
  const colsOutput = [];

  for (let index = 1; index <= maxCols; index++) {
    colsOutput.push({ label: `${index} - (${Math.round((100 / maxCols) * index)}%)`, value: index });
  }

  return (
    <PanelBody title={__('Field', 'eightshift-forms')}>
      {onChangeStyleContentWidth &&
        <SelectControl
          label={__('Width', 'eightshift-forms')}
          value={styleContentWidth}
          options={colsOutput}
          onChange={onChangeStyleContentWidth}
        />
      }
    </PanelBody>
  );
};
