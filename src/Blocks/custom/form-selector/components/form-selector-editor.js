/* global esFormsLocalization */

import React from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { Button, Placeholder } from '@wordpress/components';
import { InnerBlocks } from '@wordpress/block-editor';
import { checkAttr, BlockIcon } from '@eightshift/frontend-libs/scripts';
import { createBlockFromTemplate, getAdditionalContent } from './../../../components/utils';
import manifest from './../manifest.json';

export const FormSelectorEditor = ({
	attributes,
	clientId,
	hasInnerBlocks,
}) => {
	const {
		forms,
	} = manifest;

	const formSelectorAllowedBlocks = checkAttr('formSelectorAllowedBlocks', attributes, manifest);

	return (
		<>
			{!hasInnerBlocks &&
				<Placeholder
					icon={<BlockIcon iconName='esf-form' />}
					label={__('Eightshift Forms', 'productive')}
					instructions={__('Select a form type below to start.', 'productive')}
					className={attributes.blockClass}
				>
					<div className='esf-form-type-picker-group'>
						{forms.map((form, index) => {
							const {
								label,
								slug,
								icon
							} = form;

							return (
								<Button
									className='esf-form-type-picker-button'
									icon={<BlockIcon iconName={icon} />}
									key={index}
									isTertiary
									onClick={() => {
										createBlockFromTemplate(clientId, slug, forms);
									}}
								>
									{sprintf(__('%s form', 'eightshift-forms'), label)}
								</Button>
							);
						})}
					</div>
				</Placeholder>
			}

			<div dangerouslySetInnerHTML={{ __html: getAdditionalContent('formSelectorBlockAdditionalContent') }} />

			<InnerBlocks
				allowedBlocks={(typeof formSelectorAllowedBlocks === 'undefined') || formSelectorAllowedBlocks}
				templateLock={hasInnerBlocks && 'insert'}
				renderAppender={false}
			/>
		</>
	);
};
