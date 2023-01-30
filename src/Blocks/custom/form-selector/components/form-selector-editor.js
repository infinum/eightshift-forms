import React from 'react';
import { camelCase } from 'lodash';
import { __, sprintf } from '@wordpress/i18n';
import { Button, Placeholder } from '@wordpress/components';
import { InnerBlocks } from '@wordpress/block-editor';
import { checkAttr, BlockIcon } from '@eightshift/frontend-libs/scripts';
import { createBlockFromTemplate, getAdditionalContentFilterContent } from './../../../components/utils';
import manifest from './../manifest.json';
import utilsManifest from './../../../components/utils/manifest.json';

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
							} = form;

							return (
								<Button
									className='esf-form-type-picker-button'
									key={index}
									isTertiary
									onClick={() => {
										createBlockFromTemplate(clientId, slug, forms);
									}}
								>
									<div dangerouslySetInnerHTML={{__html: utilsManifest.icons[camelCase(slug)]}} />
									{sprintf(__('%s form', 'eightshift-forms'), label)}
								</Button>
							);
						})}
					</div>
				</Placeholder>
			}

			<div dangerouslySetInnerHTML={{__html: getAdditionalContentFilterContent('formSelector')}} />

			<InnerBlocks
				allowedBlocks={(typeof formSelectorAllowedBlocks === 'undefined') || formSelectorAllowedBlocks}
				templateLock={hasInnerBlocks && 'insert'}
				renderAppender={false}
			/>
		</>
	);
};
