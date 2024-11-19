import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { PaycekEditor } from './components/paycek-editor';
import { PaycekOptions } from './components/paycek-options';

export const Paycek = (props) => {
	return (
		<>
			<InspectorControls>
				<PaycekOptions {...props} />
			</InspectorControls>
			<PaycekEditor {...props} />
		</>
	);
};
