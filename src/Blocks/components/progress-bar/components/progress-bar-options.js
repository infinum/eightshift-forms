import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { itemLimit, positionHStart, tag } from '@eightshift/ui-components/icons';
import { Container, ContainerGroup, NumberPicker, Toggle } from '@eightshift/ui-components';
import manifest from '../manifest.json';

export const ProgressBarOptions = (attributes) => {
	const { setAttributes, additionalControls, additionalControlsAfter } = attributes;

	const progressBarUse = checkAttr('progressBarUse', attributes, manifest);
	const progressBarHideLabels = checkAttr('progressBarHideLabels', attributes, manifest);
	const progressBarMultiflowUse = checkAttr('progressBarMultiflowUse', attributes, manifest);
	const progressBarMultiflowInitCount = checkAttr('progressBarMultiflowInitCount', attributes, manifest);

	return (
		<>
			<ContainerGroup>
				<Container>
					<Toggle
						icon={positionHStart}
						label={__('Progress indicator', 'eightshift-forms')}
						checked={progressBarUse}
						onChange={(value) => {
							setAttributes({ [getAttrKey('progressBarUse', attributes, manifest)]: value });
						}}
					/>
				</Container>

				<Container hidden={!progressBarUse}>
					<Toggle
						icon={tag}
						label={__('Step labels', 'eightshift-forms')}
						help={__('This will hide the labels on the progress bar.', 'eightshift-forms')}
						checked={!progressBarHideLabels}
						onChange={(value) => {
							setAttributes({ [getAttrKey('progressBarHideLabels', attributes, manifest)]: !value });
						}}
					/>
				</Container>
			</ContainerGroup>

			<ContainerGroup>
				{additionalControls}

				<Container hidden={!progressBarMultiflowUse}>
					<NumberPicker
						icon={itemLimit}
						label={__('Initial number of steps', 'eightshift-forms')}
						value={progressBarMultiflowInitCount}
						onChange={(value) =>
							setAttributes({ [getAttrKey('progressBarMultiflowInitCount', attributes, manifest)]: value })
						}
						inline
					/>
				</Container>

				{additionalControlsAfter}
			</ContainerGroup>
		</>
	);
};
