import { warningFillTransparent } from '@eightshift/ui-components/icons';
import { getUtilsIcons } from '../../form/assets/state-init';

export const InvalidEditor = ({ icon, heading, text }) => {
	return (
		<div>
			<div>
				{icon && getUtilsIcons(icon) && <div dangerouslySetInnerHTML={{ __html: getUtilsIcons(icon) }} />}
				{(!icon || (icon && !getUtilsIcons(icon))) && warningFillTransparent}
			</div>

			{heading && <span>{heading}</span>}

			{text && <span>{text}</span>}
		</div>
	);
};
