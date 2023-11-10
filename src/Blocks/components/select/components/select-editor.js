import React from 'react';
import classnames from 'classnames';
import { select } from '@wordpress/data';
import {
	selector,
	checkAttr,
	props,
	STORE_NAME,
	getAttrKey,
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';
import { MissingName, preventSaveOnMissingProps } from './../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';

export const SelectEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('select');

	const {
		componentClass,
		componentName
	} = manifest;

	const {
		additionalFieldClass,
		additionalClass,
		blockClientId,
	} = attributes;

	const selectContent = checkAttr('selectContent', attributes, manifest);
	const selectName = checkAttr('selectName', attributes, manifest);
	const selectIsDisabled = checkAttr('selectIsDisabled', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('selectName', attributes, manifest), selectName);

	const selectClass = classnames([
		selector(componentClass, componentClass),
		selector(selectIsDisabled, componentClass, '', 'disabled'),
		selector(additionalClass, additionalClass),
	]);

	const selectComponent = (
		<>
			<div className={selectClass}>

				{selectContent}

				<MissingName value={selectName} />

				{selectName &&
					<ConditionalTagsEditor
						{...props('conditionalTags', attributes)}
					/>
				}
			</div>
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: selectComponent,
					fieldIsRequired: checkAttr('selectIsRequired', attributes, manifest),
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
