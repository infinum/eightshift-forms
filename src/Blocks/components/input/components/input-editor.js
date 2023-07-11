import React from 'react';
import classnames from 'classnames';
import { select } from '@wordpress/data';
import { selector, checkAttr, props, STORE_NAME, getAttrKey } from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';
import { getAdditionalContentFilterContent, MissingName, preventSaveOnMissingProps } from './../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';

export const InputEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('input');

	const {
		componentClass,
		componentName
	} = manifest;

	const {
		additionalFieldClass,
		additionalClass,
		blockClientId,
	} = attributes;

	const inputName = checkAttr('inputName', attributes, manifest);
	const inputValue = checkAttr('inputValue', attributes, manifest);
	const inputPlaceholder = checkAttr('inputPlaceholder', attributes, manifest);
	let inputType = checkAttr('inputType', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('inputName', attributes, manifest), inputName);

	// For some reason React won't allow input type email.
	if (inputType === 'email' || inputType === 'url') {
		inputType = 'text';
	}

	const inputClass = classnames([
		selector(componentClass, componentClass),
		selector(additionalClass, additionalClass),
	]);

	const input = (
		<>
			<input
				className={inputClass}
				value={inputValue}
				placeholder={inputPlaceholder}
				type={inputType}
				readOnly
			/>

			<MissingName value={inputName} />

			{inputName &&
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
					fieldContent: input,
					fieldIsRequired: checkAttr('inputIsRequired', attributes, manifest),
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
