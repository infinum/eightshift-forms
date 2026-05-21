import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { arrowLeftCircle, arrowRightCircle, tag } from '@eightshift/ui-components/icons';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { NameField } from './../../utils';
import { Container, ContainerGroup, ContainerPanel, InputField } from '@eightshift/ui-components';
import { HelpTooltip } from '../../../assets/scripts/help-tooltip';
import manifest from '../manifest.json';

export const StepOptions = (attributes) => {
	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const stepName = checkAttr('stepName', attributes, manifest);
	const stepLabel = checkAttr('stepLabel', attributes, manifest);
	const stepPrevLabel = checkAttr('stepPrevLabel', attributes, manifest);
	const stepNextLabel = checkAttr('stepNextLabel', attributes, manifest);

	return (
		<ContainerPanel>
			<NameField
				value={stepName}
				help={__('Used to identify the step within form multi step flow.', 'eightshift-forms')}
				attribute={getAttrKey('stepName', attributes, manifest)}
				setAttributes={setAttributes}
				type={'step'}
				isChanged={isNameChanged}
				setIsChanged={setIsNameChanged}
			/>

			<Container standalone>
				<InputField
					icon={tag}
					label={__('Label', 'eightshift-forms')}
					placeholder={__('e.g. Step 1', 'eightshift-forms')}
					actions={
						<HelpTooltip>{__('Not shown to users, assists with step configuration.', 'eightshift-forms')}</HelpTooltip>
					}
					value={stepLabel}
					onChange={(value) => setAttributes({ [getAttrKey('stepLabel', attributes, manifest)]: value })}
				/>
			</Container>

			<ContainerGroup label={__('Button labels', 'eightshift-forms')}>
				<Container>
					<InputField
						icon={arrowLeftCircle}
						label={__('Previous step', 'eightshift-forms')}
						placeholder={__('Previous', 'eightshift-forms')}
						value={stepPrevLabel}
						onChange={(value) => setAttributes({ [getAttrKey('stepPrevLabel', attributes, manifest)]: value })}
						inline
					/>
				</Container>

				<Container>
					<InputField
						icon={arrowRightCircle}
						label={__('Next step', 'eightshift-forms')}
						placeholder={__('Next', 'eightshift-forms')}
						value={stepNextLabel}
						onChange={(value) => setAttributes({ [getAttrKey('stepNextLabel', attributes, manifest)]: value })}
						inline
					/>
				</Container>
			</ContainerGroup>
		</ContainerPanel>
	);
};
