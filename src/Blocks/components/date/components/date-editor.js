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
import { FieldEditor } from '../../field/components/field-editor';
import { MissingName, preventSaveOnMissingProps } from './../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';

export const DateEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('date');

	const {
		additionalFieldClass,
		additionalClass,
		blockClientId,
	} = attributes;

	const dateValue = checkAttr('dateValue', attributes, manifest);
	const datePlaceholder = checkAttr('datePlaceholder', attributes, manifest);
	const dateType = checkAttr('dateType', attributes, manifest);
	const dateName = checkAttr('dateName', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('dateName', attributes, manifest), dateName);

	const dateClass = classnames([
		selector(manifest.componentClass, manifest.componentClass),
		selector(additionalClass, additionalClass),
	]);

	const date = (
		<>
			<input
				className={dateClass}
				value={dateValue}
				placeholder={datePlaceholder}
				type={dateType}
				readOnly
			/>

			<MissingName value={dateName} />

			{dateName &&
				<ConditionalTagsEditor
					{...props('conditionalTags', attributes)}
				/>
			}
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: date,
					fieldIsRequired: checkAttr('dateIsRequired', attributes, manifest),
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={manifest.componentName}
			/>
		</>
	);
};
