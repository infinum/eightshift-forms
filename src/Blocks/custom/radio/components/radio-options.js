import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { props } from '@eightshift/frontend-libs/scripts';
import { RadioOptions as CheckboxOptionsComponent } from '../../../components/radio/components/radio-options';

export const RadioOptions = ({ attributes, setAttributes }) => {
	return (
		<PanelBody title={__('Radio', 'eightshift-forms')}>
			<CheckboxOptionsComponent
				{...props('radio', attributes, {
					setAttributes: setAttributes,
				})}
			/>
		</PanelBody>
	);
};
