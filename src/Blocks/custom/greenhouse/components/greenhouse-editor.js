import React from 'react';
import { __ } from '@wordpress/i18n';

export const GreenhouseEditor = ({ attributes }) => {
	const {
		blockClass,
	} = attributes;

	return (
		<div className={blockClass}>
			{__('Greenhouse form will be rendered here!', 'eightshift-forms')}
		</div>
	);
}
