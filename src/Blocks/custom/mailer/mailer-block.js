import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { MailerEditor } from './components/mailer-editor';
import { MailerOptions } from './components/mailer-options';

export const Mailer = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={MailerOptions}
			editor={MailerEditor}
		/>
	);
};
