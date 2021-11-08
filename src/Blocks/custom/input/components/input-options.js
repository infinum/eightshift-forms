import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { props } from '@eightshift/frontend-libs/scripts';
import { InputOptions as InputOptionsComponent } from '../../../components/input/components/input-options';

export const InputOptions = ({ attributes, setAttributes }) => {
	return (
		<PanelBody title={__('Input', 'eightshift-forms')}>
			<InputOptionsComponent
				{...props('input', attributes, {
					setAttributes,
				})}
			/>
		</PanelBody>
	);
};
