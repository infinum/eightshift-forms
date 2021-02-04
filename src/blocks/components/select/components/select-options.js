import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, ToggleControl, SelectControl } from '@wordpress/components';

export const SelectOptions = (props) => {
  const {
    attributes: {
      name,
      isDisabled,
      preventSending,
      prefillData,
      prefillDataSource,
      showName = true,
    },
    actions: {
      onChangeName,
      onChangeIsDisabled,
      onChangePreventSending,
      onChangePrefillData,
      onChangePrefillDataSource,
    },
  } = props;

  const {
    prefill,
  } = window.eightshiftForms;

  const safePrefillMulti = prefill && prefill.multi ? prefill.multi : [];

  const prefillSourcesAsOptions = [
    { label: __('Select prefill source', 'eightshift-forms'), value: 'select-please' },
    ...safePrefillMulti.map((entity) => ({ label: entity.label, value: entity.value })),
  ];

  return (
    <PanelBody title={__('Select Settings', 'eightshift-forms')}>

      {onChangeName && showName &&
        <TextControl
          label={__('Name', 'eightshift-forms')}
          value={name}
          onChange={onChangeName}
        />
      }

      {onChangePrefillData &&
        <ToggleControl
          label={__('Prefill data?', 'eightshift-forms')}
          help={__('If enabled, this field\'s select options will be prefilled from a source of your choice.', 'eightshift-forms')}
          checked={prefillData}
          onChange={onChangePrefillData}
        />
      }

      {onChangePrefillData && prefillData &&
        <SelectControl
          label={__('Prefill from?', 'eightshift-forms')}
          help={__('Please select the source from which to prefill values.', 'eightshift-forms')}
          value={prefillDataSource}
          options={prefillSourcesAsOptions}
          onChange={onChangePrefillDataSource}
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
          help={__('If enabled this field won\'t be sent when the form is submitted.', 'eightshift-forms')}
          checked={preventSending}
          onChange={onChangePreventSending}
        />
      }

    </PanelBody>
  );
};
