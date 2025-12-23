import React from 'react';
import { __ } from '@wordpress/i18n';
import { SettingsButton, GlobalSettingsButton } from '../../../components/utils';
import { ContainerPanel } from '@eightshift/ui-components';

export const FormSelectorOptions = () => {
	return (
		<ContainerPanel title={__('Eightshift Forms', 'eightshift-forms')}>
			<SettingsButton />
			<br />
			<br />
			<GlobalSettingsButton />
		</ContainerPanel>
	);
};
