/* global esFormsBlocksLocalization */

import React from 'react';
import { isArray } from 'lodash';
import { __ } from '@wordpress/i18n';
import { PanelBody, SelectControl, TextControl, Button, BaseControl } from '@wordpress/components';
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
import globalManifest from '../../../manifest.json';

export const FormsOptions = ({ attributes, setAttributes }) => {
	const {
		editFormUrl,
	} = globalManifest;

	const {
		postType,
	} = manifest;

	const formsFormPostId = checkAttr('formsFormPostId', attributes, manifest);
	const formsStyle = checkAttr('formsStyle', attributes, manifest);
	const formsFormTypeSelector = checkAttr('formsFormTypeSelector', attributes, manifest);

	let formsStyleOptions = [];

	if (typeof esFormsBlocksLocalization !== 'undefined' && isArray(esFormsBlocksLocalization?.formsBlockStyleOptions)) {
		formsStyleOptions = esFormsBlocksLocalization.formsBlockStyleOptions;
	}

	return (
		<PanelBody title={__('Forms', 'eightshift-forms')}>
			<CustomSelect
				label={<IconLabel icon={icons.dropdown} label={__('Select form', 'eightshift-forms')} />}
				help={__('Select form from the list that is going to be shown to the user.', 'eightshift-forms')}
				value={parseInt(formsFormPostId)}
				loadOptions={getFetchWpApi(postType, {processLabel: ({ title: { rendered: renderedTitle } }) => unescapeHTML(renderedTitle) })}
				onChange={(value) => setAttributes({[getAttrKey('formsFormPostId', attributes, manifest)]: value.toString()})}
				isClearable={false}
				reFetchOnSearch={true}
				multiple={false}
				simpleValue
			/>

			{formsFormPostId &&
				<BaseControl>
					<Button
						href={`${editFormUrl}&post=${formsFormPostId}`}
						isSecondary
					>
						{__('Edit Form details', 'eightshift-forms')}
					</Button>
				</BaseControl>
			}

			<TextControl
				label={<IconLabel icon={icons.code} label={__('Type Selector', 'eightshift-forms')} />}
				help={__('Set additional data type selector for the form.', 'eightshift-forms')}
				value={formsFormTypeSelector}
				onChange={(value) => setAttributes({ [getAttrKey('formsFormTypeSelector', attributes, manifest)]: value })}
			/>

			{formsStyleOptions &&
				<SelectControl
					label={<IconLabel icon={icons.paletteColor} label={__('Style', 'eightshift-forms')} />}
					help={__('Set what style type is your form.', 'eightshift-forms')}
					value={formsStyle}
					options={formsStyleOptions}
					onChange={(value) => setAttributes({ [getAttrKey('formsStyle', attributes, manifest)]: value })}
				/>
			}
		</PanelBody>
	);
};
