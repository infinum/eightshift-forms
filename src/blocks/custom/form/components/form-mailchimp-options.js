import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { SelectControl, ToggleControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { MAILCHIMP_FETCH_SEGMENTS_STORE } from '../../../stores/mailchimp-fetch-segments';

export const FormMailchimpOptions = (props) => {
  const {
    // listId,
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

  const listId = 'eb7fd0b84a';

  console.log('List ID: ', listId);

  const tagsAndSegments = useSelect((select) => {
    const response = select(MAILCHIMP_FETCH_SEGMENTS_STORE).receiveSegments(listId);
    const defaultResponse = {
      tags: [
        {
          label: 'No tags found for route',
          value: tag || 0,
        },
      ],
      segments: [
        {
          label: 'No segments found for route',
          value: tag || 0,
        },
      ],
    };
    return response && response.data && response.data.segments ? response.data.segments.map((segment) => {
      console.log(segment);
    }) : defaultResponse;
  }, [listId]);

  console.log(count);

  const tags = [];
  const segments = [];

  const audienceOptions = audiences.length ? audiences : [
    {
      label: __('ERROR unable to read audience list from Mailchimp', 'eightshift-forms'),
      value: listId,
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
          onChange={onChangeListId}
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
        <SelectControl
          label={__('Tag', 'eightshift-forms')}
          help={__('Select which tag to add to user', 'eightshift-forms')}
          value={tag}
          options={tags}
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
        <SelectControl
          label={__('Segment', 'eightshift-forms')}
          help={__('Select which segment to add to user', 'eightshift-forms')}
          value={segment}
          options={segments}
          onChange={onChangeSegment}
        />
      }
    </Fragment>
  );
};
