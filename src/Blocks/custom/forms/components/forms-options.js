import React from 'react';
import { __ } from '@wordpress/i18n';
import {PanelBody, SelectControl} from '@wordpress/components';
import {
	CustomSelect,
	IconLabel,
	icons,
	getAttrKey,
	checkAttr,
	getFetchWpApi,
	unescapeHTML,
	getOption
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FormsOptions = ({ attributes, setAttributes }) => {
	const {
		postType,
	} = manifest;

	const formsFormPostId = checkAttr('formsFormPostId', attributes, manifest);
	const formsStyle = checkAttr('formsStyle', attributes, manifest);

	return (
		<PanelBody title={__('Forms', 'eightshift-forms')}>
			<CustomSelect
				label={<IconLabel icon={icons.file} label={__('Select form', 'eightshift-forms')} />}
				help={__('Select form from the list that is going to be shown to the user.', 'eightshift-forms')}
				value={formsFormPostId}
				loadOptions={getFetchWpApi(postType, {processLabel: ({ title: { rendered: renderedTitle } }) => unescapeHTML(renderedTitle) })}
				onChange={(value) => {setAttributes({[getAttrKey('formsFormPostId', attributes, manifest)]: value.value.toString()})}}
				isClearable={false}
				reFetchOnSearch={true}
				multiple={false}
			/>

			<SelectControl
				label={<IconLabel icon={icons.color} label={__('Style', 'eightshift-forms')} />}
				help={__('Set what style type is your form.', 'eightshift-forms')}
				value={formsStyle}
				options={getOption('formsStyle', attributes, manifest)}
				onChange={(value) => setAttributes({ [getAttrKey('formsStyle', attributes, manifest)]: value })}
			/>
		</PanelBody>
	);
};
