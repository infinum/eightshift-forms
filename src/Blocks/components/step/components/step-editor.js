import { checkAttr, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { MissingName, usePreventSaveOnMissingProps } from './../../utils';
import { useBlockProps } from '@wordpress/block-editor';
import manifest from '../manifest.json';

export const StepEditor = (attributes) => {
	const { blockClientId } = attributes;

	const stepName = checkAttr('stepName', attributes, manifest);
	const stepLabel = checkAttr('stepLabel', attributes, manifest);

	usePreventSaveOnMissingProps(blockClientId, getAttrKey('stepName', attributes, manifest), stepName);

	const blockProps = useBlockProps({
		className: 'esf:flex esf:items-center es:font-sans esf:py-12',
	});

	return (
		<div {...blockProps}>
			<div className='esf:grow esf:h-px esf:bg-current esf:mask-l-from-75%' />
			<div className='esf:border esf:border-current esf:rounded-xl esf:py-4 esf:px-10 esf:text-base esf:flex esf:items-center esf:gap-6'>
				{stepLabel ? stepLabel : stepName}

				<MissingName value={stepName} />
			</div>
			<div className='esf:grow esf:h-px esf:bg-current esf:mask-r-from-75%' />
		</div>
	);
};
