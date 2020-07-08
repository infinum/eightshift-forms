import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';

export const SelectOptions = (props) => {
  const {
    attributes: {
      name,
      isDisabled,
    },
    actions: {
      onChangeName,
      onChangeIsDisabled,
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

    </PanelBody>
  );
};
