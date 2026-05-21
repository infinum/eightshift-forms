import { checkAttr, props, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';
import { usePreventSaveOnMissingProps } from './../../utils';
import manifest from '../manifest.json';

export const TextareaEditor = (attributes) => {
	const { blockClientId, prefix } = attributes;

	const textareaValue = checkAttr('textareaValue', attributes, manifest);
	const textareaPlaceholder = checkAttr('textareaPlaceholder', attributes, manifest);
	const textareaName = checkAttr('textareaName', attributes, manifest);

	usePreventSaveOnMissingProps(blockClientId, getAttrKey('textareaName', attributes, manifest), textareaName);

	const textarea = (
		<textarea
			className='esf-input esf:h-80'
			placeholder={textareaPlaceholder}
			disabled
		>
			{textareaValue}
		</textarea>
	);

	return (
		<FieldEditor
			{...props('field', attributes, {
				fieldContent: textarea,
				fieldIsRequired: checkAttr('textareaIsRequired', attributes, manifest),
			})}
			statusSlot={[
				!textareaName && 'missingName',
				attributes?.[`${prefix}ConditionalTagsUse`] && 'conditionals',
			].filter(Boolean)}
		/>
	);
};
