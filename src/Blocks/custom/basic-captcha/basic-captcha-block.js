import { Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';

import { getActions } from '@eightshift/frontend-libs/scripts/editor';
import manifest from './manifest.json';

import { LabelOptions } from '../../components/label/components/label-options';
import { BasicCaptchaEditor } from './components/basic-captcha-editor';

export const BasicCaptcha = (props) => {
  const {
    attributes,
    attributes: {
      label,
    },
  } = props;

  const actions = getActions(props, manifest);

  return (
    <>
      <InspectorControls>
        <LabelOptions
          label={label}
          onChangeLabel={actions.onChangeLabel}
        />
      </InspectorControls>
      <BasicCaptchaEditor
        attributes={attributes}
        actions={actions}
      />
    </>
  );
};
