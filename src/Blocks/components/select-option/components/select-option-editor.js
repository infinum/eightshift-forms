import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { StatusFieldOutput, usePreventSaveOnMissingProps } from './../../utils';
import { clsx } from '@eightshift/ui-components/utilities';
import { useBlockProps } from '@wordpress/block-editor';
import manifest from '../manifest.json';

export const SelectOptionEditor = (attributes) => {
	const { blockClientId, prefix } = attributes;

	const selectOptionLabel = checkAttr('selectOptionLabel', attributes, manifest);
	const selectOptionValue = checkAttr('selectOptionValue', attributes, manifest);
	const selectOptionIsHidden = checkAttr('selectOptionIsHidden', attributes, manifest);
	const selectOptionIsSelected = checkAttr('selectOptionIsSelected', attributes, manifest);
	const selectOptionIsDisabled = checkAttr('selectOptionIsDisabled', attributes, manifest);

	usePreventSaveOnMissingProps(blockClientId, getAttrKey('selectOptionValue', attributes, manifest), selectOptionValue);

	const blockProps = useBlockProps({
		className: 'esf:flex esf:items-center esf:gap-12',
	});

	return (
		<div {...blockProps}>
			<div className={clsx('esf-fieldset-item', selectOptionIsHidden && 'esf-field-hidden', selectOptionIsSelected && 'esf:text-mist-600!')}>{selectOptionLabel ? selectOptionLabel : __('Enter option label in sidebar.', 'eightshift-forms')}</div>

			<StatusFieldOutput components={[selectOptionIsHidden && 'hidden', !selectOptionValue && 'missingName', selectOptionIsDisabled && 'disabled', attributes?.[`${prefix}ConditionalTagsUse`] && 'conditionals'].filter(Boolean)} />
		</div>
	);
};
