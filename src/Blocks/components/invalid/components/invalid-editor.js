import React from 'react';
import manifest from '../manifest.json';

export const InvalidEditor = ({
	icon,
	heading,
	text,
}) => {
	const {
		componentClass,
		iconDefault,
	} = manifest;

	return (
		<div className={componentClass}>
			<div className={`${componentClass}__icon`}>
				<div dangerouslySetInnerHTML={{__html: icon ? icon : iconDefault}} />
			</div>

			{heading &&
				<div className={`${componentClass}__heading`}>
					{heading}
				</div>
			}

			{text &&
				<div className={`${componentClass}__text`}>
					{text}
				</div>
			}
		</div>
	);
};
