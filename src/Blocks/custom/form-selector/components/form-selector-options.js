import React from 'react';
import { __ } from '@wordpress/i18n';
import { SettingsButton, GlobalSettingsButton } from '../../../components/utils';
import { ContainerPanel, HStack } from '@eightshift/ui-components';

export const FormSelectorOptions = () => {
	return (
		<ContainerPanel title={__('Eightshift Forms', 'eightshift-forms')}>
			<HStack>
				<SettingsButton />
				<GlobalSettingsButton />
			</HStack>
		</ContainerPanel>
	);
};
