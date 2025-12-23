/* global esFormsLocalization */

import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { Field } from './field-block';
import manifestField from './manifest.json';
import manifestConditionalTags from '../../components/conditional-tags/manifest.json';
import globalManifest from '../../manifest.json';

// Wrap none forms block with field block.
const setNoneEightshiftFormsBlocksField = createHigherOrderComponent((BlockEdit) => {
	return (innerProps) => {
		const { name } = innerProps;

		// Change only none forms blocks in forms post type.
		if (esFormsLocalization?.currentPostType.isForms && !name.includes(esFormsLocalization?.postTypes?.forms)) {
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
function setNoneEightshiftBlocksFieldAttributes(settings, name) {
	// Change only none forms blocks in forms post type.
	if (esFormsLocalization?.currentPostType.isForms && !name.includes(esFormsLocalization?.postTypes?.forms)) {
		return {
			...settings,
			attributes: {
				...settings.attributes,
				...manifestField.attributes,
				...manifestConditionalTags.attributes,
			},
		};
	}

	return settings;
}

export const hooks = () => {
	const { blockName } = manifestField;
	const { namespace } = globalManifest;

	addFilter('editor.BlockEdit', `${namespace}/${blockName}`, setNoneEightshiftFormsBlocksField);
	addFilter('blocks.registerBlockType', `${namespace}/${blockName}`, setNoneEightshiftBlocksFieldAttributes);
};
