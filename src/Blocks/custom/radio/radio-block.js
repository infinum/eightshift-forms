import React from 'react';
import { useSelect } from '@wordpress/data';
import { overrideInnerBlockAttributes } from '@eightshift/frontend-libs/scripts/editor';
import { InspectorControls } from '@wordpress/block-editor';
import { RadioEditor } from './components/radio-editor';
import { RadioOptions } from './components/radio-options';

export const Radio = (props) => {
	const {
		clientId,
	} = props;

	// Set this attributes to all inner blocks once inserted in DOM.
	useSelect((select) => {
		overrideInnerBlockAttributes(
			select,
			clientId,
			{
				choiceChoiceType: 'radio',
			}
		);
	});

	return (
		<>
			<InspectorControls>
				<RadioOptions {...props} />
			</InspectorControls>
			<RadioEditor {...props} />
		</>
	);
};
