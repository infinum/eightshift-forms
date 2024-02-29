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
import { getUtilsIcons } from '../../form/assets/state-init';

export const SliderEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('slider');

	const {
		componentName,
		componentClass
	} = manifest;

	const {
		additionalFieldClass,
		blockClientId,
		additionalClass,
	} = attributes;

	const sliderName = checkAttr('sliderName', attributes, manifest);
	// const sliderAmount = checkAttr('sliderAmount', attributes, manifest);
	const sliderValue = checkAttr('sliderValue', attributes, manifest);

	const sliderClass = classnames([
		selector(componentClass, componentClass),
		selector(additionalClass, additionalClass),
	]);

	preventSaveOnMissingProps(blockClientId, getAttrKey('sliderName', attributes, manifest), sliderName);

	const slider = (
		<div className={sliderClass} data-slider={sliderValue}>
			{/* {sliderAmount && 
				<>
					{[...Array(parseInt(sliderAmount, 10))].map((x, i) => {
						return <div className={`${componentClass}__star`} key={i} dangerouslySetInnerHTML={{ __html: getUtilsIcons('slider')}} data-slider={i + 1} />;
					})}
				</>
			} */}

			<MissingName value={sliderName} />

			{sliderName &&
				<ConditionalTagsEditor
					{...props('conditionalTags', attributes)}
				/>
			}
		</div>
	);

	return (
		<FieldEditor
			{...props('field', attributes, {
				fieldContent: slider,
				fieldIsRequired: checkAttr('sliderIsRequired', attributes, manifest),
			})}
			additionalFieldClass={additionalFieldClass}
			selectorClass={componentName}
		/>
	);
};
