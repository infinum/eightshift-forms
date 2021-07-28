import _ from 'lodash';
import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { SelectControl, ToggleControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { AsyncFormTokenField } from '../../../components/async-form-token-field/async-form-token-field';
import { MAILCHIMP_FETCH_SEGMENTS_STORE } from '../../../stores/all';
import { getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

/**
 * Fetches tags segments for Mailchimp audience with formMailchimpListLid from store.
 *
 * @param {string} formMailchimpListLid List for which to fetch tags / segments.
 */
const getTags = (formMailchimpListLid) => {
	return useSelect((select) => {
		const response = select(MAILCHIMP_FETCH_SEGMENTS_STORE).receiveResponse([
			{
				key: 'list-id',
				value: formMailchimpListLid,
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
	}, [formMailchimpListLid]);
};

/**
 * Options component
 *
 * @param {object} props Component props.
 */
export const FormMailchimpOptions = (props) => {
	const {
		attributes,
		setAttributes,
		formMailchimpListLid,
		formMailchimpAudiences,
		formMailchimpAddTag,
		formMailchimpTags,
		formMailchimpAddExistingMembers,
	} = props;

	const {
		isLoading,
		suggestions,
	} = getTags(formMailchimpListLid);

	const audienceOptions = formMailchimpAudiences.length ? [
		{
			value: '',
			label: __('Please select audience', 'eightshift-forms'),
		},
		...formMailchimpAudiences,
	] : [
		{
			value: formMailchimpListLid,
			label: __('ERROR unable to read audience list from Mailchimp', 'eightshift-forms'),
		},
	];

	return (
		<Fragment>
			<SelectControl
				label={__('List ID (Audience)', 'eightshift-forms')}
				help={__('Please select which list does this form add members to', 'eightshift-forms')}
				value={formMailchimpListLid}
				options={audienceOptions}
				onChange={(value) => {
					setAttributes({ [getAttrKey('formMailchimpTags', attributes, manifest)]: [] });
					setAttributes({ [getAttrKey('formMailchimpListLid', attributes, manifest)]: value });
				}}
			/>
			<ToggleControl
				label={__('Add tag(s) to member?', 'eightshift-forms')}
				help={__('If enabled, the form will add selected tag(s) to member on submit.', 'eightshift-forms')}
				checked={formMailchimpAddTag}
				onChange={(value) => setAttributes({ [getAttrKey('formMailchimpAddTag', attributes, manifest)]: value })}
			/>
			{formMailchimpAddTag &&
				<AsyncFormTokenField
					label={__('Tag(s)', 'eightshift-forms')}
					help={__('Select which tag to add to user. Will autofill found tags from Mailchimp. Any new tags you enter will be created', 'eightshift-forms')}
					placeholder={__('Start typing to add tags', 'eightshift-forms')}
					value={formMailchimpTags}
					suggestions={suggestions}
					isLoading={isLoading}
					onChange={(value) => setAttributes({ [getAttrKey('formMailchimpTags', attributes, manifest)]: value })}
					/>
			}
			<ToggleControl
				label={__('Modify existing Mailchimp information on submit?', 'eightshift-forms')}
				help={__('If enabled, the form will modify existing user\'s Mailchimp information on submit. If disabled, it will only add new users to the list.', 'eightshift-forms')}
				checked={formMailchimpAddExistingMembers}
				onChange={(value) => setAttributes({ [getAttrKey('formMailchimpAddExistingMembers', attributes, manifest)]: value })}
				/>
		</Fragment>
	);
};
