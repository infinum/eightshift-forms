import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { PanelBody, TextControl, SelectControl, ToggleControl, ExternalLink, BaseControl } from '@wordpress/components';
import { updateThemeFromTopParent } from '../../../helpers/update-theme-from-top-parent';
import { getSinglePrefillSources } from '../../../helpers/prefill';

export const InputOptions = (props) => {
	const {
		clientId,
		attributes: {
			name,
			value,
			id,
			placeholder,
			classes,
			type,
			isDisabled,
			isReadOnly,
			isRequired,
			preventSending,
			prefillData,
			prefillDataSource,
			pattern,
			customValidityMsg,
			showName,
			showType,
			showClasses,
			showPrefillOptions = true,
		},
		actions: {
			onChangeName,
			onChangeValue,
			onChangeId,
			onChangePlaceholder,
			onChangeClasses,
			onChangeType,
			onChangeIsDisabled,
			onChangeIsReadOnly,
			onChangeIsRequired,
			onChangePreventSending,
			onChangePattern,
			onChangeCustomValidityMsg,
			onChangeTheme,
			onChangePrefillData,
			onChangePrefillDataSource,
		},
	} = props;

	const prefillSourcesAsValues = getSinglePrefillSources();

	updateThemeFromTopParent(clientId, onChangeTheme);

	return (
		<PanelBody title={__('Input Settings', 'eightshift-forms')}>
			{onChangeName && showName &&
				<TextControl
					label={__('Name', 'eightshift-forms')}
					value={name}
					onChange={onChangeName}
				/>
			}

			{onChangeValue &&
				<TextControl
					label={__('Value', 'eightshift-forms')}
					value={value}
					onChange={onChangeValue}
				/>
			}

			{onChangePlaceholder &&
				<TextControl
					label={__('Placeholder', 'eightshift-forms')}
					value={placeholder}
					onChange={onChangePlaceholder}
				/>
			}

			{onChangeType && showType &&
				<SelectControl
					label={__('Type', 'eightshift-forms')}
					value={type}
					options={[
						{ label: __('Text', 'eightshift-forms'), value: 'text' },
						{ label: __('Hidden', 'eightshift-forms'), value: 'hidden' },
						{ label: __('Url', 'eightshift-forms'), value: 'url' },
						{ label: __('Email', 'eightshift-forms'), value: 'email' },
						{ label: __('Number', 'eightshift-forms'), value: 'number' },
						{ label: __('Password', 'eightshift-forms'), value: 'password' },
						{ label: __('Color', 'eightshift-forms'), value: 'color' },
						{ label: __('Date', 'eightshift-forms'), value: 'date' },
						{ label: __('Date Time Local', 'eightshift-forms'), value: 'datetime-local' },
						{ label: __('Image', 'eightshift-forms'), value: 'image' },
						{ label: __('Month', 'eightshift-forms'), value: 'month' },
						{ label: __('Range', 'eightshift-forms'), value: 'range' },
						{ label: __('Search', 'eightshift-forms'), value: 'search' },
						{ label: __('Tel', 'eightshift-forms'), value: 'tel' },
						{ label: __('Time', 'eightshift-forms'), value: 'time' },
						{ label: __('Week', 'eightshift-forms'), value: 'week' },
					]}
					onChange={onChangeType}
				/>
			}

			{onChangePrefillData && showPrefillOptions &&
				<ToggleControl
					label={__('Prefill data?', 'eightshift-forms')}
					help={__('If enabled, this field\'s select options will be prefilled from a source of your choice.', 'eightshift-forms')}
					checked={prefillData}
					onChange={onChangePrefillData}
				/>
			}

			{onChangePrefillData && showPrefillOptions && prefillData &&
				<SelectControl
					label={__('Prefill from?', 'eightshift-forms')}
					help={__('Please select the source from which to prefill values.', 'eightshift-forms')}
					value={prefillDataSource}
					options={prefillSourcesAsValues}
					onChange={onChangePrefillDataSource}
				/>
			}

			{onChangePattern &&
				<>
					<TextControl
						label={__('Custom pattern', 'eightshift-forms')}
						help={__('Set a custom pattern that the user must match in order for this field to be valid', 'eightshift-forms')}
						value={pattern}
						onChange={onChangePattern}
					/>
					<BaseControl>
						<ExternalLink href="https://developer.mozilla.org/en-US/docs/Learn/Forms/Form_validation#Validating_against_a_regular_expression">{__('More info on regular expressions', 'eightshift-forms')}</ExternalLink>
					</BaseControl>
				</>
			}

			{onChangeCustomValidityMsg && pattern &&
				<TextControl
					label={__('Custom validity error message', 'eightshift-forms')}
					help={__('Set a custom message that user sees if he doesnt match the set pattern.', 'eightshift-forms')}
					value={customValidityMsg}
					onChange={onChangeCustomValidityMsg}
				/>
			}

			{onChangeClasses && showClasses &&
				<TextControl
					label={__('Classes', 'eightshift-forms')}
					value={classes}
					onChange={onChangeClasses}
				/>
			}

			{onChangeId &&
				<TextControl
					label={__('ID', 'eightshift-forms')}
					value={id}
					onChange={onChangeId}
				/>
			}

			{onChangeIsDisabled &&
				<ToggleControl
					label={__('Disabled', 'eightshift-forms')}
					checked={isDisabled}
					onChange={onChangeIsDisabled}
				/>
			}

			{onChangeIsReadOnly &&
				<ToggleControl
					label={__('Readonly', 'eightshift-forms')}
					checked={isReadOnly}
					onChange={onChangeIsReadOnly}
				/>
			}

			{onChangeIsRequired &&
				<ToggleControl
					label={__('Required', 'eightshift-forms')}
					checked={isRequired}
					onChange={onChangeIsRequired}
				/>
			}

			{onChangePreventSending &&
				<ToggleControl
					label={__('Do not send?', 'eightshift-forms')}
					help={__('If enabled this field won\'t be sent when the form is submitted.', 'eightshift-forms')}
					checked={preventSending}
					onChange={onChangePreventSending}
				/>
			}
		</PanelBody>
	);
};
