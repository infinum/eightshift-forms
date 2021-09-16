import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { props } from '@eightshift/frontend-libs/scripts';
import { ChoiceOptions as ChoiceOptionsComponent } from '../../../components/choice/components/choice-options';

export const ChoiceOptions = ({ attributes, setAttributes }) => {
	return (
		<PanelBody title={__('Choice', 'eightshift-forms')}>
			<ChoiceOptionsComponent
				{...props('choice', attributes, {
					setAttributes: setAttributes,
				})}
			/>
		</PanelBody>
	);
};
