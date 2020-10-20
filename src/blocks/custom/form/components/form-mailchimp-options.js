import _ from 'lodash';
import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { SelectControl, ToggleControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { AsyncFormTokenField } from '../../../components/async-form-token-field/async-form-token-field';
import { MAILCHIMP_FETCH_SEGMENTS_STORE } from '../../../stores/all';

/**
 * Fetches tags segments for Mailchimp audience with listId from store.
 *
 * @param {string} listId List for which to fetch tags / segments.
 */
const getTags = (listId) => {
  return useSelect((select) => {
    const response = select(MAILCHIMP_FETCH_SEGMENTS_STORE).receiveResponse([
      {
        key: 'list-id',
        value: listId,
      },
    ]);

    // Response if there was an error.
    if (!response || !response.data || response.code !== 200 || !response.data.tags || !response.data.segments) {
      return {
        isLoading: _.isEmpty(response),
        suggestions: [],
      };
    }

    return {
      isLoading: false,
      suggestions: response.data.tags.map((currentTag) => (currentTag.name)),
    };
  }, [listId]);
};

/**
 * Options component
 *
 * @param {object} props Component props.
 */
export const FormMailchimpOptions = (props) => {
  const {
    listId,
    audiences,
    addTag,
    tags,
    onChangeListId,
    onChangeAddTag,
    onChangeTags,
  } = props;

  const {
    isLoading,
    suggestions,
  } = getTags(listId);

  const audienceOptions = audiences.length ? [
    {
      value: '',
      label: __('Please select audience', 'eightshift-forms'),
    },
    ...audiences,
  ] : [
    {
      value: listId,
      label: __('ERROR unable to read audience list from Mailchimp', 'eightshift-forms'),
    },
  ];

  return (
    <Fragment>
      {onChangeListId &&
        <SelectControl
          label={__('List ID (Audience)', 'eightshift-forms')}
          help={__('Please select which list does this form add members to', 'eightshift-forms')}
          value={listId}
          options={audienceOptions}
          onChange={(newListId) => {
            onChangeTags([]);
            onChangeListId(newListId);
          }}
        />
      }
      {onChangeAddTag &&
        <ToggleControl
          label={__('Add tag(s) to member?', 'eightshift-forms')}
          help={__('If enabled, the form will add selected tag(s) to member on submit.', 'eightshift-forms')}
          checked={addTag}
          onChange={onChangeAddTag}
        />
      }
      {onChangeTags && addTag &&
        <AsyncFormTokenField
          label={__('Tag(s)', 'eightshift-forms')}
          help={__('Select which tag to add to user. Will autofill found tags from Mailchimp. Any new tags you enter will be created', 'eightshift-forms')}
          placeholder={__('Start typing to add tags', 'eightshift-forms')}
          value={tags}
          suggestions={suggestions}
          isLoading={isLoading}
          onChange={onChangeTags}
        />
      }
    </Fragment>
  );
};
