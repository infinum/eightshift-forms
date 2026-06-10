import { __ } from '@wordpress/i18n';
import { props } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldOptionsExternalBlocks, FieldOptionsLayout } from './../../../components/field/components/field-options';
import { Tab, TabList, TabPanel, Tabs } from '@eightshift/ui-components';

// This block is only used if you want to include custom external blocks to forms.
export const FieldOptions = ({ attributes, setAttributes }) => {
	return (
		<>
			<Tabs type='chips'>
				<TabList>
					<Tab label={__('General', 'eightshift-forms')} />
					<Tab label={__('Design', 'eightshift-forms')} />
				</TabList>

				<TabPanel>
					<FieldOptionsExternalBlocks
						attributes={attributes}
						setAttributes={setAttributes}
						prefix='field'
					/>
				</TabPanel>

				<TabPanel>
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
				</TabPanel>
			</Tabs>
		</>
	);
};
