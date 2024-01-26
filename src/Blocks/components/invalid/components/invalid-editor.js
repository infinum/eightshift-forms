import React from 'react';
import { icons } from '@eightshift/frontend-libs/scripts';
import { getUtilsIcons } from '../../form/assets/state-init';

export const InvalidEditor = ({ icon, heading, text }) => {
	return (
		<div className='es-v-center es-gap-1! es-w-96 es-mx-auto es-px-5 es-py-10 es-rounded-3 es-border-red-500 es-text-align-center'>
			<div className='es-nested-w-8 es-nested-h-8 es-nested-color-red-500 es-mb-2'>
				{icon && getUtilsIcons(icon) && <div className='es-nested-w-8 es-nested-h-8' dangerouslySetInnerHTML={{ __html: getUtilsIcons(icon) }} />}
				{(!icon || (icon && !getUtilsIcons(icon))) && icons.warningFillTransparent}
			</div>

			{heading &&
				<span className='es-text-4 es-font-weight-500'>{heading}</span>
			}

			{text &&
				<span className='es-text-3 es-color-cool-gray-500'>{text}</span>
			}
		</div>
	);
};
