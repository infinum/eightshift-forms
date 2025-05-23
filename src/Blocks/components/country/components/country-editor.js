import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { props, checkAttr, STORE_NAME, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { MissingName, preventSaveOnMissingProps } from '../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';

export const CountryEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('country');

	const { additionalFieldClass, blockClientId } = attributes;

	const countryName = checkAttr('countryName', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('countryName', attributes, manifest), countryName);

	const country = (
		<>
			<div>{__('This data will be provided by an external source select in the sidebar!', 'eightshift-forms')}</div>

			<MissingName value={countryName} />

			{countryName && <ConditionalTagsEditor {...props('conditionalTags', attributes)} />}
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: country,
					fieldIsRequired: checkAttr('countryIsRequired', attributes, manifest),
				})}
				additionalFieldClass={additionalFieldClass}
			/>
		</>
	);
};
