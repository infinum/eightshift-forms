/* global esFormsLocalization */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { checkAttr } from '@eightshift/frontend-libs-tailwind/scripts';
import { StatusIconHidden, StatusFieldOutput } from './../../utils';
import manifest from '../manifest.json';
import { clsx } from '@eightshift/ui-components/utilities';

export const FieldEditorExternalBlocks = ({ attributes, children, fieldName }) => {
	return (
		<div>
			<div>
				<div>
					<div>
						{children}

						{/* {fieldName && (
							<ConditionalTagsEditor
								{...props('conditionalTags', attributes)}
								conditionalTagsUse={attributes?.conditionalTagsUse}
							/>
						)} */}
					</div>
				</div>
			</div>
		</div>
	);
};
export const FieldEditor = (attributes) => {
	const { statusSlog = [] } = attributes;

	const fieldContent = checkAttr('fieldContent', attributes, manifest);
	const fieldSkip = checkAttr('fieldSkip', attributes, manifest);
	const fieldIsRequired = checkAttr('fieldIsRequired', attributes, manifest);

	// Enable option to skip field and just render content.
	if (fieldSkip) {
		return fieldContent;
	}

	const fieldLabel = checkAttr('fieldLabel', attributes, manifest);
	const fieldHideLabel = checkAttr('fieldHideLabel', attributes, manifest);
	const fieldBeforeContent = checkAttr('fieldBeforeContent', attributes, manifest);
	const fieldAfterContent = checkAttr('fieldAfterContent', attributes, manifest);
	const fieldSuffixContent = checkAttr('fieldSuffixContent', attributes, manifest);
	const fieldHelp = checkAttr('fieldHelp', attributes, manifest);
	const fieldHidden = checkAttr('fieldHidden', attributes, manifest);

	return (
		<div className={clsx('esf:w-full esf:flex! esf:flex-col! esf:gap-4!', fieldHidden && 'esf-field-hidden')}>
			{fieldLabel && !fieldHideLabel && (
				<div
					className='esf:text-base! esf:text-secondary-900!'
					dangerouslySetInnerHTML={{ __html: fieldLabel }}
				/>
			)}
			{fieldBeforeContent && <div className='esf:text-xs! esf:text-secondary-500!'>{fieldBeforeContent}</div>}

			<div className='esf:relative!'>
				{fieldContent} <StatusFieldOutput components={[fieldHidden && <StatusIconHidden />, ...statusSlog]} />
			</div>

			{fieldSuffixContent && <div className='esf:text-xs! esf:text-secondary-500!'>{fieldSuffixContent}</div>}
			{fieldAfterContent && <div className='esf:text-xs! esf:text-secondary-500!'>{fieldAfterContent}</div>}
			{fieldHelp && <div className='esf:text-xs! esf:text-secondary-500!'>{fieldHelp}</div>}
		</div>
	);
};
