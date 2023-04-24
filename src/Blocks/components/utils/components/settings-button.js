import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { Button } from '@wordpress/components';
import { Control, icons } from '@eightshift/frontend-libs/scripts';
import { getSettingsPageUrl } from './../index';

export const SettingsButton = () => {
	const postId = select('core/editor').getCurrentPostId();

	return (
		<Control>
			<Button
				href={getSettingsPageUrl(postId)}
				icon={icons.options}
				className='es-rounded-1 es-border-cool-gray-300 es-hover-border-cool-gray-400 es-transition'
			>
				{__('Form settings', 'eightshift-forms')}
			</Button>
		</Control>
	);
};
