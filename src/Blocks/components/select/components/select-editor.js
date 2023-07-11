import React from 'react';
import classnames from 'classnames';
import { select } from '@wordpress/data';
import {
	selector,
	checkAttr,
	props,
	STORE_NAME,
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';
import { getAdditionalContentFilterContent, MissingName } from './../../utils';
import { SelectOptionEditor } from './../../select-option/components/select-option-editor';
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

	const selectComponent = (
		<>
			<div className={selectClass}>

				{selectPlaceholder &&
					<div className={`${componentClass}__placeholder`}>
						<SelectOptionEditor
							selectOptionLabel={selectPlaceholder}
							selectOptionAsPlaceholder={true}
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
					fieldContent: selectComponent,
					fieldIsRequired: checkAttr('selectIsRequired', attributes, manifest),
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
