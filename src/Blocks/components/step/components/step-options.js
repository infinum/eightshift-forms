import React from 'react';
import { select } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey, STORE_NAME } from '@eightshift/frontend-libs-tailwind/scripts';
import { NameField } from './../../utils';
import { icons } from '@eightshift/ui-components/icons';
import { InputField, BaseControl } from '@eightshift/ui-components';

export const StepOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('step');

	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const stepName = checkAttr('stepName', attributes, manifest);
	const stepLabel = checkAttr('stepLabel', attributes, manifest);
	const stepPrevLabel = checkAttr('stepPrevLabel', attributes, manifest);
	const stepNextLabel = checkAttr('stepNextLabel', attributes, manifest);

	return (
		<>
			<>
				<BaseControl
					icon={icons.options}
					label={__('General', 'eightshift-forms')}
				>
					<NameField
						value={stepName}
						help={__('Used to identify the step within form multi step flow.', 'eightshift-forms')}
						attribute={getAttrKey('stepName', attributes, manifest)}
						setAttributes={setAttributes}
						type={'step'}
						isChanged={isNameChanged}
						setIsChanged={setIsNameChanged}
					/>
				</BaseControl>

				<BaseControl
					icon={icons.tag}
					label={__('Label', 'eightshift-forms')}
				>
					<InputField
						help={__('This label will not be shown on the frontend, this is only for easier configuration.', 'eightshift-forms')}
						value={stepLabel}
						onChange={(value) => setAttributes({ [getAttrKey('stepLabel', attributes, manifest)]: value })}
					/>
				</BaseControl>

				<BaseControl
					icon={icons.buttonFilled}
					label={__('Buttons', 'eightshift-forms')}
				>
					<InputField
						label={__('Previous button label', 'eightshift-forms')}
						placeholder={__('Previous', 'eightshift-forms')}
						value={stepPrevLabel}
						onChange={(value) => setAttributes({ [getAttrKey('stepPrevLabel', attributes, manifest)]: value })}
					/>

					<InputField
						label={__('Next button label', 'eightshift-forms')}
						placeholder={__('Next', 'eightshift-forms')}
						value={stepNextLabel}
						onChange={(value) => setAttributes({ [getAttrKey('stepNextLabel', attributes, manifest)]: value })}
					/>
				</BaseControl>
			</>
		</>
	);
};
