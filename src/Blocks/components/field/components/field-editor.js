import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { checkAttr, STORE_NAME, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { MissingName, VisibilityHidden } from './../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';

export const FieldEditorExternalBlocks = ({ attributes, children, fieldName }) => {
	const manifest = select(STORE_NAME).getComponent('field');

	return (
		<div>
			<div>
				<div>
					<div>
						{children}

						<MissingName
							value={fieldName}
							isOptional
						/>

						{fieldName && (
							<ConditionalTagsEditor
								{...props('conditionalTags', attributes)}
								conditionalTagsUse={attributes?.conditionalTagsUse}
								useCustom
							/>
						)}
					</div>
				</div>
			</div>
		</div>
	);
};

export const FieldEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('field');

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
	const fieldType = checkAttr('fieldType', attributes, manifest);
	const fieldHelp = checkAttr('fieldHelp', attributes, manifest);
	const fieldStyle = checkAttr('fieldStyle', attributes, manifest);
	const fieldHidden = checkAttr('fieldHidden', attributes, manifest);

	const DivContent = () => {
		return (
			<div className={'es:mb-5'}>
				<div>
					{fieldLabel && !fieldHideLabel && <div className={'es:mb-1 es:text-base'}>{fieldLabel}</div>}
					{fieldBeforeContent && <div className={'es:text-sm es:mb-1 es:text-secondary-400'}>{fieldBeforeContent}</div>}
					<div>
						{fieldContent}

						{fieldSuffixContent && <div className={'es:text-sm es:mt-1 es:text-secondary-400'}>{fieldSuffixContent}</div>}
					</div>
					{fieldAfterContent && <div className={'es:text-sm es:mt-1 es:text-secondary-400'}>{fieldAfterContent}</div>}
					{fieldHelp && <div className={'es:text-sm es:mt-1 es:text-secondary-400'}>{fieldHelp}</div>}
				</div>

				<VisibilityHidden
					value={fieldHidden}
					label={__('Field', 'eightshift-forms')}
				/>
			</div>
		);
	};

	const FieldsetContent = () => {
		return (
			<fieldset className={'es:mb-5'}>
				{fieldLabel && !fieldHideLabel && <div className={'es:mb-1 es:text-base'}>{fieldLabel}</div>}
				{fieldBeforeContent && <div className={'es:text-sm es:mb-1 es:text-secondary-400'}>{fieldBeforeContent}</div>}
				<div className={'es:border es:border-secondary-300 es:bg-white'}>{fieldContent}</div>
				{fieldSuffixContent && <div className={'es:text-sm es:mt-1 es:text-secondary-400'}>{fieldSuffixContent}</div>}
				{fieldAfterContent && <div className={'es:text-sm es:mt-1 es:text-secondary-400'}>{fieldAfterContent}</div>}
				<div className={'es:text-sm es:mt-1 es:text-secondary-400'}>{fieldHelp}</div>

				<VisibilityHidden
					value={fieldHidden}
					label={__('Field', 'eightshift-forms')}
				/>
			</fieldset>
		);
	};

	return fieldType === 'div' ? <DivContent /> : <FieldsetContent />;
};
