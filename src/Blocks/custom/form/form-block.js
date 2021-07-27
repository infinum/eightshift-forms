import { Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { getActions } from '@eightshift/frontend-libs/scripts/editor';
import manifest from './manifest.json';
import { FormEditor } from './components/form-editor';
import { FormOptions } from './components/form-options';

export const Form = (props) => {
  const actions = getActions(props, manifest);

  return (
    <Fragment>
      <InspectorControls>
        <FormOptions {...props}/>
      </InspectorControls>
      <FormEditor {...props} />
    </Fragment>
  );
};
