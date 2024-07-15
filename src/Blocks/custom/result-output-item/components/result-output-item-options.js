import React from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl } from '@wordpress/components';
import {
	checkAttr,
	getAttrKey,
	icons,
	IconLabel,
	Notification,
	Select,
} from '@eightshift/frontend-libs/scripts';
import {
	CONDITIONAL_TAGS_OPERATORS_EXTENDED_LABELS,
	CONDITIONAL_TAGS_OPERATORS_LABELS,
} from './../../../components/conditional-tags/components/conditional-tags-labels';
import { getConstantsOptions } from './../../../components/utils';
import manifest from '../manifest.json';
import { CONDITIONAL_TAGS_OPERATORS_EXTENDED } from '../../../components/conditional-tags/assets/utils';

export const ResultOutputItemOptions = ({
	attributes,
	setAttributes,
}) => {

	const resultOutputItemName = checkAttr('resultOutputItemName', attributes, manifest);
	const resultOutputItemValue = checkAttr('resultOutputItemValue', attributes, manifest);
	const resultOutputItemValueEnd = checkAttr('resultOutputItemValueEnd', attributes, manifest);
	const resultOutputItemOperator = checkAttr('resultOutputItemOperator', attributes, manifest);

	const [showEndValue, setShowEndValue] = useState(resultOutputItemOperator.toUpperCase() in CONDITIONAL_TAGS_OPERATORS_EXTENDED);

	return (
		<PanelBody title={__('Result item', 'eightshift-forms')}>
			<TextControl
				label={<IconLabel icon={icons.id} label={__('Variable name', 'eightshift-forms')} />}
				value={resultOutputItemName}
				onChange={(value) => setAttributes({ [getAttrKey('resultOutputItemName', attributes, manifest)]: value })}
			/>

			<TextControl
				label={
					<IconLabel icon={icons.positionHStart}
						label={
							showEndValue ?
							__('Variable value start', 'eightshift-forms'):
							__('Variable value', 'eightshift-forms')
						}
					/>
				}
				help={showEndValue && __('Start value must be number.', 'eightshift-forms')}
				value={resultOutputItemValue}
				onChange={(value) => setAttributes({ [getAttrKey('resultOutputItemValue', attributes, manifest)]: value })}
			/>

			<Select
				label={<IconLabel icon={icons.containerSpacing} label={__('Compare operator', 'eightshift-forms')} />}
				value={resultOutputItemOperator}
				options={getConstantsOptions(
					{
						...CONDITIONAL_TAGS_OPERATORS_LABELS,
						...CONDITIONAL_TAGS_OPERATORS_EXTENDED_LABELS,
					}
				)}
				onChange={(value) => {
					setShowEndValue(value.toUpperCase() in CONDITIONAL_TAGS_OPERATORS_EXTENDED);
					setAttributes({ [getAttrKey('resultOutputItemOperator', attributes, manifest)]: value });
				}}
				simpleValue
				closeMenuAfterSelect
			/>

			{showEndValue && 
				<TextControl
					label={<IconLabel icon={icons.positionHEnd} label={__('Variable value end', 'eightshift-forms')} />}
					value={resultOutputItemValueEnd}
					onChange={(value) => setAttributes({ [getAttrKey('resultOutputItemValueEnd', attributes, manifest)]: value })}
					help={showEndValue && __('End value must be number.', 'eightshift-forms')}
				/>
			}

		<Notification
			text={__('The block will not show anything if filters are not added through code! If you have the Computed fields add-on, its output is also supported.', 'eightshift-forms')}
			type='warning'
		/>

		</PanelBody>
	);
};
