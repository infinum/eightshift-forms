import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import {
	FieldOptions,
	FieldOptionsMore,
	FieldOptionsLayout,
	FieldOptionsVisibility,
} from '../../field/components/field-options';
import { isOptionDisabled, NameField } from '../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import {
	checks,
	googleTagManager,
	none,
	requiredAlt,
	star,
	titleGeneric,
	moreH,
	sliders,
	tag,
	design,
} from '@eightshift/ui-components/icons';
import {
	ContainerPanel,
	InputField,
	Toggle,
	NumberPicker,
	Container,
	ContainerGroup,
	Tab,
	TabList,
	Tabs,
	TabPanel,
} from '@eightshift/ui-components';
import manifest from '../manifest.json';

export const RatingOptions = (attributes) => {
	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const ratingName = checkAttr('ratingName', attributes, manifest);
	const ratingValue = checkAttr('ratingValue', attributes, manifest);
	const ratingIsDisabled = checkAttr('ratingIsDisabled', attributes, manifest);
	const ratingIsRequired = checkAttr('ratingIsRequired', attributes, manifest);
	const ratingTracking = checkAttr('ratingTracking', attributes, manifest);
	const ratingDisabledOptions = checkAttr('ratingDisabledOptions', attributes, manifest);
	const ratingAmount = checkAttr('ratingAmount', attributes, manifest);

	return (
		<Tabs>
			<TabList>
				<Tab
					icon={sliders}
					label={__('General', 'eightshift-forms')}
				/>

				<Tab
					icon={tag}
					label={__('Labels', 'eightshift-forms')}
				/>

				<Tab
					icon={design}
					label={__('Design', 'eightshift-forms')}
				/>

				<Tab
					icon={checks}
					label={__('Validation', 'eightshift-forms')}
				/>

				<Tab
					icon={moreH}
					label={__('Advanced', 'eightshift-forms')}
				/>
			</TabList>

			<TabPanel>
				<ContainerPanel>
					<NameField
						value={ratingName}
						attribute={getAttrKey('ratingName', attributes, manifest)}
						disabledOptions={ratingDisabledOptions}
						setAttributes={setAttributes}
						type={'rating'}
						isChanged={isNameChanged}
						setIsChanged={setIsNameChanged}
					/>

					<Container standalone>
						<NumberPicker
							icon={star}
							label={__('Number of stars', 'eightshift-forms')}
							value={ratingAmount}
							onChange={(value) => setAttributes({ [getAttrKey('ratingAmount', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('ratingAmount', attributes, manifest), ratingDisabledOptions)}
							min={1}
							max={10}
							inline
						/>
					</Container>

					<Container standalone>
						<NumberPicker
							icon={titleGeneric}
							label={__('Initial value', 'eightshift-forms')}
							placeholder={__('Enter initial value', 'eightshift-forms')}
							value={ratingValue}
							onChange={(value) => setAttributes({ [getAttrKey('ratingValue', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('ratingValue', attributes, manifest), ratingDisabledOptions)}
							min={0}
							max={ratingAmount}
							fixedWidth={2}
							inline
						/>
					</Container>

					<ContainerGroup>
						<FieldOptionsVisibility
							{...props('field', attributes, {
								fieldDisabledOptions: ratingDisabledOptions,
							})}
						/>

						<Container>
							<Toggle
								icon={none}
								label={__('Disabled', 'eightshift-forms')}
								checked={ratingIsDisabled}
								onChange={(value) => setAttributes({ [getAttrKey('ratingIsDisabled', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('ratingIsDisabled', attributes, manifest), ratingDisabledOptions)}
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<FieldOptions
						{...props('field', attributes, {
							fieldDisabledOptions: ratingDisabledOptions,
						})}
					/>

					<FieldOptionsMore
						{...props('field', attributes, {
							fieldDisabledOptions: ratingDisabledOptions,
						})}
					/>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<FieldOptionsLayout
						{...props('field', attributes, {
							fieldDisabledOptions: ratingDisabledOptions,
						})}
					/>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<Container standalone>
						<Toggle
							icon={requiredAlt}
							label={__('Required', 'eightshift-forms')}
							checked={ratingIsRequired}
							onChange={(value) => setAttributes({ [getAttrKey('ratingIsRequired', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('ratingIsRequired', attributes, manifest), ratingDisabledOptions)}
						/>
					</Container>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<ConditionalTagsOptions
						{...props('conditionalTags', attributes, {
							conditionalTagsBlockName: ratingName,
							conditionalTagsIsHidden: checkAttr('ratingFieldHidden', attributes, manifest),
						})}
					/>

					<ContainerGroup label={__('Tracking', 'eightshift-forms')}>
						<Container>
							<InputField
								icon={googleTagManager}
								label={__('GTM tracking code', 'eightshift-forms')}
								value={ratingTracking}
								onChange={(value) => setAttributes({ [getAttrKey('ratingTracking', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('ratingTracking', attributes, manifest), ratingDisabledOptions)}
								monospaceFont
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>
		</Tabs>
	);
};
