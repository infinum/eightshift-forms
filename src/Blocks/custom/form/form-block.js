import { Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { FormEditor } from './components/form-editor';
import { FormOptions } from './components/form-options';

export const Form = (props) => {
  return (
    <Fragment>
      <InspectorControls>
        <FormOptions {...props}/>
      </InspectorControls>
      <FormEditor {...props} />
    </Fragment>
  );
};
