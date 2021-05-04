import _ from 'lodash';
import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { SelectControl } from '@wordpress/components';

/**
 * Options component
 *
 * @param {object} props Component props.
 */
export const FormMailerliteOptions = (props) => {
  const {
    groups,
    groupId,
    onChangeGroupId,
  } = props;

  const groupsOptions = groups.length && [
    {
      value: '',
      label: __('Please select group', 'eightshift-forms'),
    },
    ...groups,
  ];

  return (
    <Fragment>
      {onChangeGroupId &&
        <SelectControl
          label={__('Group ID', 'eightshift-forms')}
          help={__('Please select to which group does this form add members to', 'eightshift-forms')}
          value={groupId}
          options={groupsOptions}
          onChange={(newListId) => {
            onChangeGroupId(newListId);
          }}
        />
      }
    </Fragment>
  );
};
