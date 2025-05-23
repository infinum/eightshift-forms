import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { checkAttr, STORE_NAME, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { MissingName, VisibilityHidden } from './../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';

export const FieldEditorExternalBlocks = ({ attributes, children, clientId, fieldName }) => {
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

	const { clientId } = attributes;

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

	const LabelDefault = () => (
		<>
			{!fieldHideLabel && (
				<div>
					<span dangerouslySetInnerHTML={{ __html: fieldLabel }} />
				</div>
			)}
		</>
	);

	const LegendDefault = () => (
		<>
			{!fieldHideLabel && (
				<div>
					<span dangerouslySetInnerHTML={{ __html: fieldLabel }} />
				</div>
			)}
		</>
	);

	const Content = () => (
		<div>
			{fieldBeforeContent && <div>{fieldBeforeContent}</div>}
			<div>
				{fieldContent}

				{fieldSuffixContent && <div>{fieldSuffixContent}</div>}
			</div>
			{fieldAfterContent && <div>{fieldAfterContent}</div>}
		</div>
	);

	const Help = () => <div>{fieldHelp}</div>;

	const DivContent = () => {
		return (
			<div>
				<div>
					{fieldLabel && <LabelDefault />}
					<Content />
					<Help />
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
			<fieldset>
				<div>
					{fieldLabel && <LegendDefault />}
					<Content />
					<Help />
				</div>

				<VisibilityHidden
					value={fieldHidden}
					label={__('Field', 'eightshift-forms')}
				/>
			</fieldset>
		);
	};

	return fieldType === 'div' ? <DivContent /> : <FieldsetContent />;
};
