import classnames from 'classnames';
import { __ } from '@wordpress/i18n';
import { InnerBlocks } from '@wordpress/block-editor';
import { useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { Placeholder } from '@wordpress/components';
import { checkAttr } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

/**
 * Form editor.
 *
 * @param {object} props Props.
 */

export const FormEditor = ({ attributes, clientId }) => {
	const {
		blockClass,
	} = manifest;

	const classes = checkAttr('formClasses', attributes, manifest);
	const id = checkAttr('formId', attributes, manifest);

	const blockClasses = classnames(
		blockClass,
		classes
	);

	const [hasInnerBlocks, setHasInnerBlocks] = useState(false);

	useSelect((select) => {
		const hasInner = select('core/block-editor').getBlock(clientId).innerBlocks.length > 0;

		if (
			(!hasInnerBlocks && hasInner) ||
			(hasInnerBlocks && !hasInner)
		) {
			setHasInnerBlocks(hasInner);
		}
	});

	return (
		<form className={blockClasses} id={id}>
			{!hasInnerBlocks &&
				<Placeholder
					icon="welcome-write-blog"
					label={__('This is an empty form. Click on me to start building your form.', 'eightshift-forms')}
				/>
			}
			<InnerBlocks
				templateLock={false}
			/>
		</form>
	);
};
