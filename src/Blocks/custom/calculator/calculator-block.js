import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { CalculatorEditor } from './components/calculator-editor';
import { CalculatorOptions } from './components/calculator-options';

export const Calculator = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={CalculatorOptions}
			editor={CalculatorEditor}
		/>
	);
};
