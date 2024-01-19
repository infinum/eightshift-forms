import React from 'react';
import { select } from '@wordpress/data';
import classnames from 'classnames';
import {
	selector,
	checkAttr,
	props,
	STORE_NAME,
	getAttrKey,
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { MissingName, preventSaveOnMissingProps } from '../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';

export const RatingEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('rating');
	const utilsIcons = select(STORE_NAME).getComponent('utils').icons;

	const {
		componentName,
		componentClass
	} = manifest;

	const {
		additionalFieldClass,
		blockClientId,
		additionalClass,
	} = attributes;

	const ratingName = checkAttr('ratingName', attributes, manifest);
	const ratingAmount = checkAttr('ratingAmount', attributes, manifest);
	const ratingValue = checkAttr('ratingValue', attributes, manifest);

	const ratingClass = classnames([
		selector(componentClass, componentClass),
		selector(additionalClass, additionalClass),
	]);

	preventSaveOnMissingProps(blockClientId, getAttrKey('ratingName', attributes, manifest), ratingName);

	const rating = (
		<div className={ratingClass} data-rating={ratingValue}>
			{ratingAmount && 
				<>
					{[...Array(parseInt(ratingAmount, 10))].map((x, i) => {
						return <div className={`${componentClass}__star`} key={i} dangerouslySetInnerHTML={{ __html: utilsIcons.rating}} data-rating={i + 1} />;
					})}
				</>
			}

			<MissingName value={ratingName} />

			{ratingName &&
				<ConditionalTagsEditor
					{...props('conditionalTags', attributes)}
				/>
			}
		</div>
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
