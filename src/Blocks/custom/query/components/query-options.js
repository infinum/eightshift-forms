/* global esFormsBlocksLocalization */

import React from 'react';
import { isArray } from 'lodash';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { SelectControl } from '@wordpress/components';
import {
	icons,
	props,
	checkAttr,
	IconLabel,
	getAttrKey,
	getOption
 } from '@eightshift/frontend-libs/scripts';
import { SelectOptions } from '../../../components/select/components/select-options';
import { CheckboxesOptions } from '../../../components/checkboxes/components/checkboxes-options';
import { RadiosOptions } from '../../../components/radios/components/radios-options';
import manifest from '../manifest.json';

export const QueryOptions = ({ attributes, setAttributes, clientId }) => {

	const queryData = checkAttr('queryData', attributes, manifest);
	const queryFieldType = checkAttr('queryFieldType', attributes, manifest);

	let queryDataOptions = [];

	if (typeof esFormsBlocksLocalization !== 'undefined' && isArray(esFormsBlocksLocalization?.queryBlockOptions)) {
		queryDataOptions = esFormsBlocksLocalization.queryBlockOptions;
	}

	return (
		<PanelBody title={__('Query', 'eightshift-forms')}>
			<SelectControl
				label={<IconLabel icon={icons.color} label={__('Field Type', 'eightshift-forms')} />}
				help={__('Set what field type you want to use.', 'eightshift-forms')}
				value={queryFieldType}
				options={getOption('queryFieldType', attributes, manifest)}
				onChange={(value) => setAttributes({ [getAttrKey('queryFieldType', attributes, manifest)]: value })}
			/>

			<SelectControl
				label={<IconLabel icon={icons.color} label={__('Data to show', 'eightshift-forms')} />}
				help={__('Set what data type you want to use.', 'eightshift-forms')}
				value={queryData}
				options={queryDataOptions}
				onChange={(value) => setAttributes({ [getAttrKey('queryData', attributes, manifest)]: value })}
			/>

			{(queryFieldType === 'select' || queryFieldType === '') &&
				<SelectOptions
					{...props('select', attributes, {
						setAttributes,
					})}
				/>
			}

			{queryFieldType === 'checkboxes' &&
				<CheckboxesOptions
					{...props('checkboxes', attributes, {
						setAttributes,
						clientId
					})}
				/>
			}

			{queryFieldType === 'radios' &&
				<RadiosOptions
					{...props('radios', attributes, {
						setAttributes,
					})}
				/>
			}
		</PanelBody>
	);
};
