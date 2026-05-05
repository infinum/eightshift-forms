import { checkAttr, props, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { preventSaveOnMissingProps } from './../../utils';
import manifest from '../manifest.json';

export const RadiosEditor = (attributes) => {
	const { blockClientId, prefix } = attributes;

	const radiosContent = checkAttr('radiosContent', attributes, manifest);
	const radiosName = checkAttr('radiosName', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('radiosName', attributes, manifest), radiosName);

	const radios = <div className='esf-fieldset'>{radiosContent}</div>;

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: radios,
					fieldIsRequired: checkAttr('radiosIsRequired', attributes, manifest),
				})}
				statusSlot={[
					!radiosName && 'missingName',
					attributes?.[`${prefix}ConditionalTagsUse`] && 'conditionals',
				].filter(Boolean)}
			/>
		</>
	);
};
