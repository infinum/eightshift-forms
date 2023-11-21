import React from 'react';
import { select } from '@wordpress/data';
import { checkAttr, props, STORE_NAME, getAttrKey } from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { MissingName, preventSaveOnMissingProps } from '../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';

export const RatingEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('rating');
	const utilsIcons = select(STORE_NAME).getComponent('utils').icons;

	const {
		componentName
	} = manifest;

	const {
		additionalFieldClass,
		blockClientId,
	} = attributes;

	const ratingName = checkAttr('ratingName', attributes, manifest);
	const ratingAmount = checkAttr('ratingAmount', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('ratingName', attributes, manifest), ratingName);

	const rating = (
		<>
			{ratingAmount && 
				<>
					{[...Array(parseInt(ratingAmount, 10))].map((x, i) => {
						return <span key={i} dangerouslySetInnerHTML={{ __html: utilsIcons.rating}} />;
					})}
				</>
			}

			<MissingName value={ratingName} />

			{ratingName &&
				<ConditionalTagsEditor
					{...props('conditionalTags', attributes)}
				/>
			}
		</>
	);

	return (
		<FieldEditor
			{...props('field', attributes, {
				fieldContent: rating,
				fieldIsRequired: checkAttr('ratingIsRequired', attributes, manifest),
			})}
			additionalFieldClass={additionalFieldClass}
			selectorClass={componentName}
		/>
	);
};
