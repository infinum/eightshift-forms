import React from 'react';
import { __ } from '@wordpress/i18n';
import { props } from '@eightshift/frontend-libs/scripts';
import { InputOptions as InputOptionsComponent } from '../../../components/input/components/input-options';

export const SenderEmailOptions = ({ attributes, setAttributes }) => {
	return (
		<InputOptionsComponent
			{...props('input', attributes, {
				setAttributes,
			})}
			showInputName={false}
			showInputType={false}
			showInputValidationOptions={false}
			title={__('Sender\'s e-mail', 'eightshift-forms')}
		/>
	);
};
