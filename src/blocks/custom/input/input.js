import { Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/editor';

import { getActions } from 'EighshiftBlocksGetActions';
import manifest from './manifest.json';

import { LabelOptions } from './../../components/label/components/label-options';
import { InputEditor } from './components/input-editor';
import { InputOptions } from './components/input-options';

export const Input = (props) => {
  const {
    attributes,
    attributes: {
      label,
    },
  } = props;

  const actions = getActions(props, manifest);

  return (
    <Fragment>
      <InspectorControls>
        <LabelOptions
          label={label}
          onChangeLabel={actions.onChangeLabel}
        />
        <InputOptions
          attributes={attributes}
          actions={actions}
        />
      </InspectorControls>
      <InputEditor
        attributes={attributes}
        actions={actions}
      />
    </Fragment>
  );
};
