/* global esFormsBlocksLocalization */

import React, { useMemo, useEffect } from 'react';
import classnames from 'classnames';
import {
	selector,
	props,
	getAttrKey,
	getUnique
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import manifest from '../manifest.json';

export const FileEditor = (attributes) => {
	const unique = useMemo(() => getUnique(), []);
	const {
		componentClass,
		componentName
	} = manifest;

	const {
		setAttributes,

		additionalFieldClass,
		additionalClass,
	} = attributes;

	const fileClass = classnames([
		selector(componentClass, componentClass),
		selector(additionalClass, additionalClass),
	]);

	// Populate ID manually and make it generic.
	useEffect(() => {
		setAttributes({ [getAttrKey('fileId', attributes, manifest)]: unique });
	}, []); // eslint-disable-line

		// Additional content filter.
		let additionalContent = '';

		if (
			typeof esFormsBlocksLocalization !== 'undefined' &&
			(esFormsBlocksLocalization?.fileBlockAdditionalContent) !== ''
		) {
			additionalContent = esFormsBlocksLocalization.fileBlockAdditionalContent;
		}

	const file = (
		<>
			<input
				className={fileClass}
				type={'file'}
				readOnly
				disabled
			/>

			<div dangerouslySetInnerHTML={{__html: additionalContent}} />
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: file,
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
