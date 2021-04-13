import { __ } from '@wordpress/i18n';

export const getSinglePrefillSources = () => {
  const {
    prefill: {
      single = [],
    } = {},
  } = window.eightshiftForms;

  const label = single.length > 0 ? __('Select prefill source', 'eightshift-forms') : __('No prefill source defined', 'eightshift-forms')
  const prefillSourcesAsValues = [
    { label, value: 'select-please' },
    ...single,
  ];

  return prefillSourcesAsValues;
};
