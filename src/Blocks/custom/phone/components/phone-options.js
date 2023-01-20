import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { PhoneOptions as PhoneOptionsComponent } from '../../../components/phone/components/phone-options';

export const PhoneOptions = ({ attributes, setAttributes }) => {
	return (
		<PhoneOptionsComponent
			{...props('phone', attributes, {
				setAttributes,
			})}
		/>
	);
};
