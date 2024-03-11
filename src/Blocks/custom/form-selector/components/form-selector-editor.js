import React from 'react';
import { camelCase } from 'lodash';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { Button, Placeholder } from '@wordpress/components';
import { InnerBlocks } from '@wordpress/block-editor';
import { STORE_NAME, checkAttr, icons } from '@eightshift/frontend-libs/scripts';
import { createBlockFromTemplate, DashboardButton } from './../../../components/utils';
import { getUtilsIcons } from '../../../components/form/assets/state-init';

export const FormSelectorEditor = ({
	attributes,
	clientId,
	hasInnerBlocks,
}) => {
	const manifest = select(STORE_NAME).getBlock('form-selector');

	const {
		forms,
	} = manifest;

	const formSelectorAllowedBlocks = checkAttr('formSelectorAllowedBlocks', attributes, manifest);

	return (
		<>
			{!hasInnerBlocks &&
				<Placeholder
					icon={icons.form}
					label={<span className='es-font-weight-400'>{__('Eightshift Forms', 'eightshift-forms')}</span>}
					className='es-max-w-108 es-rounded-3! es-mx-auto! es-font-weight-400 es-color-cool-gray-500! es-nested-color-current!'
				>
					<h4 className='es-mb-0! es-mx-0! es-mt-1! es-text-5 es-font-weight-500 es-color-pure-black es-min-w-full {'>{__('What type is your new form?', 'eightshift-forms')}</h4>
					{forms.length > 0 &&
						<div className='es-h-spaced-wrap es-gap-2!'>
							{forms.map((form, index) => {
									const { label, slug, icon } = form;

									let iconComponent = icon;

									if (!icon) {
										iconComponent = getUtilsIcons(camelCase(slug));
									}

									return (
										<Button
											key={index}
											className='es-v-spaced es-content-center! es-m-0! es-nested-w-8 es-nested-h-8 es-h-auto es-w-32 es-h-24 es-rounded-1.5 es-border es-border-cool-gray-100 es-hover-border-cool-gray-400 es-transition es-nested-m-0!'
											onClick={() => createBlockFromTemplate(clientId, slug, forms)}
											icon={<div dangerouslySetInnerHTML={{ __html: iconComponent }} />}
										>
											{label}
										</Button>
									);
								})}
						</div>
					}

					{forms.length < 1 &&
						<>
							{__('It appears that you don\'t have any active integrations set up for your project. Please go to the Eightshift Forms dashboard and configure your first integration.', 'eightshift-forms')}
							<DashboardButton />
						</>
					}
				</Placeholder>
			}

			<InnerBlocks
				allowedBlocks={(typeof formSelectorAllowedBlocks === 'undefined') || formSelectorAllowedBlocks}
				templateLock={hasInnerBlocks && 'insert'}
				renderAppender={false}
			/>
		</>
	);
};
