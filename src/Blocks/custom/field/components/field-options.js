import { __ } from '@wordpress/i18n';
import { props } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldOptionsExternalBlocks, FieldOptionsLayout } from './../../../components/field/components/field-options';
import { ContainerPanel, Tab, TabList, TabPanel, Tabs } from '@eightshift/ui-components';
import { design, sliders } from '@eightshift/ui-components/icons';

// This block is only used if you want to include custom external blocks to forms.
export const FieldOptions = ({ attributes, setAttributes }) => {
	return (
		<>
			<Tabs>
				<TabList>
					<Tab
						icon={sliders}
						label={__('General', 'eightshift-forms')}
					/>
					<Tab
						icon={design}
						label={__('Design', 'eightshift-forms')}
					/>
				</TabList>

				<TabPanel>
					<ContainerPanel>
						<FieldOptionsExternalBlocks
							attributes={attributes}
							setAttributes={setAttributes}
							prefix='field'
						/>
					</ContainerPanel>
				</TabPanel>

				<TabPanel>
					<ContainerPanel>
						<FieldOptionsLayout
							{...props('field', attributes, {
								setAttributes,
							})}
							prefix='field'
							fieldWidthLarge={attributes.fieldWidthLarge}
							fieldWidthDesktop={attributes.fieldWidthDesktop}
							fieldWidthTablet={attributes.fieldWidthTablet}
							fieldWidthMobile={attributes.fieldWidthMobile}
						/>
					</ContainerPanel>
				</TabPanel>
			</Tabs>
		</>
	);
};
