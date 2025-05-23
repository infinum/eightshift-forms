import React from 'react';
import { icons } from '@eightshift/ui-components/icons';
import { getUtilsIcons } from '../../utils';

export const InvalidEditor = ({ heading, icon = null, text = null }) => {
	return (
		<div>
			<div>
				{icon && getUtilsIcons(icon) && <div dangerouslySetInnerHTML={{ __html: getUtilsIcons(icon) }} />}
				{(!icon || (icon && !getUtilsIcons(icon))) && icons.warningFillTransparent}
			</div>

			{heading && <span>{heading}</span>}

			{text && <span>{text}</span>}
		</div>
	);
};
