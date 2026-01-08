import React from 'react';
import { checkAttr, props, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { preventSaveOnMissingProps, StatusIconMissingName, StatusIconConditionals } from '../../utils';
import { getUtilsIcons } from '../../form/assets/state-init';
import manifest from '../manifest.json';

export const RatingEditor = (attributes) => {
	const { blockClientId, prefix } = attributes;

	const ratingName = checkAttr('ratingName', attributes, manifest);
	const ratingAmount = checkAttr('ratingAmount', attributes, manifest);
	const ratingValue = checkAttr('ratingValue', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('ratingName', attributes, manifest), ratingName);

	const rating = (
		<div
			data-rating={ratingValue}
			className='esf:flex! esf:flex-row! esf:gap-10!'
		>
			{ratingAmount && (
				<>
					{[...Array(parseInt(ratingAmount, 10))].map((x, i) => {
						return (
							<div
								key={i}
								dangerouslySetInnerHTML={{ __html: getUtilsIcons('rating') }}
								data-rating={i + 1}
							/>
						);
					})}
				</>
			)}
		</div>
	);

	return (
		<FieldEditor
			{...props('field', attributes, {
				fieldContent: rating,
				fieldIsRequired: checkAttr('ratingIsRequired', attributes, manifest),
			})}
			statusSlog={[
				!ratingName && <StatusIconMissingName />,
				attributes?.[`${prefix}ConditionalTagsUse`] && <StatusIconConditionals />,
			]}
		/>
	);
};
