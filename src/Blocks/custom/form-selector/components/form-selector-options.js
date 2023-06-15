import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { SettingsButton } from '../../../components/utils';

export const FormSelectorOptions = () => {
	return (
		<PanelBody title={__('Eightshift Forms', 'eightshift-forms')}>
			<SettingsButton />
		</PanelBody>
	);
};
