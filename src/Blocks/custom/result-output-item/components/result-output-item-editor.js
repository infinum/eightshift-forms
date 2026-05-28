import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { useInnerBlocksProps } from '@wordpress/block-editor';
import { checkAttr } from '@eightshift/frontend-libs-tailwind/scripts';
import manifest from '../manifest.json';
import { CONDITIONAL_TAGS_OPERATORS_EXTENDED_LABELS, CONDITIONAL_TAGS_OPERATORS_LABELS } from '../../../components/conditional-tags/components/conditional-tags-labels';
import globalManifest from '../../../manifest.json';
import { Container, ContainerGroup, RichLabel } from '@eightshift/ui-components';
import { visible, warningFill } from '@eightshift/ui-components/icons';
import { clsx } from '@eightshift/ui-components/utilities';

export const ResultOutputItemEditor = ({ attributes }) => {
	const { blockClass } = attributes;

	const innerBlocksProps = useInnerBlocksProps({ className: 'esf:mt-16' });

	const resultOutputItemName = checkAttr('resultOutputItemName', attributes, manifest);
	const resultOutputItemValueStart = checkAttr('resultOutputItemValue', attributes, manifest);
	const resultOutputItemValueEnd = checkAttr('resultOutputItemValueEnd', attributes, manifest);
	const resultOutputItemOperator = checkAttr('resultOutputItemOperator', attributes, manifest);

	const [isValidConfiguration, setIsValidConfiguration] = useState(resultOutputItemName && resultOutputItemValueStart);

	useEffect(() => {
		setIsValidConfiguration(resultOutputItemName && resultOutputItemValueStart);
	}, [resultOutputItemName, resultOutputItemValueStart]);

	const operatorLabel =
		{
			...CONDITIONAL_TAGS_OPERATORS_LABELS,
			...CONDITIONAL_TAGS_OPERATORS_EXTENDED_LABELS,
		}?.[resultOutputItemOperator] ?? CONDITIONAL_TAGS_OPERATORS_LABELS[globalManifest.comparator.IS];

	let outputName = '';

	if (resultOutputItemOperator === globalManifest.comparator.GT || resultOutputItemOperator === globalManifest.comparator.LT) {
		outputName = `is ${operatorLabel} ${resultOutputItemValueStart}`;
	} else if (resultOutputItemOperator === globalManifest.comparator.GTE || resultOutputItemOperator === globalManifest.comparator.LTE) {
		outputName = `is ${operatorLabel} to ${resultOutputItemValueStart}`;
	} else if (resultOutputItemOperator === globalManifest.comparatorExtended.B || resultOutputItemOperator === globalManifest.comparatorExtended.BS || resultOutputItemOperator === globalManifest.comparatorExtended.BN || resultOutputItemOperator === globalManifest.comparatorExtended.BNS) {
		outputName = `is ${operatorLabel} between ${resultOutputItemValueStart} and ${resultOutputItemValueEnd}`;
	} else {
		outputName = `${operatorLabel} ${resultOutputItemValueStart}`;
	}

	if (!isValidConfiguration) {
		return (
			<div className={clsx(blockClass, 'esf:p-8 es:font-sans es:text-sm')}>
				<ContainerGroup>
					<Container
						className='es-uic-theme-orange'
						centered
						elevated
						compact
						accent
					>
						{__('Result output item', 'eightshift-forms')}
					</Container>

					<Container centered>
						<RichLabel
							icon={warningFill}
							label={__('Missing configuration options!', 'eightshift-forms')}
						/>
					</Container>
				</ContainerGroup>
			</div>
		);
	}

	return (
		<div className={clsx(blockClass, 'esf:p-8 esf:border esf:border-current/10 esf:border-dashed esf:rounded-2xl')}>
			<div className='es:font-sans es:text-sm'>
				<ContainerGroup>
					<Container
						centered
						elevated
						compact
						accent
					>
						{__('Result output item', 'eightshift-forms')}
					</Container>
					<Container centered>
						<RichLabel
							icon={visible}
							label={__('Show', 'eightshift-forms')}
							className='esf:font-bold! es:text-mist-600-600'
						/>
						&nbsp;
						{__('if the variable name is', 'eightshift-forms')}
						&nbsp;
						<span className='esf:font-medium es:font-mono es:text-mist-600-600'>{resultOutputItemName}</span>
						&nbsp;
						{__('and variable value', 'eightshift-forms')}
						&nbsp;
						<span className='esf:font-medium es:font-mono es:text-mist-600-600'>{outputName}</span>
					</Container>
				</ContainerGroup>
			</div>

			<div {...innerBlocksProps} />
		</div>
	);
};
