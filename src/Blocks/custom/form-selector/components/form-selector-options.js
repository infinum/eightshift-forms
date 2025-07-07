import React from 'react';
import { __ } from '@wordpress/i18n';
import { ContainerPanel } from '@eightshift/ui-components';
import { SettingsButton, GlobalSettingsButton } from '../../../components/utils';

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
