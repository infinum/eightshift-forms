import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { InnerBlocks } from '@wordpress/block-editor';
import { checkAttr, BlockInserter, selector } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';
import { CONDITIONAL_TAGS_OPERATORS_EXTENDED_LABELS, CONDITIONAL_TAGS_OPERATORS_LABELS } from '../../../components/conditional-tags/components/conditional-tags-labels';
import globalManifest from '../../../manifest.json';

export const ResultOutputItemEditor = ({ attributes, clientId }) => {
	const {
		blockClass,
	} = attributes;

	const resultOutputItemName = checkAttr('resultOutputItemName', attributes, manifest);
	const resultOutputItemValue = checkAttr('resultOutputItemValue', attributes, manifest);
	const resultOutputItemValueEnd = checkAttr('resultOutputItemValueEnd', attributes, manifest);
	const resultOutputItemOperator = checkAttr('resultOutputItemOperator', attributes, manifest);

	const [isValidConfiguration, setIsValidConfiguration] = useState(resultOutputItemName && resultOutputItemValue);

	useEffect(() => {
		setIsValidConfiguration(resultOutputItemName && resultOutputItemValue);
	}, [resultOutputItemName, resultOutputItemValue]);

	const operatorLabel = {
		...CONDITIONAL_TAGS_OPERATORS_LABELS,
		...CONDITIONAL_TAGS_OPERATORS_EXTENDED_LABELS,
	}?.[resultOutputItemOperator] ?? CONDITIONAL_TAGS_OPERATORS_LABELS[globalManifest.comparator.IS];

	return (
		<div className={blockClass}>
			<div className={selector(blockClass, blockClass, 'intro')}>
				{!isValidConfiguration && 
					<div className={selector(blockClass, blockClass, 'intro', 'missing')}>{__(`Missing configuration options!`, 'eightshift-forms')}</div>
				}

				{isValidConfiguration &&
					<>
						<b>{__('SHOW', 'eightshift-forms')}</b>
						{__(` if the variable name is `, 'eightshift-forms')}
						<b>{resultOutputItemName}</b>
						{__(` and variable value`, 'eightshift-forms')}
						<br />
						<b>
							{
								resultOutputItemValueEnd ?
								`${resultOutputItemValue} is ${operatorLabel} ${resultOutputItemValueEnd}`:
								`${operatorLabel} ${resultOutputItemValue}`
							}
						</b>
					</>
				}
			</div>

			{isValidConfiguration &&
				<InnerBlocks
					renderAppender={() => <BlockInserter clientId={clientId} />}
				/>
			}
		</div>
	);
};
