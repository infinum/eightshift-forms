import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../field/components/field-options';

export const RadioOptions = (attributes) => {
	return (
		<>
			<FieldOptions
				{...props('field', attributes)}
			/>
		</>
	);
};
