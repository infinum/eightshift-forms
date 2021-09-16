import React from 'react';
import { __ } from '@wordpress/i18n';
import { TextControl, SelectControl } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	getOption
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FormOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const formName = checkAttr('formName', attributes, manifest);
	const formAction = checkAttr('formAction', attributes, manifest);
	const formMethod = checkAttr('formMethod', attributes, manifest);
	const formTarget = checkAttr('formTarget', attributes, manifest);
	const formId = checkAttr('formId', attributes, manifest);

	return (
		<>
			<TextControl
				label={<IconLabel icon={icons.id} label={__('Name', 'eightshift-forms')} />}
				value={formName}
				onChange={(value) => setAttributes({ [getAttrKey('formName', attributes, manifest)]: value })}
			/>

			<TextControl
				label={<IconLabel icon={icons.id} label={__('Action', 'eightshift-forms')} />}
				value={formAction}
				onChange={(value) => setAttributes({ [getAttrKey('formAction', attributes, manifest)]: value })}
			/>

			<SelectControl
				label={<IconLabel icon={icons.id} label={__('Method', 'eightshift-forms')} />}
				value={formMethod}
				options={getOption('formMethod', attributes, manifest)}
				onChange={(value) => setAttributes({ [getAttrKey('formMethod', attributes, manifest)]: value })}
			/>

			<SelectControl
				label={<IconLabel icon={icons.id} label={__('Target', 'eightshift-forms')} />}
				value={formTarget}
				options={getOption('formTarget', attributes, manifest)}
				onChange={(value) => setAttributes({ [getAttrKey('formTarget', attributes, manifest)]: value })}
			/>

			<TextControl
				label={<IconLabel icon={icons.id} label={__('Id', 'eightshift-forms')} />}
				value={formId}
				onChange={(value) => setAttributes({ [getAttrKey('formId', attributes, manifest)]: value })}
			/>
		</>
	);
};
