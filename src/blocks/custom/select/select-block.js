import { Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/editor';

import { getActions } from '@eightshift/frontend-libs/scripts/editor';
import manifest from './manifest.json';

import { LabelOptions } from '../../components/label/components/label-options';
import { SelectOptions } from './components/select-options';
import { SelectEditor } from './components/select-editor';

export const Select = (props) => {
  const {
    attributes,
    attributes: {
      label,
    },
    isSelected,
    clientId,
  } = props;

  console.log(props);

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
      <SelectEditor {...props} />
    </Fragment>
  );
};
