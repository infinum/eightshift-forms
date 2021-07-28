import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { TextControl } from '@wordpress/components';
import manifest from '../manifest.json';
import { getAttrKey } from '@eightshift/frontend-libs/scripts';

/**
 * Options component
 *
 * @param {object} props Component props.
 */
export const FormCustomOptions = (attributes) => {
	const {
		setAttributes,
		formAction,
		formMethod,
		formTarget,
	} = attributes;

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
