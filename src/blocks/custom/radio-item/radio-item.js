import { Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/editor';

import { getActions } from 'EighshiftBlocksGetActions';
import manifest from './manifest.json';

import { LabelOptions } from '../../components/label/components/label-options';
import { RadioItemEditor } from './components/radio-item-editor';
import { RadioItemOptions } from './components/radio-item-options';

export const RadioItem = (props) => {
  const {
    attributes,
  } = props;

  const actions = getActions(props, manifest);

  return (
    <Fragment>
      <InspectorControls>
        <RadioItemOptions
          attributes={attributes}
          actions={actions}
        />
      </InspectorControls>
      <RadioItemEditor
        attributes={attributes}
        actions={actions}
      />
    </Fragment>
  );
};
