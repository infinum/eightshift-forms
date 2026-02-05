import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { props } from '@eightshift/frontend-libs-tailwind/scripts';
import { BaseControl, Button, ContainerPanel, Spacer, HStack } from '@eightshift/ui-components';
import { LocationsButton, SettingsButton, resetInnerBlocks } from '../../utils';
import { icons } from '@eightshift/ui-components/icons';
import { FormOptions } from '../../../components/form/components/form-options';
import { StepMultiflowOptions } from '../../step/components/step-multiflow-options';

export const IntegrationsInternalOptions = ({ attributes, setAttributes, clientId }) => {
	const postId = select('core/editor').getCurrentPostId();

	return (
		<>
			<ContainerPanel>
				<HStack>
					<SettingsButton />
					<LocationsButton />
				</HStack>

				<Spacer
					border
					icon={icons.warning}
					text={__('Danger zone', 'eightshift-forms')}
				/>

				<BaseControl
					help={__(
						'If you want to use a different integration for this form. Current configuration will be deleted.',
						'eightshift-forms',
					)}
				>
					<Button
						icon={icons.reset}
						onClick={() => {
							// Reset block to original state.
							resetInnerBlocks(clientId, true);
						}}
					>
						{__('Reset form', 'eightshift-forms')}
					</Button>
				</BaseControl>

				<FormOptions
					{...props('form', attributes, {
						setAttributes,
					})}
				/>
			</ContainerPanel>

			<StepMultiflowOptions
				{...props('step', attributes, {
					setAttributes,
					stepMultiflowPostId: postId,
				})}
			/>
		</>
	);
};
