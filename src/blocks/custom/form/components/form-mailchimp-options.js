import _ from 'lodash';
import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { SelectControl, ToggleControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { AsyncSelectControl } from '../../../components/async-select/async-select';
import { MAILCHIMP_FETCH_SEGMENTS_STORE } from '../../../stores/all';

const getTagsAndSegments = (listId) => {
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
        routeResponse: response,
        isLoading: _.isEmpty(response),
        tags: [],
        segments: [],
      };
    }

    return {
      isLoading: false,
      tags: response.data.tags.map((currentTag) => {
        return {
          label: currentTag.name,
          value: currentTag.id,
        };
      }),
      segments: response.data.segments.map((currentSegment) => {
        return {
          label: currentSegment.name,
          value: currentSegment.id,
        };
      }),
    };
  }, [listId]);
};

export const FormMailchimpOptions = (props) => {
  const {
    listId,
    audiences,
    addTag,
    tag,
    addSegment,
    segment,
    onChangeListId,
    onChangeAddTag,
    onChangeTag,
    onChangeAddSegment,
    onChangeSegment,
  } = props;

  const tagsAndSegments = getTagsAndSegments(listId);
  console.log(tagsAndSegments);

  const {
    isLoading,
    tags,
    segments,
  } = tagsAndSegments;

  const audienceOptions = audiences.length ? [
    {
      value: '',
      label: __('Please select audience', 'eightshift-forms'),
    },
    ...audiences,
    {
      value: '12123',
      label: 'Temp mock audience',
    },
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
            onChangeTag(null);
            onChangeSegment(null);
            onChangeListId(newListId);
          }}
        />
      }
      {onChangeAddTag &&
        <ToggleControl
          label={__('Add tag to member?', 'eightshift-forms')}
          help={__('If enabled, the form will add selected tag to member on submit.', 'eightshift-forms')}
          checked={addTag}
          onChange={onChangeAddTag}
        />
      }
      {onChangeTag && addTag &&
        <AsyncSelectControl
          label={__('Tag', 'eightshift-forms')}
          help={__('Select which tag to add to user', 'eightshift-forms')}
          value={tag}
          options={tags}
          isLoading={isLoading}
          onChange={onChangeTag}
        />
      }
      {onChangeAddSegment &&
        <ToggleControl
          label={__('Put member in segment?', 'eightshift-forms')}
          help={__('If enabled, the form will add member to selected segment on submit.', 'eightshift-forms')}
          checked={addSegment}
          onChange={onChangeAddSegment}
        />
      }
      {onChangeSegment && addSegment &&
        <AsyncSelectControl
          label={__('Segment', 'eightshift-forms')}
          help={__('Select which segment to add to user', 'eightshift-forms')}
          value={segment}
          options={segments}
          isLoading={isLoading}
          onChange={onChangeSegment}
        />
      }
    </Fragment>
  );
};
