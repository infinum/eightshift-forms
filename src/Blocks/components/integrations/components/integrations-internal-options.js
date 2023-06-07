import React from 'react';
import { select } from "@wordpress/data";
import { PanelBody } from '@wordpress/components';
import { props } from '@eightshift/frontend-libs/scripts';
import { SettingsButton } from '../../utils';
import { FormOptions } from '../../../components/form/components/form-options';
import { StepMultiflowOptions } from '../../step/components/step-multiflow-options';

export const IntegrationsInternalOptions = ({
	title,
	attributes,
	setAttributes,
}) => {

	const postId = select('core/editor').getCurrentPostId();

	return (
		<>
			<PanelBody title={title}>
				<SettingsButton />

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
