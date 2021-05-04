import { __ } from '@wordpress/i18n';
import { PanelBody, SelectControl } from '@wordpress/components';
import { PostSelect } from '../../../components/post-select/components/post-select-options';

export const FormsOptions = (props) => {
  const {
    attributes: {
      theme,
      selectedFormId,
    },
    actions: {
      onChangeTheme,
      onChangeSelectedFormId,
    },
  } = props;

  const {
    hasThemes = false,
    themes = [],
  } = window.eightshiftForms;

  const themeAsOptions = [
    {
      label: __('Please select theme', 'eightshift-forms'),
      value: '',
    },
    ...themes.map((tempTheme) => ({ label: tempTheme, value: tempTheme })),
  ];

  return (
    <PanelBody title={__('Form Settings', 'eightshift-forms')}>
      {onChangeSelectedFormId &&
        <PostSelect
          selectedPostId={selectedFormId}
          onChange={onChangeSelectedFormId}
          type="eightshift-forms"
        />
      }
      {onChangeTheme && hasThemes &&
        <SelectControl
          label={__('Theme', 'eightshift-forms')}
          help={__('Choose your form theme.', 'eightshift-forms')}
          value={theme}
          options={themeAsOptions}
          onChange={onChangeTheme}
        />
      }
    </PanelBody>
  );
};
