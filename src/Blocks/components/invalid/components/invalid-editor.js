import { warningFill } from '@eightshift/ui-components/icons';
import { getUtilsIcons } from '../../form/assets/state-init';
import { Container, RichLabel } from '@eightshift/ui-components';
import { JsxSvg } from '@eightshift/ui-components/icons';

export const InvalidEditor = ({ icon, heading, text }) => {
	const utilsIcon = getUtilsIcons(icon);

	return (
		<div className='esf:p-8 es-uic-font-sans'>
			<Container
				standalone
				centered
			>
				<RichLabel
					icon={utilsIcon ? <JsxSvg svg={utilsIcon} /> : (icon ?? warningFill)}
					label={heading}
					subtitle={text}
				/>
			</Container>
		</div>
	);
};
