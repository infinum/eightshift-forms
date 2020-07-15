import { Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/editor';

import { getActions } from '@eightshift/frontend-libs/scripts/editor';
import manifest from './manifest.json';

import { LabelOptions } from '../../components/label/components/label-options';
import { CheckboxEditor } from './components/checkbox-editor';
import { CheckboxOptions } from './components/checkbox-options';

export const Checkbox = (props) => {
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
        <CheckboxOptions
          attributes={attributes}
          actions={actions}
        />
      </InspectorControls>
      <CheckboxEditor
        attributes={attributes}
        actions={actions}
      />
    </Fragment>
  );
};
