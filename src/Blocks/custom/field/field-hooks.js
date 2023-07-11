/* global esFormsLocalization */

import { addFilter } from '@wordpress/hooks';
import _ from 'lodash';
import { select } from '@wordpress/data';
import { createHigherOrderComponent } from '@wordpress/compose';
import { STORE_NAME } from '@eightshift/frontend-libs/scripts/editor';
import { Field } from './field-block';

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
		const {
			attributes,
			variables,
			responsiveAttributes,
		} = select(STORE_NAME).getComponent('field');

		return _.assign({}, settings, {
			attributes: _.assign( {}, settings.attributes, attributes),
			responsiveAttributes: _.assign( {}, settings.responsiveAttributes, responsiveAttributes),
			variables: _.assign( {}, settings.variables, variables),
		});
	}

	return settings;
}

export const hooks = () => {
	const { blockName } = select(STORE_NAME).getBlock('field');
	const namespace = select(STORE_NAME).getSettingsNamespace();

	addFilter('editor.BlockEdit', `${namespace}/${blockName}`, setNoneEightshiftFormsBlocksField);
	addFilter('blocks.registerBlockType', `${namespace}/${blockName}`, setNoneEightshiftBlocksFieldAttributes);
};
