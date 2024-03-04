import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { CalculatorResultEditor } from './components/calculator-result-editor';
import { CalculatorResultOptions } from './components/calculator-result-options';
import { getFetchWpApi } from '@eightshift/frontend-libs/scripts';
import { outputFormSelectItemWithIcon } from '../../components/utils';

const formSelectOptions = function(postType) {
	return getFetchWpApi(
		postType,
		{
			noCache: true,
			processLabel: ({ title: { rendered: label }, integration_type: metadata, id }) => {
				return outputFormSelectItemWithIcon({
					label,
					id,
					metadata,
				})?.label;
			},
			fields: 'id,title,integration_type',
			processMetadata: ({ title: { rendered: label }, integration_type: metadata, id }) => ({
				id,
				value: id,
				label,
				metadata,
			}),
		}
	);
};

export const CalculatorResult = (props) => {
	const {
		forms,
		calculator,
	} = esFormsLocalization?.postTypes;

	return (
		<>
			<InspectorControls>
				<CalculatorResultOptions
					{...props}
					formSelectOptions={formSelectOptions(forms)}
					calculatorSelectOptions={formSelectOptions(calculator)}
				/>
			</InspectorControls>
			<CalculatorResultEditor
				{...props}
				formSelectOptions={formSelectOptions(forms)}
				calculatorSelectOptions={formSelectOptions(calculator)}
			/>
		</>
	);
};
