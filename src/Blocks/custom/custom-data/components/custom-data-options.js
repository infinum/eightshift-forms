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

export const CustomDataOptions = ({ attributes, setAttributes, clientId }) => {

	const customDataData = checkAttr('customDataData', attributes, manifest);
	const customDataFieldType = checkAttr('customDataFieldType', attributes, manifest);

	let customDataDataOptions = [];

	if (typeof esFormsBlocksLocalization !== 'undefined' && isArray(esFormsBlocksLocalization?.customDataBlockOptions)) {
		customDataDataOptions = esFormsBlocksLocalization.customDataBlockOptions;
	}

	return (
		<PanelBody title={__('Custom Data', 'eightshift-forms')}>
			<SelectControl
				label={<IconLabel icon={icons.color} label={__('Field Type', 'eightshift-forms')} />}
				help={__('Set what field type you want to use.', 'eightshift-forms')}
				value={customDataFieldType}
				options={getOption('customDataFieldType', attributes, manifest)}
				onChange={(value) => setAttributes({ [getAttrKey('customDataFieldType', attributes, manifest)]: value })}
			/>

			<SelectControl
				label={<IconLabel icon={icons.color} label={__('Data to show', 'eightshift-forms')} />}
				help={__('Set what data type you want to use.', 'eightshift-forms')}
				value={customDataData}
				options={customDataDataOptions}
				onChange={(value) => setAttributes({ [getAttrKey('customDataData', attributes, manifest)]: value })}
			/>

			{(customDataFieldType === 'select' || customDataFieldType === '') &&
				<SelectOptions
					{...props('select', attributes, {
						setAttributes,
					})}
				/>
			}

			{customDataFieldType === 'checkboxes' &&
				<CheckboxesOptions
					{...props('checkboxes', attributes, {
						setAttributes,
						clientId
					})}
				/>
			}

			{customDataFieldType === 'radios' &&
				<RadiosOptions
					{...props('radios', attributes, {
						setAttributes,
					})}
				/>
			}
		</PanelBody>
	);
};
