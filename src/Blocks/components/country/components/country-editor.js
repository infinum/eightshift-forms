import React from 'react';
import { __ } from '@wordpress/i18n';
import { props, checkAttr, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { preventSaveOnMissingProps, StatusIconMissingName, StatusIconConditionals } from '../../utils';
import manifest from '../manifest.json';

export const CountryEditor = (attributes) => {
	const { blockClientId, prefix } = attributes;

	const countryName = checkAttr('countryName', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('countryName', attributes, manifest), countryName);

	const country = (
		<>
			<input
				className='esf-input'
				placeholder={__('This data will be provided by an external source select in the sidebar!', 'eightshift-forms')}
				disabled
			/>
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: country,
					fieldIsRequired: checkAttr('countryIsRequired', attributes, manifest),
				})}
				statusSlog={[
					!countryName && <StatusIconMissingName />,
					attributes?.[`${prefix}ConditionalTagsUse`] && <StatusIconConditionals />,
				]}
			/>
		</>
	);
};
