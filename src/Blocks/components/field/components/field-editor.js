import { checkAttr } from '@eightshift/frontend-libs-tailwind/scripts';
import { StatusFieldOutput } from './../../utils';
import { useBlockProps } from '@wordpress/block-editor';
import { HStack } from '@eightshift/ui-components';
import manifest from '../manifest.json';

export const FieldEditorExternalBlocks = ({ attributes, children, fieldName }) => {
	return (
		<div>
			<div>
				<div>
					<div>
						{children}

						{fieldName && (
							<ConditionalTagsEditor
								{...props('conditionalTags', attributes)}
								conditionalTagsUse={attributes?.conditionalTagsUse}
							/>
						)}
					</div>
				</div>
			</div>
		</div>
	);
};
export const FieldEditor = (attributes) => {
	const { statusSlot = [] } = attributes;

	const blockProps = useBlockProps({
		className: 'esf:w-full esf:flex! esf:flex-col! esf:gap-4 esf:mb-20!',
	});

	const fieldContent = checkAttr('fieldContent', attributes, manifest);
	const fieldSkip = checkAttr('fieldSkip', attributes, manifest);

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

	const statusFieldData = [fieldHidden && 'hidden', ...statusSlot].filter(Boolean);

	return (
		<div {...blockProps}>
			{fieldLabel && !fieldHideLabel && (
				<HStack noWrap>
					<div
						className='esf:text-base! esf:text-gray-900!'
						dangerouslySetInnerHTML={{ __html: fieldLabel }}
					/>

					<StatusFieldOutput components={statusFieldData} />
				</HStack>
			)}

			{fieldBeforeContent && <div className='esf:text-xs! esf:text-gray-500!'>{fieldBeforeContent}</div>}

			<div className={fieldHidden && 'esf-field-hidden'}>{fieldContent}</div>

			{fieldSuffixContent && <div className='esf:text-xs! esf:text-gray-500!'>{fieldSuffixContent}</div>}

			{fieldAfterContent && <div className='esf:text-xs! esf:text-gray-500!'>{fieldAfterContent}</div>}

			{fieldHelp && <div className='esf:text-xs! esf:text-gray-500!'>{fieldHelp}</div>}
		</div>
	);
};
