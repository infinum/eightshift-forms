import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from "@wordpress/data";
import { PanelBody, Button } from '@wordpress/components';
import { icons, props } from '@eightshift/frontend-libs/scripts';
import { FormOptions } from '../../../components/form/components/form-options';
import { getSettingsPageUrl } from '../../../components/utils';

export const MailerOptions = ({ attributes, setAttributes }) => {
	const postId = select('core/editor').getCurrentPostId();

	return (
		<PanelBody title={__('Mailer form', 'eightshift-forms')}>
			<Button
				href={getSettingsPageUrl(postId)}
				variant="secondary"
				icon={icons.options}
			>
				{__('Open form settings', 'eightshift-forms')}
			</Button>

			<hr />

			<FormOptions
				{...props('form', attributes, {
					setAttributes,
				})}
			/>
		</PanelBody>
	);
};
