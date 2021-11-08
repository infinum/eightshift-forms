import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { props } from '@eightshift/frontend-libs/scripts';
import { CheckboxesOptions as CheckboxesOptionsComponent } from '../../../components/checkboxes/components/checkboxes-options';

export const CheckboxesOptions = ({ attributes, setAttributes, clientId }) => {
	return (
		<PanelBody title={__('Checkboxes', 'eightshift-forms')}>
			<CheckboxesOptionsComponent
				{...props('checkboxes', attributes, {
					setAttributes,
					clientId
				})}
			/>
		</PanelBody>
	);
};
