import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { TextControl } from '@wordpress/components';
import manifest from '../manifest.json';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';

/**
 * Options component
 *
 * @param {object} props Component props.
 */
export const FormCustomOptions = ({attributes, setAttributes}) => {

	const formAction = checkAttr('formAction', attributes, manifest);
	const formMethod = checkAttr('formMethod', attributes, manifest);
	const formTarget = checkAttr('formTarget', attributes, manifest);

	return (
		<>
			<TextControl
				label={__('Action', 'eightshift-forms')}
				value={formAction}
				onChange={(value) => setAttributes({ [getAttrKey('formAction', attributes, manifest)]: value })}
			/>

			<TextControl
				label={__('Method', 'eightshift-forms')}
				value={formMethod}
				onChange={(value) => setAttributes({ [getAttrKey('formMethod', attributes, manifest)]: value })}
			/>

			<TextControl
				label={__('Target', 'eightshift-forms')}
				value={formTarget}
				onChange={(value) => setAttributes({ [getAttrKey('formTarget', attributes, manifest)]: value })}
			/>
		</>
	);
};
