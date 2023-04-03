import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { StepEditor as StepEditorComponent } from '../../../components/step/components/step-editor';

export const StepEditor = ({ attributes, setAttributes, clientId }) => {
	return (
		<StepEditorComponent
			{...props('step', attributes, {
				setAttributes,
				stepUniqueId: clientId,
				stepServerSideRender: true
			})}
		/>
	);
};
