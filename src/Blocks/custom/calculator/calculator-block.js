import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { CalculatorEditor } from './components/calculator-editor';
import { CalculatorOptions } from './components/calculator-options';

export const Calculator = (props) => {
	return (
		<>
			<InspectorControls>
				<CalculatorOptions {...props} />
			</InspectorControls>
			<CalculatorEditor {...props} />
		</>
	);
};
