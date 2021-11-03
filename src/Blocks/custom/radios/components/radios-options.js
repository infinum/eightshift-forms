import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { props } from '@eightshift/frontend-libs/scripts';
import { RadiosOptions as RadiosOptionsComponent } from '../../../components/radios/components/radios-options';

export const RadiosOptions = ({ attributes, setAttributes }) => {
	return (
		<PanelBody title={__('Radios', 'eightshift-forms')}>
			<RadiosOptionsComponent
				{...props('radios', attributes, {
					setAttributes,
				})}
			/>
		</PanelBody>
	);
};
