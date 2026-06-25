import { Toaster } from 'sonner';
import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { form, JsxSvg, warningCircle } from '@eightshift/ui-components/icons';
import { Button, VStack, HStack, RichLabel, Spacer } from '@eightshift/ui-components';
import { createBlockFromTemplate, DashboardButton } from './../../../components/utils';
import { camelCase } from '@eightshift/ui-components/utilities';
import { getUtilsIcons } from '../../../components/form/assets/state-init';
import globalSettings from './../../../manifest.json';
import manifest from '../manifest.json';

export const FormSelectorEditor = ({ clientId, hasInnerBlocks }) => {
	const { forms } = manifest;

	const blockProps = useBlockProps({
		className: 'esf:max-w-3xl esf:mx-auto',
	});

	return (
		<>
			{!hasInnerBlocks && (
				<div {...blockProps}>
					<VStack
						className='esf:items-center esf:py-8 es:font-sans'
						noWrap
					>
						<RichLabel
							icon={form}
							label={__('Eightshift Forms', 'eightshift-ui-kit')}
							className='esf:mb-24 esf:not-contrast-more:opacity-60'
						/>

						{forms.length > 0 && (
							<RichLabel
								labelClassName='esf:text-lg!'
								label={__('Create a form', 'eightshift-ui-kit')}
							/>
						)}

						{forms.length < 1 && (
							<VStack className='esf:max-w-xs esf:items-center esf:gap-8 esf:text-center'>
								<RichLabel
									icon={warningCircle}
									label={__('No integrations are active, configure one in the Dashboard', 'eightshift-forms')}
									contentsOnly
								/>

								<Spacer />

								<DashboardButton />
							</VStack>
						)}

						<HStack className='esf:max-w-2xs esf:justify-center'>
							{forms?.map((option) => {
								const { label, slug, icon } = option;

								let iconComponent = icon;

								if (!icon) {
									iconComponent = <JsxSvg svg={getUtilsIcons(camelCase(slug))} />;
								}

								return (
									<Button
										key={slug}
										icon={iconComponent}
										onPress={() => createBlockFromTemplate(clientId, slug, forms)}
										size='large'
										className='esf:size-80! esf:flex-col'
									>
										{label}
									</Button>
								);
							})}
						</HStack>
					</VStack>
				</div>
			)}
			<Toaster
				richColors
				position='bottom-center'
				offset={40}
			/>

			<div {...blockProps}>
				<InnerBlocks
					templateLock={false}
					allowedBlocks={[...globalSettings.allowedBlocksList.integrationsBuilder, ...globalSettings.allowedBlocksList.integrationsNoBuilder]}
				/>
			</div>
		</>
	);
};
