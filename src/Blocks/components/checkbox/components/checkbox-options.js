import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../../components/field/components/field-options';

export const CheckboxOptions = (attributes) => {
	return (
		<>
			<FieldOptions
				{...props('field', attributes)}
			/>
		</>
	);
};
