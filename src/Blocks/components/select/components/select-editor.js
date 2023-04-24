import React from 'react';
import classnames from 'classnames';
import {
	selector,
	checkAttr,
	props,
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';
import { getAdditionalContentFilterContent, MissingName } from './../../utils';
import { SelectOptionEditor } from './../../select-option/components/select-option-editor';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';
import manifest from '../manifest.json';

export const SelectEditor = (attributes) => {
	const {
		componentClass,
		componentName
	} = manifest;

	const {
		additionalFieldClass,
		additionalClass,
	} = attributes;

	const selectContent = checkAttr('selectContent', attributes, manifest);
	const selectName = checkAttr('selectName', attributes, manifest);
	const selectPlaceholder = checkAttr('selectPlaceholder', attributes, manifest);

	const selectIsDisabled = checkAttr('selectIsDisabled', attributes, manifest);

	const selectClass = classnames([
		selector(componentClass, componentClass),
		selector(selectIsDisabled, componentClass, '', 'disabled'),
		selector(additionalClass, additionalClass),
	]);

	const select = (
		<>
			<div className={selectClass}>

				{selectPlaceholder &&
					<div className={`${componentClass}__placeholder`}>
						<SelectOptionEditor
							selectOptionLabel={
								selectPlaceholder
							}
						/>
					</div>
				}

				{selectContent}

				<MissingName value={selectName} />

				{selectName &&
					<ConditionalTagsEditor
						{...props('conditionalTags', attributes)}
					/>
				}
			</div>

			<div dangerouslySetInnerHTML={{ __html: getAdditionalContentFilterContent(componentName) }} />
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: select
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
