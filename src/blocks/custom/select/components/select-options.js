import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';

export const SelectOptions = (props) => {
  const {
    attributes: {
      name,
      isDisabled,
      preventSending,
    },
    actions: {
      onChangeName,
      onChangeIsDisabled,
      onChangePreventSending,
    },
  } = props;

  return (
    <PanelBody title={__('Select Settings', 'eightshift-forms')}>

      {onChangeName &&
        <TextControl
          label={__('Name', 'eightshift-forms')}
          value={name}
          onChange={onChangeName}
        />
      }

      {onChangeIsDisabled &&
        <ToggleControl
          label={__('Disabled', 'eightshift-forms')}
          checked={isDisabled}
          onChange={onChangeIsDisabled}
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
