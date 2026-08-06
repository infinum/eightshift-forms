import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { StatusFieldOutput, usePreventSaveOnMissingProps } from './../../utils';
import { clsx } from '@eightshift/ui-components/utilities';
import { useBlockProps } from '@wordpress/block-editor';
import manifest from '../manifest.json';

export const RadioEditor = (attributes) => {
	const { blockClientId, prefix } = attributes;

	const radioLabel = checkAttr('radioLabel', attributes, manifest);
	const radioValue = checkAttr('radioValue', attributes, manifest);
	const radioIsHidden = checkAttr('radioIsHidden', attributes, manifest);
	const radioIsChecked = checkAttr('radioIsChecked', attributes, manifest);
	const radioIsDisabled = checkAttr('radioIsDisabled', attributes, manifest);

	usePreventSaveOnMissingProps(blockClientId, getAttrKey('radioValue', attributes, manifest), radioValue);

	const blockProps = useBlockProps({
		className: 'esf:flex esf:items-center esf:gap-12',
	});

	return (
		<div {...blockProps}>
			<div className={clsx('esf-fieldset-radio', 'esf-fieldset-checkbox', 'esf-fieldset-item', 'esf:relative!', radioIsHidden && 'esf-field-hidden', radioIsChecked && 'esf-fieldset-checked')}>
				<span
					dangerouslySetInnerHTML={{
						__html: radioLabel ? radioLabel : __('Please enter radio label in sidebar or this radio will not show on the frontend.', 'eightshift-forms'),
					}}
				/>
			</div>

			<StatusFieldOutput components={[radioIsHidden && 'hidden', !radioValue && 'missingName', radioIsDisabled && 'disabled', attributes?.[`${prefix}ConditionalTagsUse`] && 'conditionals'].filter(Boolean)} />
		</div>
	);
};
