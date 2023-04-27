import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from "@wordpress/data";
import { PanelBody, Button } from '@wordpress/components';
import { Control, icons, props } from '@eightshift/frontend-libs/scripts';
import { FormOptions } from '../../../components/form/components/form-options';
import { getSettingsPageUrl } from '../../../components/utils';

export const JiraOptions = ({ attributes, setAttributes }) => {
	const postId = select('core/editor').getCurrentPostId();

	return (
		<PanelBody title={__('Jira form', 'eightshift-forms')}>
			<Control>
				<Button
					href={getSettingsPageUrl(postId)}
					icon={icons.options}
					className='es-rounded-1 es-border-cool-gray-300 es-hover-border-cool-gray-400 es-transition'
				>
					{__('Form settings', 'eightshift-forms')}
				</Button>
			</Control>

			<hr />

			<FormOptions
				{...props('form', attributes, {
					setAttributes,
				})}
			/>
		</PanelBody>
	);
};
