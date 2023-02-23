import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { DateOptions as DateOptionsComponent } from '../../../components/date/components/date-options';

export const DateOptions = ({ attributes, setAttributes }) => {
	return (
		<DateOptionsComponent
			{...props('date', attributes, {
				setAttributes,
			})}
		/>
	);
};
