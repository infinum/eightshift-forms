import React from 'react';
import { __ } from '@wordpress/i18n';
import { Placeholder } from '@wordpress/components';
import { InnerBlocks } from '@wordpress/block-editor';
import { icons } from '@eightshift/ui-components/icons';
import { Button } from '@eightshift/ui-components';
import { createBlockFromTemplate, DashboardButton } from './../../../components/utils';
import { camelCase } from '@eightshift/ui-components/utilities';
import { getUtilsIcons } from '../../../components/form/assets/state-init';
import globalSettings from './../../../manifest.json';
import manifest from '../manifest.json';

export const FormSelectorEditor = ({ clientId, hasInnerBlocks }) => {
	const { forms } = manifest;

	return (
		<>
			{!hasInnerBlocks && (
				<Placeholder
					icon={icons.form}
					label={<span>{__('Eightshift Forms', 'eightshift-forms')}</span>}
				>
					<h4>{__('What type is your new form?', 'eightshift-forms')}</h4>
					{forms.length > 0 && (
						<div>
							{forms.map((form, index) => {
								const { label, slug, icon } = form;

								let iconComponent = icon;

								if (!icon) {
									iconComponent = getUtilsIcons(camelCase(slug));
								}

								return (
									<Button
										key={index}
										onClick={() => createBlockFromTemplate(clientId, slug, forms)}
										icon={<div dangerouslySetInnerHTML={{ __html: iconComponent }} />}
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
				allowedBlocks={[
					...globalSettings.allowedBlocksList.integrationsBuilder,
					...globalSettings.allowedBlocksList.integrationsNoBuilder,
				]}
			/>
		</>
	);
};
