import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { Button, Placeholder } from '@wordpress/components';
import { InnerBlocks } from '@wordpress/block-editor';
import { STORE_NAME } from '@eightshift/frontend-libs-tailwind/scripts';
import { createBlockFromTemplate, DashboardButton } from './../../../components/utils';
import globalSettings from './../../../manifest.json';
import { icons } from '@eightshift/ui-components/icons';

export const FormSelectorEditor = ({ clientId, hasInnerBlocks }) => {
	const manifest = select(STORE_NAME).getBlock('form-selector');

	const { forms } = manifest;

	return (
		<>
			{!hasInnerBlocks && (
				<Placeholder
					icon={icons.form}
					label={__('Eightshift Forms', 'eightshift-forms')}
				>
					<h4>{__('What type is your new form?', 'eightshift-forms')}</h4>
					{forms.length > 0 && (
						<div>
							{forms.map((form, index) => {
								const { label, slug } = form;

								return (
									<Button
										key={index}
										onClick={() => createBlockFromTemplate(clientId, slug, forms)}
									>
										{label}
									</Button>
								);
							})}
						</div>
					)}

					{forms.length < 1 && (
						<>
							{__(
								"It appears that you don't have any active integrations set up for your project. Please go to the Eightshift Forms dashboard and configure your first integration.",
								'eightshift-forms',
							)}
							<DashboardButton />
						</>
					)}
				</Placeholder>
			)}

			<InnerBlocks
				templateLock={false}
				allowedBlocks={[...globalSettings.allowedBlocksList.integrationsBuilder, ...globalSettings.allowedBlocksList.integrationsNoBuilder]}
			/>
		</>
	);
};
