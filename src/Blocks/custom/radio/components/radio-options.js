import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { props } from '@eightshift/frontend-libs/scripts';
import { FieldsetOptions } from '../../../components/fieldset/components/fieldset-options';

export const RadioOptions = ({ attributes, setAttributes }) => {
	return (
		<PanelBody title={__('Radio', 'eightshift-forms')}>
			<FieldsetOptions
				{...props('fieldset', attributes, {
					setAttributes: setAttributes,
				})}
			/>
		</PanelBody>
	);
};
