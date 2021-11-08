import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { props } from '@eightshift/frontend-libs/scripts';
import { SubmitOptions as SubmitOptionsComponent } from '../../../components/submit/components/submit-options';

export const SubmitOptions = ({ attributes, setAttributes }) => {
	return (
		<PanelBody title={__('Submit', 'eightshift-forms')}>
			<SubmitOptionsComponent
				{...props('submit', attributes, {
					setAttributes,
				})}
			/>
		</PanelBody>
	);
};
