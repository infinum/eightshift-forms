import { Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';

import { getActions } from '@eightshift/frontend-libs/scripts/editor';
import manifest from './manifest.json';

import { SubmitEditor } from './components/submit-editor';
import { SubmitOptions } from './components/submit-options';

export const Submit = (props) => {
	const {
		attributes,
	} = props;

	const actions = getActions(props, manifest);

	return (
		<>
			<InspectorControls>
				<SubmitOptions
					attributes={attributes}
					actions={actions}
				/>
			</InspectorControls>
			<SubmitEditor
				attributes={attributes}
				actions={actions}
			/>
		</>
	);
};
