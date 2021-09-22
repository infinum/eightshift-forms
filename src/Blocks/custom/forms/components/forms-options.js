import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, Button, BaseControl } from '@wordpress/components';
import {
	CustomSelect,
	IconLabel,
	icons,
	getAttrKey,
	checkAttr,
	getFetchWpApi,
	unescapeHTML
} from '@eightshift/frontend-libs/scripts';
import globalManifest from './../../../manifest.json';
import manifest from '../manifest.json';

export const FormsOptions = ({ attributes, setAttributes }) => {
	const {
		settingsPageUrl,
	} = globalManifest;

	const {
		postType,
	} = manifest;

	const formsForm = checkAttr('formsForm', attributes, manifest);

	return (
		<PanelBody title={__('Forms', 'eightshift-forms')}>
			<CustomSelect
				label={<IconLabel icon={icons.file} label={__('Select form', 'eightshift-forms')} />}
				help={__('Select form from the list that is going to be shown to the user.', 'eightshift-forms')}
				value={formsForm}
				loadOptions={getFetchWpApi(postType, {processLabel: ({ title: { rendered: renderedTitle } }) => unescapeHTML(renderedTitle) })}
				onChange={(value) => {setAttributes({[getAttrKey('formsForm', attributes, manifest)]: value.value})}}
				isClearable={false}
				reFetchOnSearch={true}
			/>

			<hr />

			<BaseControl
				label={<IconLabel icon={icons.options} label={__('Settings', 'eightshift-forms')} />}
				help={__('On settings page you can setup email settings, integrations and much more.', 'eightshift-forms')}
			>
				<Button
					label={__('Open Form Settings Page', 'eightshift-forms')}
					href={`${settingsPageUrl}${formsForm}`}
					isSecondary
				>
					{__('Open Form Settings', 'eightshift-forms')}
				</Button>
			</BaseControl>
		</PanelBody>
	);
};
