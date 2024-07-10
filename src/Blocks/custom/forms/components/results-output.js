import { select } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { PanelBody, Button, Modal } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import {
	icons,
	checkAttr,
	getAttrKey,
	STORE_NAME,
	IconToggle,
	Section,
	Control,
	IconLabel,
} from '@eightshift/frontend-libs/scripts';

export const ResultsOutputOptions = ({attributes, setAttributes}) => {
	const [isModalOpen, setIsModalOpen] = useState(false);

	const manifest = select(STORE_NAME).getBlock('forms');

	const formsResultsOutputUse = checkAttr('formsResultsOutputUse', attributes, manifest);
	const formsResultsOutputData = checkAttr('formsResultsOutputData', attributes, manifest);

	return (
		<PanelBody>
			<IconToggle
				icon={icons.visibilityAlt}
				label={__('Results output', 'eightshift-forms')}
				checked={formsResultsOutputUse}
				onChange={(value) => {
					setAttributes({ [getAttrKey('formsResultsOutputUse', attributes, manifest)]: value });

					// if (!value) {
					// 	setAttributes({ [getAttrKey('conditionalTagsAction', attributes, manifest)]: undefined });
					// 	setAttributes({ [getAttrKey('formsResultsOutputData', attributes, manifest)]: undefined });
					// } else {
					// 	setAttributes({ [getAttrKey('formsResultsOutputData', attributes, manifest)]: [] });
					// }
				}}
				noBottomSpacing={!formsResultsOutputUse}
				additionalClasses='es-font-weight-500'
			/>

			<Section showIf={formsResultsOutputUse} noBottomSpacing>
				<Control
					icon={icons.conditionH}
					label={__('Data set', 'eightshift-forms')}
					// Translators: %d refers to the number of active rules
					subtitle={formsResultsOutputData?.length > 0 && sprintf(__('%d added', 'eightshift-forms'), formsResultsOutputData.length)}
					noBottomSpacing
					inlineLabel
				>
					<Button
						variant='tertiary'
						onClick={() => setIsModalOpen(true)}
						className='es-rounded-1.5 es-w-9 es-h-center es-font-weight-500'
					>
						{formsResultsOutputData?.length > 0 ? __('Edit', 'eightshift-forms') : __('Add', 'eightshift-forms')}
					</Button>
				</Control>
			</Section>

			{formsResultsOutputUse && isModalOpen &&
				<Modal
					overlayClassName='es-conditional-tags-modal es-geolocation-modal'
					className='es-modal-max-width-xxl es-rounded-3!'
					title={<IconLabel icon={icons.visibilityAlt} label={__('Results output data', 'eightshift-forms')} standalone />}
					onRequestClose={() => setIsModalOpen(false)}
				>
					<p>
						{__('This data will be displayed after the form is submitted and populated in the connected results output item.', 'eightshift-forms')}
					</p>
				</Modal>
			}

			</PanelBody>
	);
}
