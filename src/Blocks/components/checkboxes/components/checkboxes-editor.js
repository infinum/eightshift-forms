import React, { useMemo, useEffect } from 'react';
import {
	checkAttr,
	props,
	getUnique,
	getAttrKey
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import manifest from '../manifest.json';


export const CheckboxesEditor = (attributes) => {
	const unique = useMemo(() => getUnique(), []);
	const {
		componentName
	} = manifest;

	const {
		setAttributes,

		additionalFieldClass,
	} = attributes;

	const checkboxesContent = checkAttr('checkboxesContent', attributes, manifest);

	// Populate ID manually and make it generic.
	useEffect(() => {
		setAttributes({ [getAttrKey('checkboxesId', attributes, manifest)]: unique });
	}, []); // eslint-disable-line

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: checkboxesContent
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
