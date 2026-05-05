import { InspectorControls } from '@wordpress/block-editor';
import { ResultOutputEditor } from './components/result-output-editor';
import { ResultOutputOptions } from './components/result-output-options';

export const ResultOutput = (props) => {
	return (
		<>
			<InspectorControls>
				<ResultOutputOptions {...props} />
			</InspectorControls>
			<ResultOutputEditor {...props} />
		</>
	);
};
