import { __ } from '@wordpress/i18n';
import { CONDITIONAL_TAGS_OPERATORS, CONDITIONAL_TAGS_ACTIONS, CONDITIONAL_TAGS_LOGIC } from '../../form/assets/utilities';

export const CONDITIONAL_TAGS_OPERATORS_INTERNAL = {
	[CONDITIONAL_TAGS_OPERATORS.IS]: __('is', 'eightshift-forms'),
	[CONDITIONAL_TAGS_OPERATORS.ISN]: __('is not', 'eightshift-forms'),
	[CONDITIONAL_TAGS_OPERATORS.GT]: __('greater than', 'eightshift-forms'),
	[CONDITIONAL_TAGS_OPERATORS.GTE]: __('greater/equal than', 'eightshift-forms'),
	[CONDITIONAL_TAGS_OPERATORS.LT]: __('less than', 'eightshift-forms'),
	[CONDITIONAL_TAGS_OPERATORS.LTE]: __('less/equal than', 'eightshift-forms'),
	[CONDITIONAL_TAGS_OPERATORS.C]: __('contains', 'eightshift-forms'),
	[CONDITIONAL_TAGS_OPERATORS.SW]: __('starts with', 'eightshift-forms'),
	[CONDITIONAL_TAGS_OPERATORS.EW]: __('ends with', 'eightshift-forms'),
};

export const CONDITIONAL_TAGS_ACTIONS_INTERNAL = {
	[CONDITIONAL_TAGS_ACTIONS.SHOW]: __('Show', 'eightshift-forms'),
	[CONDITIONAL_TAGS_ACTIONS.HIDE]: __('Hide', 'eightshift-forms'),
};

export const CONDITIONAL_TAGS_LOGIC_INTERNAL = {
	[CONDITIONAL_TAGS_LOGIC.ALL]: __('All', 'eightshift-forms'),
	[CONDITIONAL_TAGS_LOGIC.ANY]: __('Any', 'eightshift-forms'),
};
