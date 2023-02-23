import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { PhoneEditor } from './components/phone-editor';
import { PhoneOptions } from './components/phone-options';

export const Phone = (props) => {
	return (
		<>
			<InspectorControls>
				<PhoneOptions {...props} />
			</InspectorControls>
			<PhoneEditor {...props} />
		</>
	);
};
