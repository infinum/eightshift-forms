/* global esFormsLocalization */

import React, { useMemo } from 'react';
import classnames from 'classnames';
import { __ } from '@wordpress/i18n';
import {
	selector,
	checkAttr,
	props,
	getUnique
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';
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
	const selectUseDynamic = checkAttr('selectUseDynamic', attributes, manifest);

	const selectClass = classnames([
		selector(componentClass, componentClass),
		selector(additionalClass, additionalClass),
	]);

	// Additional content filter.
	let additionalContent = '';

	if (
		typeof esFormsLocalization !== 'undefined' &&
		(esFormsLocalization?.selectBlockAdditionalContent) !== ''
	) {
		additionalContent = esFormsLocalization.selectBlockAdditionalContent;
	}

	const select = (
		<>
			{selectUseDynamic ? 
				__('This data will be provided by an external source selected in the sidebar!', 'eightshift-forms') :
				<div className={selectClass}>
					{selectContent}
				</div>
			}

			<div dangerouslySetInnerHTML={{__html: additionalContent}} />
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
