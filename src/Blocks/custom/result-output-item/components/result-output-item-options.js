import React from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl } from '@wordpress/components';
import {
	checkAttr,
	getAttrKey,
	icons,
	IconLabel,
	Select,
} from '@eightshift/frontend-libs/scripts';
import {
	CONDITIONAL_TAGS_OPERATORS_EXTENDED_LABELS,
	CONDITIONAL_TAGS_OPERATORS_LABELS,
} from './../../../components/conditional-tags/components/conditional-tags-labels';
import { getConstantsOptions, NameField } from './../../../components/utils';
import manifest from '../manifest.json';
import globalManifest from '../../../manifest.json';

export const ResultOutputItemOptions = ({
	attributes,
	setAttributes,
}) => {
	const [isNameChanged, setIsNameChanged] = useState(false);

	const resultOutputItemName = checkAttr('resultOutputItemName', attributes, manifest);
	const resultOutputItemValueStart = checkAttr('resultOutputItemValue', attributes, manifest);
	const resultOutputItemValueEnd = checkAttr('resultOutputItemValueEnd', attributes, manifest);
	const resultOutputItemOperator = checkAttr('resultOutputItemOperator', attributes, manifest);

	const [showEndValue, setShowEndValue] = useState(resultOutputItemOperator.toUpperCase() in globalManifest.comparatorExtended);

	return (
		<PanelBody title={__('Result item', 'eightshift-forms')}>
			<NameField
				value={resultOutputItemName}
				attribute={getAttrKey('resultOutputItemName', attributes, manifest)}
				setAttributes={setAttributes}
				type='resultOutputItem'
				isChanged={isNameChanged}
				setIsChanged={setIsNameChanged}
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
					setShowEndValue(value.toUpperCase() in globalManifest.comparatorExtended);
					setAttributes({ [getAttrKey('resultOutputItemOperator', attributes, manifest)]: value });
				}}
				simpleValue
				closeMenuAfterSelect
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
				value={resultOutputItemValueStart}
				onChange={(value) => setAttributes({ [getAttrKey('resultOutputItemValue', attributes, manifest)]: value })}
			/>

			{showEndValue && 
				<TextControl
					label={<IconLabel icon={icons.positionHEnd} label={__('Variable value end', 'eightshift-forms')} />}
					value={resultOutputItemValueEnd}
					onChange={(value) => setAttributes({ [getAttrKey('resultOutputItemValueEnd', attributes, manifest)]: value })}
					help={showEndValue && __('End value must be number.', 'eightshift-forms')}
				/>
			}

		</PanelBody>
	);
};
