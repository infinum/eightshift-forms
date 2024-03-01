import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { CalculatorEditor } from './components/calculator-editor';
import { CalculatorOptions } from './components/calculator-options';

export const Calculator = (props) => {
	const itemIdKey = 'calculatorIntegrationId';

	return (
		<>
			<InspectorControls>
				<CalculatorOptions
					{...props}
					clientId={props.clientId}
					itemIdKey={itemIdKey}
				/>
			</InspectorControls>
			<CalculatorEditor
				{...props}
				itemIdKey={itemIdKey}
			/>
		</>
	);
};
