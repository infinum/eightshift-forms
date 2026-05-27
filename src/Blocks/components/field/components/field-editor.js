import { checkAttr } from '@eightshift/frontend-libs-tailwind/scripts';
import { StatusFieldOutput } from './../../utils';
import { useBlockProps } from '@wordpress/block-editor';
import { HStack } from '@eightshift/ui-components';
import manifest from '../manifest.json';

export const FieldEditorExternalBlocks = ({ attributes, children, fieldName }) => {
	const blockProps = useBlockProps();

	return (
		<div {...blockProps}>
			<HStack>
				<div>{children}</div>

				{fieldName && <StatusFieldOutput components={attributes?.conditionalTagsUse ? ['conditionals'] : []} />}
			</HStack>
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
						className='esf:text-sm'
						dangerouslySetInnerHTML={{ __html: fieldLabel }}
					/>

					<StatusFieldOutput components={statusFieldData} />
				</HStack>
			)}

			{!(fieldLabel && !fieldHideLabel) && <StatusFieldOutput components={statusFieldData} />}

			{fieldBeforeContent && <div className='esf:text-xs esf:text-current/80'>{fieldBeforeContent}</div>}

			<div className={fieldHidden ? 'esf-field-hidden' : ''}>{fieldContent}</div>

			{fieldSuffixContent && <div className='esf:text-xs esf:text-current/80'>{fieldSuffixContent}</div>}

			{fieldAfterContent && <div className='esf:text-xs esf:text-current/80 esf:mt-8'>{fieldAfterContent}</div>}

			{fieldHelp && <div className='esf:text-xs esf:font-medium esf:text-current/80'>{fieldHelp}</div>}
		</div>
	);
};
