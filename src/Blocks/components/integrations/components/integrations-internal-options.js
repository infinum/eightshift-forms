import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { props } from '@eightshift/frontend-libs-tailwind/scripts';
import { BaseControl, Button } from '@eightshift/ui-components';
import { LocationsButton, SettingsButton, resetInnerBlocks } from '../../utils';
import { FormOptions } from '../../../components/form/components/form-options';
import { StepMultiflowOptions } from '../../step/components/step-multiflow-options';
import { icons } from '@eightshift/ui-components/icons';

export const IntegrationsInternalOptions = ({ title, attributes, setAttributes, clientId }) => {
	const postId = select('core/editor').getCurrentPostId();

	return (
		<>
			<BaseControl>
				<div className='es-fifty-fifty-h es-gap-2!'>
					<SettingsButton />
					<LocationsButton />
				</div>
			</BaseControl>

			<BaseControl
				icon={icons.warning}
				label={__('Danger zone', 'eightshift-forms')}
			>
				<BaseControl help={__('If you want to use a different integration for this form. Current configuration will be deleted.', 'eightshift-forms')}>
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
				</BaseControl>
			</BaseControl>

			<FormOptions
				{...props('form', attributes, {
					setAttributes,
				})}
			/>

			<StepMultiflowOptions
				{...props('step', attributes, {
					setAttributes,
					stepMultiflowPostId: postId,
				})}
			/>
		</>
	);
};
