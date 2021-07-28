import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { SelectControl } from '@wordpress/components';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

/**
 * Options component
 *
 * @param {object} props Component props.
 */
export const FormMailerliteOptions = ({attributes, setAttributes}) => {
	const formMailerliteGroupId = checkAttr('formMailerliteGroupId', attributes, manifest);
	const formMailerliteGroups = checkAttr('formMailerliteGroups', attributes, manifest);

	const groupsOptions = formMailerliteGroups.length && [
		{
			value: '',
			label: __('Please select group', 'eightshift-forms'),
		},
		...formMailerliteGroups,
	];

	return (
		<>
			<SelectControl
				label={__('Group ID', 'eightshift-forms')}
				help={__('Please select to which group does this form add members to', 'eightshift-forms')}
				value={formMailerliteGroupId}
				options={groupsOptions}
				onChange={(value) => setAttributes({ [getAttrKey('formMailerliteGroupId', attributes, manifest)]: value })}
				/>
		</>
	);
};
