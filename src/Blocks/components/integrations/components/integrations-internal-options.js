import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from "@wordpress/data";
import { PanelBody, Button } from '@wordpress/components';
import { props, Section, Control, icons } from '@eightshift/frontend-libs/scripts';
import { LocationsButton, SettingsButton, resetInnerBlocks } from '../../utils';
import { FormOptions } from '../../../components/form/components/form-options';
import { StepMultiflowOptions } from '../../step/components/step-multiflow-options';

export const IntegrationsInternalOptions = ({
	title,
	attributes,
	setAttributes,
	clientId,
}) => {

	const postId = select('core/editor').getCurrentPostId();

	return (
		<>
			<PanelBody title={title}>
				<Control>
					<div className='es-fifty-fifty-h es-gap-2!'>
						<SettingsButton />
						<LocationsButton />
					</div>
				</Control>

				<Section icon={icons.warning} label={__('Danger zone', 'eightshift-forms')}>
					<Control help={__('If you want to use a different integration for this form. Current configuration will be deleted.', 'eightshift-forms')} noBottomSpacing>
						<Button
							icon={icons.reset}
							onClick={() => {
								// Reset block to original state.
								resetInnerBlocks(clientId, true);
							}}
							className='es-rounded-1 es-border-cool-gray-300 es-hover-border-cool-gray-400 es-transition'
						>
							{__('Reset form', 'eightshift-forms')}
						</Button>
					</Control>
				</Section>

				<FormOptions
					{...props('form', attributes, {
						setAttributes,
					})}
				/>

			</PanelBody>

			<StepMultiflowOptions
				{...props('step', attributes, {
					setAttributes,
					stepMultiflowPostId: postId,
				})}
			/>
		</>
	);
};
