import React from 'react';
import { useSelect, select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { InnerBlocks } from '@wordpress/block-editor';
import { props } from '@eightshift/frontend-libs/scripts';
import { FormEditor } from './../../form/components/form-editor';
import manifest from '../manifest.json';

export const IntegrationsEditor = ({
	attributes,
	setAttributes,
	itemId,
	innerId,
	clientId,
	useInnerId = false,
}) => {
	const {
		componentClass,
	} = manifest;

	// Check if form selector has inner blocks.
	const hasInnerBlocks = useSelect((select) => {
		const blocks = select('core/block-editor').getBlock(clientId);

		return blocks?.innerBlocks.length !== 0;
	});

	const InvalidPlaceholder = () => {
		return (
			<div className={`${componentClass}__invalid`}>
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M4.71991 1.60974C3.11997 2.29956 2 3.89096 2 5.74394C2 7.70327 3.25221 9.37013 5 9.98788V17C5 17.8284 5.67157 18.5 6.5 18.5C7.32843 18.5 8 17.8284 8 17V9.98788C9.74779 9.37013 11 7.70327 11 5.74394C11 3.78461 9.74779 2.11775 8 1.5V5.74394C8 6.57237 7.32843 7.24394 6.5 7.24394C5.67157 7.24394 5 6.57237 5 5.74394V1.5C4.90514 1.53353 4.81173 1.57015 4.71991 1.60974Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
					<path d="M13 13V16C13 17.3807 14.1193 18.5 15.5 18.5V18.5C16.8807 18.5 18 17.3807 18 16V13M13 13V10.5H14M13 13H18M18 13V10.5H17M14 10.5V5.5L13.5 3.5L14 1.5H17L17.5 3.5L17 5.5V10.5M14 10.5H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<br />
				<b>{__('You need to select the form from the dropdown.', 'eightshift-forms')}</b>
				<br />
				{__('Check the forms sidebar and select the integration form your want to use.', 'eightshift-forms')}
			</div>
		)
	}

	const OutputDefault = () => {
		return (
			<>
				{itemId ? 
					<Output /> :
					<InvalidPlaceholder />
				}
			</>
		);
	}

	const OutputWithInner = () => {
		return (
			<>
				{(itemId && innerId) ? 
					<Output /> :
					<InvalidPlaceholder />
				}
			</>
		);
	}

	const Output = () => {
		return (
			<FormEditor
				{...props('form', attributes, {
					setAttributes,
					formContent: <InnerBlocks />
				})}
			/>
		);
	}

	return (
		<>
			{hasInnerBlocks ? 
				<>
					{useInnerId ?
						<OutputWithInner /> :
						<OutputDefault />
					}
				</> :
				<InvalidPlaceholder />
			}
			
		</>
	);
}