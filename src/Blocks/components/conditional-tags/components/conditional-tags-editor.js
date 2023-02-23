import React from 'react';
import { __ } from '@wordpress/i18n';
import { checkAttr } from '@eightshift/frontend-libs/scripts';
import utilsManifest from './../../utils/manifest.json';
import manifest from './../manifest.json';

export const ConditionalTagsEditor = (attributes) => {
	const {
		componentClass,
	} = manifest;

	const conditionalTagsUse = checkAttr('conditionalTagsUse', attributes, manifest);

	return (
		<>
			{conditionalTagsUse &&
				<div className={componentClass} dangerouslySetInnerHTML={{__html: utilsManifest.icons.conditionalTags}} title={__('This field has conditional tags set tags', 'eightshift-forms')}></div>
			}
		</>
	);
};
