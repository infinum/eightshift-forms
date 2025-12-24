/* global esFormsLocalization */

import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { Field } from '../../custom/field/field-block';
import formsManifest from '../../custom/forms/manifest.json';
import manifestField from '../../components/field/manifest.json';
import manifestConditionalTags from '../../components/conditional-tags/manifest.json';
import globalManifest from '../../manifest.json';
import { camelCase, clsx, upperFirst } from '@eightshift/ui-components/utilities';

const { namespace, allowedBlocksList } = globalManifest;

const onlyFields = [
	...allowedBlocksList.formsCpt,
	...allowedBlocksList.other,
	...allowedBlocksList.fieldsIntegration,
	...allowedBlocksList.fieldsWithChildren,
	...allowedBlocksList.integrationsNoBuilder,
	...allowedBlocksList.integrationsBuilder,
];

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

// Set editor block list block.
const setEditorBlockListBlock = createHigherOrderComponent((BlockListBlock) => {
	return (innerProps) => {
		const { name, attributes, clientId } = innerProps;

		if (esFormsLocalization?.currentPostType.isForms && !onlyFields.includes(name)) {
			let key = `${attributes.blockName}${upperFirst(camelCase(attributes.blockName))}FieldWidthLarge`;

			if (esFormsLocalization.additionalBlocks.includes(name)) {
				key = 'fieldWidthLarge';
			}

			const fieldWidthLarge = attributes?.[key];

			const componentClass = clsx(
				fieldWidthLarge && `esf:col-span-${fieldWidthLarge}!`,
				attributes.blockClass,
				globalManifest.globalVariables.customBlocksName,
			);

			const updatedProps = {
				...innerProps,
				className: componentClass,
			};

			return <BlockListBlock {...updatedProps} />;
		}

		if (name === formsManifest.blockName) {
			const componentClass = clsx(attributes.blockClass, globalManifest.globalVariables.customBlocksName);

			const updatedProps = {
				...innerProps,
				className: componentClass,
			};

			return (
				<BlockListBlock
					{...updatedProps}
					wrapperProps={{ 'data-id': clientId }}
				/>
			);
		}

		return <BlockListBlock {...innerProps} />;
	};
}, 'setEditorBlockListBlock');

addFilter('editor.BlockEdit', namespace, setNoneEightshiftFormsBlocksField);
addFilter('blocks.registerBlockType', namespace, setNoneEightshiftBlocksFieldAttributes);
addFilter('editor.BlockListBlock', namespace, setEditorBlockListBlock);
