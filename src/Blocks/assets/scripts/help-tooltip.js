import { TriggeredPopover } from '@eightshift/ui-components';
import { help } from '@eightshift/ui-components/icons';

export const HelpTooltip = ({ children, icon = help, hidden = false }) => {
	if (!children || hidden) {
		return null;
	}

	return (
		<TriggeredPopover
			triggerButtonIcon={icon}
			triggerButtonProps={{
				size: 'small',
				type: 'ghost',
				className: 'esf:size-24!',
			}}
			className='esf:p-12! esf:max-w-240'
		>
			{children}
		</TriggeredPopover>
	);
};
