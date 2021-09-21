import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../field/components/field-options';

export const RadiosOptions = (attributes) => {
	return (
		<>
			<FieldOptions
				{...props('field', attributes)}
			/>
		</>
	);
};
