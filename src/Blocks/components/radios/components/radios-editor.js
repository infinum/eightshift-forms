import React, { useMemo, useEffect } from 'react';
import {
	checkAttr,
	props,
	getUnique,
	getAttrKey
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import manifest from '../manifest.json';

export const RadiosEditor = (attributes) => {
	const unique = useMemo(() => getUnique(), []);

	const radiosContent = checkAttr('radiosContent', attributes, manifest);

	const {
		setAttributes,
	} = attributes;

	// Populate ID manually and make it generic.
	useEffect(() => {
		setAttributes({ [getAttrKey('radiosId', attributes, manifest)]: unique });
	}, []); // eslint-disable-line

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: radiosContent
				})}
			/>
		</>
	);
}
