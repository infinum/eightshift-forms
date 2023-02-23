import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { Button, BaseControl } from '@wordpress/components';
import { icons } from '@eightshift/frontend-libs/scripts';
import { getSettingsPageUrl } from './../index';

export const SettingsButton = () => {
	const postId = select('core/editor').getCurrentPostId();

	return (
		<BaseControl
			help={__('On form settings page you can setup all additional settings regarding you form.', 'eightshift-forms')}
		>
			<Button
				href={getSettingsPageUrl(postId)}
				variant="secondary"
				icon={icons.options}
			>
				{__('Open form settings', 'eightshift-forms')}
			</Button>
		</BaseControl>
	);
};
