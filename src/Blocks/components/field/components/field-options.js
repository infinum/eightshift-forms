import React from 'react';
import { __ } from '@wordpress/i18n';
import { TextControl, Button, BaseControl } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FieldOptions = (attributes) => {
	const {
		setAttributes,

		showFieldLabel = true,
	} = attributes;

	const fieldHelp = checkAttr('fieldHelp', attributes, manifest);
	const fieldLabel = checkAttr('fieldLabel', attributes, manifest);
	const fieldHideLabel = checkAttr('fieldHideLabel', attributes, manifest);

	return (
		<>
			{showFieldLabel &&
				<BaseControl
					label={(
						<div className='es-h-between es-w-full'>
							<IconLabel icon={icons.fieldLabel} label={__('Field label', 'eightshift-forms')} />
							<Button
								icon={icons.hide}
								isPressed={fieldHideLabel}
								onClick={() => setAttributes({ [getAttrKey('fieldHideLabel', attributes, manifest)]: !fieldHideLabel })}
							>
								{__('Hide', 'eightshift-forms')}
							</Button>

						</div>
					)}
					help={fieldHideLabel ? __('Hiding the label might impact accessibility!', 'eightshift-forms') : null}
				>
					{!fieldHideLabel &&
						<TextControl
							value={fieldLabel}
							onChange={(value) => setAttributes({ [getAttrKey('fieldLabel', attributes, manifest)]: value })}
						/>
					}

				</BaseControl>
			}

			<TextControl
				label={<IconLabel icon={icons.fieldHelp} label={__('Help text', 'eightshift-forms')} />}
				value={fieldHelp}
				onChange={(value) => setAttributes({ [getAttrKey('fieldHelp', attributes, manifest)]: value })}
			/>
		</>
	);
};
