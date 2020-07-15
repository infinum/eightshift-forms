import { Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/editor';

import { getActions } from '@eightshift/frontend-libs/scripts/editor';
import manifest from './manifest.json';

import { LabelOptions } from '../../components/label/components/label-options';
import { RadioOptions } from './components/radio-options';
import { RadioEditor } from './components/radio-editor';

export const Radio = (props) => {
  const {
    attributes,
    attributes: {
      label,
    },
    clientId,
  } = props;

  const actions = getActions(props, manifest);

  return (
    <Fragment>
      <InspectorControls>
        <LabelOptions
          label={label}
          onChangeLabel={actions.onChangeLabel}
        />
        <RadioOptions
          attributes={attributes}
          actions={actions}
          clientId={clientId}
        />
      </InspectorControls>
      <RadioEditor
        attributes={attributes}
        actions={actions}
      />
    </Fragment>
  );
};
