import { Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/editor';

import { getActions } from '@eightshift/frontend-libs/scripts/editor';
import manifest from './manifest.json';

import { FormEditor } from './components/form-editor';
import { FormOptions } from './components/form-options';

export const Form = (props) => {
  const {
    attributes,
  } = props;

  const actions = getActions(props, manifest);

  return (
    <Fragment>
      <InspectorControls>
        <FormOptions
          attributes={attributes}
          actions={actions}
        />
      </InspectorControls>
      <FormEditor
        attributes={attributes}
        actions={actions}
      />
    </Fragment>
  );
};
