import { icons } from '@eightshift/frontend-libs/scripts';
import React from 'react';
import manifestUtils from './../../utils/manifest.json';

export const InvalidEditor = ({ icon, heading, text }) => {
	return (
		<div className='es-v-center es-gap-1! es-w-96 es-mx-auto es-px-5 es-py-10 es-rounded-3 es-border-red-500'>
			<div className='es-nested-w-8 es-nested-h-8 es-nested-color-red-500 es-mb-2'>
				{icon && manifestUtils?.[icon] && <div className='es-nested-w-8 es-nested-h-8' dangerouslySetInnerHTML={{ __html: manifestUtils.icons[icon] }} />}
				{(!icon || (icon && !manifestUtils?.[icon])) && icons.warningFillTransparent}
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
