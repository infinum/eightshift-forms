import React, { useMemo, useEffect } from 'react';
import classnames from 'classnames';
import {
	selector,
	checkAttr,
	props,
	getAttrKey,
	getUnique
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';
import manifest from '../manifest.json';

export const TextareaEditor = (attributes) => {
	const unique = useMemo(() => getUnique(), []);
	const {
		componentClass,
	} = manifest;

	const {
		setAttributes,

		selectorClass = componentClass,
		blockClass,
		additionalClass,
	} = attributes;

	const textareaValue = checkAttr('textareaValue', attributes, manifest);
	const textareaPlaceholder = checkAttr('textareaPlaceholder', attributes, manifest);

	const textareaClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	]);

	// Populate ID manually and make it generic.
	useEffect(() => {
		setAttributes({ [getAttrKey('textareaId', attributes, manifest)]: unique });
	}, []); // eslint-disable-line

	const textarea = (
		<textarea
			className={textareaClass}
			placeholder={textareaPlaceholder}
			readOnly
		>
			{textareaValue}
		</textarea>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: textarea,
				})}
			/>
		</>
	);
};
