import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { FieldEditor } from './components/field-editor';
import { FieldOptions } from './components/field-options';
import { BaseControl, Container, ContainerPanel, TriggeredPopover } from '@eightshift/ui-components';
import { form, options } from '@eightshift/ui-components/icons';

export const Field = (props) => {
	const { setAttributes, attributes, children, clientId } = props;

	return (
		<>
			<InspectorControls>
				<ContainerPanel>
					<Container standalone>
						<BaseControl
							icon={form}
							label={__('Eightshift Forms', 'eightshift-forms')}
							inline
						>
							<TriggeredPopover
								triggerButtonIcon={options}
								className='esf:w-xs esf:px-0! esf:pb-0! esf:pt-4!'
								showArrow
							>
								<FieldOptions
									attributes={attributes}
									setAttributes={setAttributes}
								/>
							</TriggeredPopover>
						</BaseControl>
					</Container>
				</ContainerPanel>
			</InspectorControls>
			<FieldEditor
				attributes={attributes}
				children={children}
				clientId={clientId}
			/>
		</>
	);
};
