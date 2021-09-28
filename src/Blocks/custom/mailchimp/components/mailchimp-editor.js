import React from 'react';
import { __ } from '@wordpress/i18n';

export const MailchimpEditor = ({ attributes }) => {
	const {
		blockClass,
	} = attributes;

	return (
		<div className={blockClass}>
			{__('Mailchimp form will be rendered here!', 'eightshift-forms')}
		</div>
	);
}
