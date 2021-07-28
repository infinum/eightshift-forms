import { Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';

import { getActions } from '@eightshift/frontend-libs/scripts/editor';
import manifest from './manifest.json';

import { FormsEditor } from './components/forms-editor';
import { FormsOptions } from './components/forms-options';

export const Forms = (props) => {
	const {
		attributes,
	} = props;

	const actions = getActions(props, manifest);

	return (
		<>
			<InspectorControls>
				<FormsOptions
					attributes={attributes}
					actions={actions}
				/>
			</InspectorControls>
			<FormsEditor
				attributes={attributes}
				actions={actions}
			/>
		</>
	);
};
