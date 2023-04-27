import React from 'react';
import { __ } from '@wordpress/i18n';
import {
	props,
	checkAttr,
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { getAdditionalContentFilterContent, MissingName } from '../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';
import manifest from '../manifest.json';

export const CountryEditor = (attributes) => {
	const {
		componentClass,
		componentName
	} = manifest;

	const {
		additionalFieldClass,
	} = attributes;

	const countryName = checkAttr('countryName', attributes, manifest);

	const country = (
		<>
			<div className={`${componentClass}__info-text`}>
				{__('This data will be provided by an external source select in the sidebar!', 'eightshift-forms')}
			</div>

			<MissingName value={countryName} />

			{countryName &&
				<ConditionalTagsEditor
					{...props('conditionalTags', attributes)}
				/>
			}

			<div dangerouslySetInnerHTML={{ __html: getAdditionalContentFilterContent(componentName) }} />
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: country
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
