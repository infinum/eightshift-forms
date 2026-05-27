import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { StatusFieldOutput, usePreventSaveOnMissingProps } from './../../utils';
import { clsx } from '@eightshift/ui-components/utilities';
import { useBlockProps } from '@wordpress/block-editor';
import manifest from '../manifest.json';

export const CheckboxEditor = (attributes) => {
	const { blockClientId, prefix } = attributes;

	const checkboxLabel = checkAttr('checkboxLabel', attributes, manifest);
	const checkboxValue = checkAttr('checkboxValue', attributes, manifest);
	const checkboxIsHidden = checkAttr('checkboxIsHidden', attributes, manifest);
	const checkboxIsChecked = checkAttr('checkboxIsChecked', attributes, manifest);

	usePreventSaveOnMissingProps(blockClientId, getAttrKey('checkboxValue', attributes, manifest), checkboxValue);
	const blockProps = useBlockProps({
		className: clsx('esf-fieldset-checkbox', 'esf-fieldset-item', 'esf:relative!', checkboxIsHidden && 'esf-field-hidden', checkboxIsChecked && 'esf-fieldset-checked'),
	});

	return (
		<div {...blockProps}>
			<span
				dangerouslySetInnerHTML={{
					__html: checkboxLabel ? checkboxLabel : __('Please enter checkbox label in sidebar or this checkbox will not show on the frontend.', 'eightshift-forms'),
				}}
			/>
			<StatusFieldOutput components={[checkboxIsHidden && 'hidden', !checkboxValue && 'missingName', attributes?.[`${prefix}ConditionalTagsUse`] && 'conditionals'].filter(Boolean)} />
		</div>
	);
};
