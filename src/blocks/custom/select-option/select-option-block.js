import { Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/editor';

import { getActions } from '@eightshift/frontend-libs/scripts/editor';
import manifest from './manifest.json';

import { SelectOptionEditor } from './components/select-option-editor';
import { SelectOptionOptions } from './components/select-option-options';

export const SelectOption = (props) => {
  const {
    attributes,
  } = props;

  const actions = getActions(props, manifest);

  return (
    <Fragment>
      <InspectorControls>
        <SelectOptionOptions
          attributes={attributes}
          actions={actions}
        />
      </InspectorControls>
      <SelectOptionEditor
        {...props}
        actions={actions}
      />
    </Fragment>
  );
};
