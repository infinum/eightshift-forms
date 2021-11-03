import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { props, } from '@eightshift/frontend-libs/scripts';
import { FormOptions as FormOptionsComponent } from '../../../components/form/components/form-options';

export const FormOptions = ({ attributes, setAttributes }) => {
	return (
		<PanelBody title={__('Form', 'eightshift-forms')}>
			<FormOptionsComponent
				{...props('form', attributes, {
					setAttributes,
				})}
			/>
		</PanelBody>
	);
};
