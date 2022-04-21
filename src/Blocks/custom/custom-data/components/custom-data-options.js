/* global esFormsBlocksLocalization */

import React from 'react';
import { isArray } from 'lodash';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import {
	icons,
	props,
	checkAttr,
	IconLabel,
	getAttrKey,
	getOption,
	CustomSelect
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

	if (customDataDataOptions?.length < 1) {
		return (
			<PanelBody title={\__('Custom data', 'eightshift-forms')}>
				{\__('Configure a custom data source to display the block options.', 'eightshift-forms')}
			</PanelBody>
		);
	}

	return (
		<>
			<PanelBody title={\__('Custom data', 'eightshift-forms')}>
				<CustomSelect
					label={<IconLabel icon={icons.fieldType} label={\__('Field type', 'eightshift-forms')} />}
					value={customDataFieldType}
					options={getOption('customDataFieldType', attributes, manifest)}
					onChange={(value) => setAttributes({ [getAttrKey('customDataFieldType', attributes, manifest)]: value })}
					simpleValue
					isSearchable={false}
					isClearable={false}
				/>

				<CustomSelect
					label={<IconLabel icon={icons.data} label={\__('Dataset', 'eightshift-forms')} />}
					value={customDataData}
					options={customDataDataOptions}
					onChange={(value) => setAttributes({ [getAttrKey('customDataData', attributes, manifest)]: value })}
					simpleValue
					isSearchable={false}
					isClearable={false}
				/>
			</PanelBody>

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
		</>
	);
};
