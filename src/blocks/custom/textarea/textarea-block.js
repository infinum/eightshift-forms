import { Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';

import { getActions } from '@eightshift/frontend-libs/scripts/editor';
import manifest from './manifest.json';

import { LabelOptions } from '../../components/label/components/label-options';
import { TextareaEditor } from './components/textarea-editor';
import { TextareaOptions } from './components/textarea-options';

export const Textarea = (props) => {
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
        <TextareaOptions
          attributes={attributes}
          actions={actions}
        />
      </InspectorControls>
      <TextareaEditor
        attributes={attributes}
        actions={actions}
      />
    </Fragment>
  );
};
