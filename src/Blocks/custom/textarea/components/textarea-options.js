import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { props } from '@eightshift/frontend-libs/scripts';
import { TextareaOptions as TextareaOptionsComponent } from '../../../components/textarea/components/textarea-options';

export const TextareaOptions = ({ attributes, setAttributes }) => {
	return (
		<PanelBody title={__('Textarea', 'eightshift-forms')}>
			<TextareaOptionsComponent
				{...props('textarea', attributes, {
					setAttributes: setAttributes,
				})}
			/>
		</PanelBody>
	);
};
