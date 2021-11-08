import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { props } from '@eightshift/frontend-libs/scripts';
import { SelectOptions as SelectOptionsComponent } from '../../../components/select/components/select-options';

export const SelectOptions = ({ attributes, setAttributes }) => {
	return (
		<PanelBody title={__('Select', 'eightshift-forms')}>
			<SelectOptionsComponent
				{...props('select', attributes, {
					setAttributes,
				})}
			/>
		</PanelBody>
	);
};
