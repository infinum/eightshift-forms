import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, ToggleControl, SelectControl } from '@wordpress/components';
import { select, dispatch } from '@wordpress/data';

export const RadioOptions = (props) => {
  const {
    attributes: {
      name,
      prefillData,
      prefillDataSource,
    },
    actions: {
      onChangeName,
      onChangePrefillData,
      onChangePrefillDataSource,
    },
    clientId,
  } = props;

  // Once name is set on parent dispatch name attribute to all the children.
  const children = select('core/editor').getBlocksByClientId(clientId)[0];

  if (children) {
    children.innerBlocks.forEach(function (block) {
      dispatch('core/editor').updateBlockAttributes(block.clientId, { name: name })
    });
  }

  // Build the prefill options.
  const {
    prefill,
  } = window.eightshiftForms;

  const prefillSourcesAsOptions = [
    { label: __('Select prefill source', 'eightshift-forms'), value: 'select-please' },
    ...prefill.multi.map((entity) => ({ label: entity.label, value: entity.value })),
  ];

  return (
    <PanelBody title={__('Radio Settings', 'eightshift-forms')}>

      {onChangeName &&
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

    </PanelBody>
  );
};
