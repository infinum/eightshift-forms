import { TriggeredPopover } from '@eightshift/ui-components';
import { help } from '@eightshift/ui-components/icons';
import { clsx } from '@eightshift/ui-components/utilities';

export const HelpTooltip = ({ children, icon = help, hidden = false, className }) => {
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
			className={clsx('esf:p-12! esf:max-w-240', className)}
		>
			{children}
		</TriggeredPopover>
	);
};
