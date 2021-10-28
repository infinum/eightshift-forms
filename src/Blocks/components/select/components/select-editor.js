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

export const SelectEditor = (attributes) => {
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

	const selectOptions = checkAttr('selectOptions', attributes, manifest);

	const selectClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	]);

	// Populate ID manually and make it generic.
	useEffect(() => {
		setAttributes({ [getAttrKey('selectId', attributes, manifest)]: unique });
	}, []); // eslint-disable-line

	const select = (
		<div className={selectClass}>
			{selectOptions}
		</div>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: select
				})}
			/>
		</>
	);
};
