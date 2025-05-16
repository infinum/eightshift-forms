import React from 'react';
import { select } from '@wordpress/data';
import { selector, checkAttr, props, STORE_NAME, getAttrKey } from '@eightshift/frontend-libs/scripts';
import { clsx } from '@eightshift/ui-components/utilities';
import { FieldEditor } from '../../field/components/field-editor';
import { MissingName, preventSaveOnMissingProps } from './../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';

export const PhoneEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('phone');

	const { componentClass, componentName } = manifest;

	const { additionalFieldClass, additionalClass, blockClientId } = attributes;

	const manifestSelect = select(STORE_NAME).getComponent('select');

	const phoneValue = checkAttr('phoneValue', attributes, manifest);
	const phonePlaceholder = checkAttr('phonePlaceholder', attributes, manifest);
	const phoneName = checkAttr('phoneName', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('phoneName', attributes, manifest), phoneName);

	const phoneClass = clsx(selector(componentClass, componentClass), selector(additionalClass, additionalClass));

	const selectClass = clsx(selector(manifestSelect.componentClass, manifestSelect.componentClass), selector(additionalClass, additionalClass));

	const phone = (
		<>
			<select className={selectClass} />
			<input
				className={phoneClass}
				value={phoneValue}
				placeholder={phonePlaceholder}
				type={'tel'}
				readOnly
			/>

			<MissingName
				value={phoneName}
				isEditor={true}
			/>

			<ConditionalTagsEditor {...props('conditionalTags', attributes)} />
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: phone,
					fieldIsRequired: checkAttr('phoneIsRequired', attributes, manifest),
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
