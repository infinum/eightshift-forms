import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { PhoneEditor as PhoneEditorComponent } from '../../../components/phone/components/phone-editor';

export const PhoneEditor = ({ attributes, setAttributes, clientId }) => {
	return (
		<PhoneEditorComponent
			{...props('phone', attributes, {
				setAttributes,
				clientId,
			})}
		/>
	);
};
