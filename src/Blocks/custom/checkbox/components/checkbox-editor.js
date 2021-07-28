import { __ } from '@wordpress/i18n';
import { RichText } from '@wordpress/block-editor';
import { CheckboxControl } from '@wordpress/components';

export const CheckboxEditor = (props) => {
	const {
		attributes: {
			blockClass,
			label,
			classes,
			isChecked,
			theme = '',
		},
		actions: {
			onChangeLabel,
			onChangeIsChecked,
		},
	} = props;

	return (
		<div className={`${blockClass} ${blockClass}__theme--${theme}`}>
			<div className={`${blockClass}__label`}>
				<span className={`${blockClass}__checkbox ${classes}`}>
					<CheckboxControl
						checked={isChecked}
						onChange={onChangeIsChecked}
					/>
				</span>

				<span className={`${blockClass}__checkmark`}></span>
				<RichText
					placeholder={__('Add your label', 'eightshift-forms')}
					className={`${blockClass}__label-content`}
					onChange={onChangeLabel}
					value={label}
				/>
			</div>
		</div>
	);
};
