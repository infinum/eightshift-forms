import { Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';

import { getActions } from '@eightshift/frontend-libs/scripts/editor';
import manifest from './manifest.json';

import { LabelOptions } from '../../components/label/components/label-options';
import { SelectOptions } from '../../components/select/components/select-options';
import { SelectEditor } from '../../components/select/components/select-editor';

export const Select = (props) => {
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
        <SelectOptions
          attributes={attributes}
          actions={actions}
          clientId={clientId}
        />
      </InspectorControls>
      <SelectEditor {...props} actions={actions} />
    </Fragment>
  );
};
