import { Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';

import { getActions } from '@eightshift/frontend-libs/scripts/editor';
import manifest from './manifest.json';

import { RadioItemEditor } from './components/radio-item-editor';
import { RadioItemOptions } from './components/radio-item-options';

export const RadioItem = (props) => {
	const {
		attributes,
	} = props;

	const actions = getActions(props, manifest);

	return (
		<>
			<InspectorControls>
				<RadioItemOptions
					attributes={attributes}
					actions={actions}
				/>
			</InspectorControls>
			<RadioItemEditor
				attributes={attributes}
				actions={actions}
			/>
		</>
	);
};
