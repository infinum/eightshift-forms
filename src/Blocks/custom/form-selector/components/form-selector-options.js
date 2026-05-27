import { SettingsButton, GlobalSettingsButton } from '../../../components/utils';
import { ButtonGroup, ContainerPanel } from '@eightshift/ui-components';

export const FormSelectorOptions = () => {
	return (
		<ContainerPanel>
			<ButtonGroup>
				<SettingsButton />
				<GlobalSettingsButton />
			</ButtonGroup>
		</ContainerPanel>
	);
};
