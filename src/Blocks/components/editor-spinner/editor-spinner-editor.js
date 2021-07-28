import { Spinner, BaseControl } from '@wordpress/components';

export const EditorSpinner = (props) => {
	const {
		label,
		help,
	} = props;

	return (
		<BaseControl label={label} help={help}>
			<div className="editor-spinner">
				<Spinner />
			</div>
		</BaseControl>
	);
};
