import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { props } from '@eightshift/frontend-libs/scripts';
import { SelectOptionOptions as SelectOptionOptionsComponent } from '../../../components/select-option/components/select-option-options';

export const SelectOptionOptions = ({ attributes, setAttributes }) => {
	return (
		<PanelBody title={\__('Select option', 'eightshift-forms')}>
			<SelectOptionOptionsComponent
				{...props('selectOption', attributes, {
					setAttributes,
				})}
			/>
		</PanelBody>
	);
};
