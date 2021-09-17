import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import {
	CustomSelect,
	IconLabel,
	icons,
	getAttrKey,
	checkAttr,
	getFetchWpApi,
	unescapeHTML
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FormsOptions = ({ attributes, setAttributes }) => {
	const {
		postType,
	} = manifest;

	const formsForm = checkAttr('formsForm', attributes, manifest);

	return (
		<PanelBody title={__('Forms', 'eightshift-forms')}>
			<CustomSelect
				label={<IconLabel icon={icons.file} label={__('Select form', 'eightshift-forms')} />}
				value={formsForm}
				loadOptions={getFetchWpApi(postType, {processLabel: ({ title: { rendered: renderedTitle } }) => unescapeHTML(renderedTitle) })}
				onChange={(value) => {setAttributes({[getAttrKey('formsForm', attributes, manifest)]: value.value})}}
				isClearable={false}
				reFetchOnSearch={true}
			/>
		</PanelBody>
	);
};
