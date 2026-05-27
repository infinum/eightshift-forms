import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { ContainerPanel, InputField, OptionSelect, Container, ContainerGroup } from '@eightshift/ui-components';
import { chevronLeft, chevronRight, experiment, rename } from '@eightshift/ui-components/icons';
import { CONDITIONAL_TAGS_OPERATORS_EXTENDED_LABELS, CONDITIONAL_TAGS_OPERATORS_LABELS } from './../../../components/conditional-tags/components/conditional-tags-labels';
import { getConstantsOptions, NameField } from './../../../components/utils';
import manifest from '../manifest.json';
import globalManifest from '../../../manifest.json';

export const ResultOutputItemOptions = ({ attributes, setAttributes }) => {
	const [isNameChanged, setIsNameChanged] = useState(false);

	const resultOutputItemName = checkAttr('resultOutputItemName', attributes, manifest);
	const resultOutputItemValueStart = checkAttr('resultOutputItemValue', attributes, manifest);
	const resultOutputItemValueEnd = checkAttr('resultOutputItemValueEnd', attributes, manifest);
	const resultOutputItemOperator = checkAttr('resultOutputItemOperator', attributes, manifest);

	const [showEndValue, setShowEndValue] = useState(resultOutputItemOperator.toUpperCase() in globalManifest.comparatorExtended);

	return (
		<ContainerPanel>
			<NameField
				value={resultOutputItemName}
				attribute={getAttrKey('resultOutputItemName', attributes, manifest)}
				setAttributes={setAttributes}
				type='resultOutputItem'
				isChanged={isNameChanged}
				setIsChanged={setIsNameChanged}
			/>

			<Container standalone>
				<OptionSelect
					icon={experiment}
					label={__('Operator', 'eightshift-forms')}
					value={resultOutputItemOperator}
					options={getConstantsOptions({
						...CONDITIONAL_TAGS_OPERATORS_LABELS,
						...CONDITIONAL_TAGS_OPERATORS_EXTENDED_LABELS,
					})}
					onChange={(value) => {
						setShowEndValue(value.toUpperCase() in globalManifest.comparatorExtended);
						setAttributes({ [getAttrKey('resultOutputItemOperator', attributes, manifest)]: value });
					}}
					type='menu'
					inline
				/>
			</Container>

			<ContainerGroup>
				<Container>
					<InputField
						type={showEndValue ? 'number' : 'text'}
						icon={showEndValue ? chevronRight : rename}
						label={showEndValue ? __('Start value', 'eightshift-forms') : __('Value', 'eightshift-forms')}
						value={resultOutputItemValueStart}
						onChange={(value) => setAttributes({ [getAttrKey('resultOutputItemValue', attributes, manifest)]: value })}
						placeholder={showEndValue && __('(number)', 'eightshift-forms')}
						inline
					/>
				</Container>

				<Container hidden={!showEndValue}>
					<InputField
						type='number'
						icon={chevronLeft}
						label={__('End value', 'eightshift-forms')}
						value={resultOutputItemValueEnd}
						onChange={(value) => setAttributes({ [getAttrKey('resultOutputItemValueEnd', attributes, manifest)]: value })}
						placeholder={__('(number)', 'eightshift-forms')}
						inline
					/>
				</Container>
			</ContainerGroup>
		</ContainerPanel>
	);
};
