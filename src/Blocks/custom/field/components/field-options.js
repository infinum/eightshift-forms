import React from 'react';
import { select } from '@wordpress/data';
import { STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { FieldPanel } from './../../../components/field/components/field-options-advanced';

// This block is only used if you want to include custom external blocks to forms.
export const FieldOptions = ({ attributes, setAttributes }) => {
	return (
		<FieldPanel
			fieldManifest={select(STORE_NAME).getComponent('field')}
			attributes={attributes}
			setAttributes={setAttributes}
			showPanel={true}
		/>
	);
};
