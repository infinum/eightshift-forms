import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { FormTokenField } from '@wordpress/components';
import { withState } from '@wordpress/compose';
import { EditorSpinner } from '../editor-spinner/editor-spinner-editor';

export const AsyncFormTokenField = withState()((props) => {
  const {
    suggestions,
    setState,
    label,
    help = '',
    placeholder = __('Start typing', 'eightshift-forms'),
    value,
    onChange,
    isLoading,

  } = props;

  return (
    <>
      {isLoading &&
        <EditorSpinner label={label} help={help} />
      }
      {!isLoading &&
        <FormTokenField
          label={label}
          help={help}
          placeholder={placeholder}
          value={value}
          suggestions={suggestions}
          onChange={(newTokens) => {
            setState({ tokens: newTokens });
            onChange(newTokens);
          }}
        />
      }
    </>

  );
});

