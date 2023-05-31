import { __ } from '@wordpress/i18n';
import {
	CONDITIONAL_TAGS_ACTIONS,
	CONDITIONAL_TAGS_LOGIC,
	CONDITIONAL_TAGS_OPERATORS,
} from '../assets/utils';

export const CONDITIONAL_TAGS_OPERATORS_LABELS = {
	[CONDITIONAL_TAGS_OPERATORS.IS]: __('is', 'eightshift-forms'),
	[CONDITIONAL_TAGS_OPERATORS.ISN]: __('is not', 'eightshift-forms'),
	[CONDITIONAL_TAGS_OPERATORS.GT]: __('greater than', 'eightshift-forms'),
	[CONDITIONAL_TAGS_OPERATORS.GTE]: __('greater than or equal', 'eightshift-forms'),
	[CONDITIONAL_TAGS_OPERATORS.LT]: __('less than', 'eightshift-forms'),
	[CONDITIONAL_TAGS_OPERATORS.LTE]: __('less than or equal', 'eightshift-forms'),
	[CONDITIONAL_TAGS_OPERATORS.C]: __('contains', 'eightshift-forms'),
	[CONDITIONAL_TAGS_OPERATORS.SW]: __('starts with', 'eightshift-forms'),
	[CONDITIONAL_TAGS_OPERATORS.EW]: __('ends with', 'eightshift-forms'),
};

export const CONDITIONAL_TAGS_ACTIONS_LABELS = {
	[CONDITIONAL_TAGS_ACTIONS.SHOW]: __('visible', 'eightshift-forms'),
	[CONDITIONAL_TAGS_ACTIONS.HIDE]: __('hidden', 'eightshift-forms'),
};

export const CONDITIONAL_TAGS_ACTIONS_INVERSE_LABELS = {
	[CONDITIONAL_TAGS_ACTIONS.SHOW]: __('Hide', 'eightshift-forms'),
	[CONDITIONAL_TAGS_ACTIONS.HIDE]: __('Show', 'eightshift-forms'),
};

export const CONDITIONAL_TAGS_ACTIONS_SHORT_LABELS = {
	[CONDITIONAL_TAGS_ACTIONS.SHOW]: __('show', 'eightshift-forms'),
	[CONDITIONAL_TAGS_ACTIONS.HIDE]: __('hide', 'eightshift-forms'),
};

export const CONDITIONAL_TAGS_LOGIC_LABELS = {
	[CONDITIONAL_TAGS_LOGIC.OR]: __('or', 'eightshift-forms'),
	[CONDITIONAL_TAGS_LOGIC.AND]: __('and', 'eightshift-forms'),
};
