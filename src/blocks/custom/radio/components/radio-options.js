import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl } from '@wordpress/components';
import { select, dispatch } from '@wordpress/data';

export const RadioOptions = (props) => {
  const {
    attributes: {
      name,
      preventSending,
    },
    actions: {
      onChangeName,
      onChangePreventSending,
    },
    clientId
  } = props;

  // Once name is set on parent dispatch name attribute to all the children.
  const children = select('core/editor').getBlocksByClientId(clientId)[0];

  if (children) {
    children.innerBlocks.forEach(function (block) {
      dispatch('core/editor').updateBlockAttributes(block.clientId, { name: name })
    });
  }

  return (
    <PanelBody title={__('Radio Settings', 'eightshift-forms')}>

      {onChangeName &&
        <TextControl
          label={__('Name', 'eightshift-forms')}
          value={name}
          onChange={onChangeName}
        />
      }

      {onChangePreventSending &&
        <ToggleControl
          label={__('Do not send?', 'eightshift-forms')}
          help={__('If enabled this field will not be sent when form is submitted.', 'eightshift-forms')}
          checked={preventSending}
          onChange={onChangePreventSending}
        />
      }

    </PanelBody>
  );
};
