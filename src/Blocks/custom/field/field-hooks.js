/* global esFormsLocalization */

import { addFilter } from '@wordpress/hooks';
import _ from 'lodash';
import { select } from '@wordpress/data';
import { createHigherOrderComponent } from '@wordpress/compose';
import { STORE_NAME } from '@eightshift/frontend-libs/scripts/editor';
import { Field } from './field-block';
import manifest from './manifest.json';
import manifestField from './../../components/field/manifest.json';

// Wrap none forms block with field block.
const setNoneEightshiftFormsBlocksField = createHigherOrderComponent((BlockEdit) => {
	const postType = select('core/editor').getCurrentPostType();

	return (innerProps) => {
		const {
			name,
		} = innerProps;

		// Change only none forms blocks in forms post type.
		if (postType === 'eightshift-forms' && !name.includes('eightshift-forms')) {
			return (
				<Field {...innerProps}>
					<BlockEdit {...innerProps} />
				</Field>
			);
		}

		// Return normal flow.
		return <BlockEdit {...innerProps} />;
	};
}, 'setNoneEightshiftFormsBlocksField');


// Add none forms block attributes from field block.
function setNoneEightshiftBlocksFieldAttributes( settings, name ) {

	// Change only none forms blocks in forms post type.
	if (esFormsLocalization?.postType === 'eightshift-forms' && !name.includes('eightshift-forms')) {
		return _.assign({}, settings, {
			attributes: _.assign( {}, settings.attributes, manifestField.attributes),
			responsiveAttributes: _.assign( {}, settings.responsiveAttributes, manifestField.responsiveAttributes),
			variables: _.assign( {}, settings.variables, manifestField.variables),
		});
	}

	return settings;
}

export const hooks = () => {
	const namespace = select(STORE_NAME).getSettingsNamespace();

	addFilter('editor.BlockEdit', `${namespace}/${manifest.blockName}`, setNoneEightshiftFormsBlocksField);
	addFilter('blocks.registerBlockType', `${namespace}/${manifest.blockName}`, setNoneEightshiftBlocksFieldAttributes);
};
