import React from 'react';
import { __ } from '@wordpress/i18n';
import { props } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldOptionsExternalBlocks, FieldOptionsLayout } from './../../../components/field/components/field-options';
import { ContainerPanel } from '@eightshift/ui-components';

// This block is only used if you want to include custom external blocks to forms.
export const FieldOptions = ({ attributes, setAttributes }) => {
	return (
		<ContainerPanel title={__('Field', 'eightshift-forms')}>
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

			<FieldOptionsExternalBlocks
				attributes={attributes}
				setAttributes={setAttributes}
			/>
		</ContainerPanel>
	);
};
