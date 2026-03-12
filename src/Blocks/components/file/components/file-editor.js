import React from 'react';
import { __ } from '@wordpress/i18n';
import { props, checkAttr, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { preventSaveOnMissingProps, StatusIconConditionals, StatusIconMissingName } from './../../utils';
import manifest from '../manifest.json';

export const FileEditor = (attributes) => {
	const { blockClientId, prefix } = attributes;

	const fileName = checkAttr('fileName', attributes, manifest);
	const fileCustomInfoText = checkAttr('fileCustomInfoText', attributes, manifest);
	const fileCustomInfoTextUse = checkAttr('fileCustomInfoTextUse', attributes, manifest);
	const fileCustomInfoButtonText = checkAttr('fileCustomInfoButtonText', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('fileName', attributes, manifest), fileName);

	const file = (
		<div className='esf:flex! esf:flex-col! esf:gap-10! esf:items-center! esf:text-center! esf:border! esf:border-dashed! esf:border-secondary-200! esf:bg-secondary-100! esf:p-20! esf:rounded-md!'>
			<div>
				<div className='esf:text-base! esf:text-secondary-600!'>
					{fileCustomInfoTextUse && fileCustomInfoText}
					{!fileCustomInfoTextUse && __('Drag and drop files here', 'eightshift-forms')}
				</div>

				<div className='esf:text-xs! esf:text-secondary-400!'>
					{fileCustomInfoButtonText?.length > 0 ? fileCustomInfoButtonText : __('Add files', 'eightshift-forms')}
				</div>
			</div>
		</div>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: file,
					fieldIsRequired: checkAttr('fileIsRequired', attributes, manifest),
				})}
				statusSlog={[
					!fileName && <StatusIconMissingName />,
					attributes?.[`${prefix}ConditionalTagsUse`] && <StatusIconConditionals />,
				]}
			/>
		</>
	);
};
