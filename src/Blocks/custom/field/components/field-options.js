import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { props } from '@eightshift/frontend-libs/scripts';
import { FieldOptionsLayout } from './../../../components/field/components/field-options';

// This block is only used if you want to include custom external blocks to forms.
export const FieldOptions = ({ attributes, setAttributes }) => {
	return (
		<PanelBody title={__('Field', 'eightshift-forms')}>
			<FieldOptionsLayout
				{...props('field', attributes, {
					setAttributes,
				})}
				prefix={'field'}
				fieldWidthLarge={attributes.fieldWidthLarge}
				fieldWidthDesktop={attributes.fieldWidthDesktop}
				fieldWidthTablet={attributes.fieldWidthTablet}
				fieldWidthMobile={attributes.fieldWidthMobile}
			/>
		</PanelBody>
	);
};
